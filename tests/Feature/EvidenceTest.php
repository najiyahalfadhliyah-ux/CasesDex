<?php

namespace Tests\Feature;

use App\Models\CaseRecord;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class EvidenceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $petugas;
    private CaseRecord $case;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->petugas = User::factory()->create();
        $this->case = CaseRecord::factory()->create(['assigned_to' => $this->petugas->id]);
    }

    // Berkas sungguhan (bukan UploadedFile::fake) supaya tipe asli dibaca dari isinya
    private function realFile(string $name, string $content): UploadedFile
    {
        $path = tempnam(sys_get_temp_dir(), 'bukti');
        file_put_contents($path, $content);

        return new UploadedFile($path, $name, null, null, true);
    }

    private function uploadDigital(User $user, UploadedFile $file)
    {
        return $this->actingAs($user)->post("/cases/{$this->case->id}/evidence", [
            'type' => 'digital',
            'recorded_date' => today()->format('Y-m-d'),
            'file' => $file,
        ]);
    }

    public function test_hash_sha256_dihitung_saat_unggah_dan_berkas_disimpan_dengan_nama_acak(): void
    {
        $content = "2026-11-01 10:00 login gagal dari 10.0.0.5\n";

        $this->uploadDigital($this->petugas, $this->realFile('akses.log', $content))->assertSessionHasNoErrors();

        $evidence = Evidence::sole();
        $this->assertSame(hash('sha256', $content), $evidence->sha256_hash);
        $this->assertSame('akses.log', $evidence->original_name);
        $this->assertSame('LOG', $evidence->format);
        $this->assertStringNotContainsString('akses', $evidence->file_path);
        Storage::disk('local')->assertExists($evidence->file_path);
    }

    public function test_pemeriksaan_hash_cocok_lalu_tidak_cocok_setelah_isi_berkas_diubah(): void
    {
        $this->uploadDigital($this->petugas, $this->realFile('catatan.txt', 'isi asli'));
        $evidence = Evidence::sole();

        $this->actingAs($this->petugas)->post("/evidence/{$evidence->id}/hash-check")->assertRedirect("/evidence/{$evidence->id}");
        $this->assertDatabaseHas('hash_checks', ['evidence_id' => $evidence->id, 'is_match' => true, 'checked_by' => $this->petugas->id]);

        Storage::disk('local')->put($evidence->file_path, 'isi sudah diubah');

        $this->actingAs($this->admin)->post("/evidence/{$evidence->id}/hash-check");
        $this->assertDatabaseHas('hash_checks', [
            'evidence_id' => $evidence->id,
            'is_match' => false,
            'computed_hash' => hash('sha256', 'isi sudah diubah'),
            'stored_hash' => hash('sha256', 'isi asli'),
        ]);

        $this->actingAs($this->admin)->get("/evidence/{$evidence->id}")->assertOk()->assertSee('Tidak cocok');
    }

    public function test_petugas_tidak_bisa_melihat_atau_mengunduh_bukti_kasus_orang_lain(): void
    {
        $this->uploadDigital($this->petugas, $this->realFile('catatan.txt', 'rahasia'));
        $evidence = Evidence::sole();
        $petugasLain = User::factory()->create();

        // Signed URL yang sah tetap ditolak untuk petugas kasus lain (Policy tetap dicek)
        $this->actingAs($petugasLain)->get($this->downloadUrl($evidence))->assertForbidden();
        $this->actingAs($petugasLain)->get("/evidence/{$evidence->id}")->assertForbidden();
        $this->actingAs($petugasLain)->post("/evidence/{$evidence->id}/hash-check")->assertForbidden();
        $this->actingAs($petugasLain)->post("/evidence/{$evidence->id}/reupload", ['file' => $this->realFile('lain.txt', 'x')])->assertForbidden();
        $this->uploadDigital($petugasLain, $this->realFile('lain.txt', 'x'))->assertForbidden();

        $this->actingAs($this->petugas)->get($this->downloadUrl($evidence))->assertOk()->assertDownload('catatan.txt');
        $this->actingAs($this->admin)->get($this->downloadUrl($evidence))->assertOk();
    }

    private function downloadUrl(Evidence $evidence, $version = null): string
    {
        return URL::temporarySignedRoute('evidence.download', now()->addMinutes(30), array_filter([$evidence, $version]));
    }

    public function test_link_unduh_tanpa_tanda_tangan_diubah_atau_kedaluwarsa_ditolak(): void
    {
        $this->uploadDigital($this->petugas, $this->realFile('catatan.txt', 'isi'));
        $evidence = Evidence::sole();
        $url = $this->downloadUrl($evidence);

        $this->actingAs($this->petugas)->get("/evidence/{$evidence->id}/download")->assertForbidden();
        $this->actingAs($this->petugas)->get($url.'x')->assertForbidden();
        $this->actingAs($this->petugas)->get("/evidence/{$evidence->id}")->assertDontSee($evidence->file_path);

        $this->travel(31)->minutes();
        $this->actingAs($this->petugas)->get($url)->assertForbidden();
    }

    public function test_unggah_ulang_isi_berbeda_tidak_cocok_dan_hash_asli_tidak_tertimpa(): void
    {
        $this->uploadDigital($this->petugas, $this->realFile('catatan.txt', 'isi asli'));
        $evidence = Evidence::sole();
        $originalPath = $evidence->file_path;

        // Isi sama: cocok
        $this->actingAs($this->petugas)->post("/evidence/{$evidence->id}/reupload", ['file' => $this->realFile('salinan.txt', 'isi asli')])
            ->assertRedirect("/evidence/{$evidence->id}");
        $this->assertDatabaseHas('hash_checks', ['action' => 'unggah_ulang', 'original_name' => 'salinan.txt', 'is_match' => true]);

        // Isi berbeda: tidak cocok
        $this->actingAs($this->petugas)->post("/evidence/{$evidence->id}/reupload", ['file' => $this->realFile('diubah.txt', 'isi palsu')]);
        $reupload = $evidence->hashChecks()->where('original_name', 'diubah.txt')->sole();
        $this->assertFalse($reupload->is_match);
        $this->assertSame(hash('sha256', 'isi palsu'), $reupload->computed_hash);
        $this->assertSame(hash('sha256', 'isi asli'), $reupload->stored_hash);

        // Hash dan berkas asli tetap; berkas lama tidak dihapus
        $evidence->refresh();
        $this->assertSame(hash('sha256', 'isi asli'), $evidence->sha256_hash);
        $this->assertSame($originalPath, $evidence->file_path);
        Storage::disk('local')->assertExists([$originalPath, $reupload->file_path]);

        // Pemeriksaan berikutnya memeriksa berkas terbaru, tetap tidak cocok
        $this->actingAs($this->admin)->post("/evidence/{$evidence->id}/hash-check");
        $this->assertFalse($evidence->hashChecks()->first()->is_match);
        $this->assertSame('periksa', $evidence->hashChecks()->first()->action);

        // Setiap versi bisa diunduh dengan nama aslinya
        $this->actingAs($this->petugas)->get($this->downloadUrl($evidence, $reupload))->assertOk()->assertDownload('diubah.txt');
        $this->actingAs($this->petugas)->get($this->downloadUrl($evidence))->assertDownload('catatan.txt');
        $this->actingAs($this->petugas)->get("/evidence/{$evidence->id}")->assertOk()->assertSee('Tidak cocok')->assertSee('diubah.txt');
    }

    public function test_unggah_ulang_tipe_terlarang_ditolak(): void
    {
        $this->uploadDigital($this->petugas, $this->realFile('catatan.txt', 'isi'));
        $evidence = Evidence::sole();

        $this->actingAs($this->petugas)->post("/evidence/{$evidence->id}/reupload", ['file' => $this->realFile('catatan.txt', '<?php echo 1; ?>')])
            ->assertSessionHasErrors('file');
        $this->assertDatabaseCount('hash_checks', 0);
    }

    public function test_tipe_berkas_terlarang_ditolak_termasuk_yang_menyamar(): void
    {
        // Ekstensi terlarang
        $this->uploadDigital($this->petugas, $this->realFile('virus.exe', 'MZ'))->assertSessionHasErrors('file');
        // Ekstensi diizinkan, tetapi isinya skrip PHP / HTML
        $this->uploadDigital($this->petugas, $this->realFile('catatan.txt', '<?php echo "hai"; ?>'))->assertSessionHasErrors('file');
        $this->uploadDigital($this->petugas, $this->realFile('foto.png', '<html><body>bukan gambar</body></html>'))->assertSessionHasErrors('file');

        $this->assertDatabaseCount('evidence', 0);
    }

    public function test_tambah_bukti_fisik_dan_hanya_admin_yang_bisa_menonaktifkan(): void
    {
        $this->actingAs($this->petugas)->post("/cases/{$this->case->id}/evidence", [
            'type' => 'fisik',
            'recorded_date' => today()->format('Y-m-d'),
            'name' => 'Linggis',
            'storage_location' => 'Lemari A-3',
        ])->assertSessionHasNoErrors();

        $evidence = Evidence::sole();
        $this->assertNull($evidence->file_path);
        $this->actingAs($this->petugas)->get("/cases/{$this->case->id}")->assertSee('Linggis');
        $this->actingAs($this->petugas)->get("/cases/{$this->case->id}/evidence/create?type=fisik")->assertOk()->assertSee('Tambah bukti fisik');
        $this->actingAs($this->petugas)->get("/cases/{$this->case->id}/evidence/create?type=digital")->assertOk()->assertSee('Unggah bukti digital');
        $this->actingAs($this->petugas)->get("/evidence/{$evidence->id}")->assertOk()->assertSee('Lemari A-3');
        $this->actingAs($this->petugas)->get("/evidence/{$evidence->id}/edit")->assertOk();

        $this->actingAs($this->petugas)->patch("/evidence/{$evidence->id}/toggle-active")->assertForbidden();
        $this->actingAs($this->admin)->patch("/evidence/{$evidence->id}/toggle-active");
        $this->assertFalse($evidence->fresh()->is_active);

        // Bukti nonaktif tidak bisa diubah petugas
        $this->actingAs($this->petugas)->get("/evidence/{$evidence->id}/edit")->assertForbidden();
    }
}
