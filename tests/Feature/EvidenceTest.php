<?php

namespace Tests\Feature;

use App\Models\CaseRecord;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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

        $this->actingAs($petugasLain)->get("/evidence/{$evidence->id}/download")->assertForbidden();
        $this->actingAs($petugasLain)->get("/evidence/{$evidence->id}")->assertForbidden();
        $this->actingAs($petugasLain)->post("/evidence/{$evidence->id}/hash-check")->assertForbidden();
        $this->uploadDigital($petugasLain, $this->realFile('lain.txt', 'x'))->assertForbidden();

        $this->actingAs($this->petugas)->get("/evidence/{$evidence->id}/download")->assertOk()->assertDownload('catatan.txt');
        $this->actingAs($this->admin)->get("/evidence/{$evidence->id}/download")->assertOk();
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
