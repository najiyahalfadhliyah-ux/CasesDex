<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CaseRecord;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use LogicException;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $petugas;
    private CaseRecord $case;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin', 'username' => 'admin']);
        $this->petugas = User::factory()->create(['username' => 'petugas1', 'password' => 'password123']);
        $this->case = CaseRecord::factory()->create(['assigned_to' => $this->petugas->id, 'status' => 'dalam_penanganan']);
    }

    private function lastLog(string $action): AuditLog
    {
        return AuditLog::where('action', $action)->latest('id')->firstOrFail();
    }

    public function test_login_logout_gagal_dan_terkunci_tercatat(): void
    {
        $this->post('/login', ['login' => 'petugas1', 'password' => 'password123']);
        $this->assertSame($this->petugas->id, $this->lastLog('auth.login')->user_id);

        $this->post('/logout');
        $this->assertSame($this->petugas->id, $this->lastLog('auth.logout')->user_id);

        foreach (range(1, 5) as $i) {
            $this->post('/login', ['login' => 'petugas1', 'password' => 'SalahSekali99']);
        }
        $this->assertSame(5, AuditLog::where('action', 'auth.login_failed')->count());
        $this->assertSame(1, AuditLog::where('action', 'auth.locked')->count());
        $this->assertNull($this->lastLog('auth.login_failed')->user_id); // Sistem

        // Sandi yang diketik tidak pernah tersimpan di log
        $this->assertSame(0, AuditLog::where('summary', 'like', '%SalahSekali99%')->orWhere('changes', 'like', '%SalahSekali99%')->count());
    }

    public function test_aksi_kasus_tercatat_dengan_nilai_sebelum_dan_sesudah(): void
    {
        $this->assertDatabaseHas('audit_logs', ['action' => 'case.created', 'auditable_id' => $this->case->id]);

        $this->actingAs($this->petugas)->patch("/cases/{$this->case->id}/status", ['status' => 'ditangguhkan']);
        $log = $this->lastLog('case.status');
        $this->assertSame($this->petugas->id, $log->user_id);
        $this->assertSame(['status' => ['old' => 'Dalam penanganan', 'new' => 'Ditangguhkan']], $log->changes);

        $this->actingAs($this->petugas)->patch("/cases/{$this->case->id}/analysis", ['summary' => 'Ringkasan baru', 'suspected_motive' => 'Ekonomi']);
        $this->assertSame('Ekonomi', $this->lastLog('case.analysis')->changes['suspected_motive']['new']);

        // Satu simpan, dua jenis aktivitas: ubah informasi + penugasan
        $lain = User::factory()->create(['name' => 'Petugas Lain']);
        $this->actingAs($this->admin);
        $this->case->update(['title' => 'Judul Baru', 'assigned_to' => $lain->id]);
        $this->assertSame('Judul Baru', $this->lastLog('case.updated')->changes['title']['new']);
        $this->assertSame('Petugas Lain', $this->lastLog('case.assigned')->changes['assigned_to']['new']);

        $this->actingAs($this->admin)->delete("/cases/{$this->case->id}");
        $this->assertSame($this->admin->id, $this->lastLog('case.deleted')->user_id);
    }

    public function test_bukti_pihak_terkait_kronologi_dan_hash_tercatat(): void
    {
        Storage::fake('local');
        $this->actingAs($this->petugas)->post("/cases/{$this->case->id}/evidence", [
            'type' => 'digital',
            'recorded_date' => today()->format('Y-m-d'),
            'file' => UploadedFile::fake()->createWithContent('catatan.txt', 'isi rahasia bukti'),
        ])->assertSessionHasNoErrors();
        $evidence = Evidence::sole();
        $this->assertDatabaseHas('audit_logs', ['action' => 'evidence.created', 'auditable_id' => $evidence->id]);

        $this->actingAs($this->petugas)->post("/evidence/{$evidence->id}/hash-check");
        $this->assertStringContainsString('hasil: cocok', $this->lastLog('evidence.hash_check')->summary);

        $this->actingAs($this->admin)->patch("/evidence/{$evidence->id}/toggle-active");
        $this->assertDatabaseHas('audit_logs', ['action' => 'evidence.deactivated', 'auditable_id' => $evidence->id]);

        $this->actingAs($this->petugas)->post("/cases/{$this->case->id}/suspects", ['name' => 'Inisial AB', 'legal_status' => 'dugaan', 'relation_description' => 'x']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'suspect.created']);

        $this->actingAs($this->petugas)->post("/cases/{$this->case->id}/timeline-events", ['event_time' => '2026-01-10 08:00', 'title' => 'Laporan', 'description' => 'x']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'timeline.created']);

        // Isi berkas bukti tidak ikut tersimpan
        $this->assertSame(0, AuditLog::where('summary', 'like', '%isi rahasia%')->orWhere('changes', 'like', '%isi rahasia%')->count());
    }

    public function test_perubahan_akun_tercatat_tanpa_kata_sandi(): void
    {
        $this->actingAs($this->admin)->put("/users/{$this->petugas->id}", [
            'name' => $this->petugas->name,
            'email' => $this->petugas->email,
            'username' => 'petugas1',
            'role' => 'admin',
            'password' => 'RahasiaBaru123',
            'password_confirmation' => 'RahasiaBaru123',
        ])->assertSessionHasNoErrors();

        $this->assertSame(['role' => ['old' => 'petugas', 'new' => 'admin']], $this->lastLog('user.role')->changes);
        $this->assertSame(['old' => null, 'new' => '(diubah)'], $this->lastLog('user.updated')->changes['password']);

        $this->actingAs($this->admin)->patch("/users/{$this->petugas->id}/toggle-active");
        $this->assertDatabaseHas('audit_logs', ['action' => 'user.deactivated', 'auditable_id' => $this->petugas->id]);

        // Baik teks asli maupun hash sandi tidak ada di log
        $hash = $this->petugas->fresh()->password;
        foreach (['RahasiaBaru123', $hash] as $secret) {
            $this->assertSame(0, AuditLog::where('summary', 'like', "%{$secret}%")->orWhere('changes', 'like', "%{$secret}%")->count());
        }
    }

    public function test_hanya_admin_yang_bisa_melihat_dan_memfilter(): void
    {
        $this->actingAs($this->petugas)->get('/audit-logs')->assertForbidden();

        $this->actingAs($this->petugas)->patch("/cases/{$this->case->id}/status", ['status' => 'ditangguhkan']);

        $this->actingAs($this->admin)->get('/audit-logs')->assertOk()->assertSee('Ubah status kasus')->assertSee('Ditangguhkan');
        $this->actingAs($this->admin)->get('/audit-logs?action=auth.login')->assertOk()->assertDontSee('Ditangguhkan');
        $this->actingAs($this->admin)->get('/audit-logs?action=bukan.aksi')->assertSessionHasErrors('action');
    }

    public function test_catatan_tidak_bisa_diubah_atau_dihapus(): void
    {
        $log = AuditLog::firstOrFail();

        try {
            $log->update(['action' => 'dipalsukan']);
            $this->fail('Update seharusnya ditolak');
        } catch (LogicException) {
        }

        $this->expectException(LogicException::class);
        $log->delete();
    }

    public function test_tidak_ada_route_ubah_atau_hapus_audit_log(): void
    {
        $id = AuditLog::firstOrFail()->id;

        $this->actingAs($this->admin)->delete("/audit-logs/{$id}")->assertNotFound();
        $this->actingAs($this->admin)->put("/audit-logs/{$id}")->assertNotFound();
        $this->assertDatabaseHas('audit_logs', ['id' => $id]);
    }
}
