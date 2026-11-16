<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CaseRecord;
use App\Models\Evidence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseSearchArchiveTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $petugas;
    private CaseRecord $milikSaya;
    private CaseRecord $milikLain;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->petugas = User::factory()->create();
        $lain = User::factory()->create();

        $this->milikSaya = CaseRecord::factory()->create([
            'case_number' => 'AK-1999-0001', 'title' => 'Pencurian Gudang Utara', 'category' => 'Pencurian',
            'incident_date' => '2025-03-01', 'assigned_to' => $this->petugas->id,
        ]);
        $this->milikLain = CaseRecord::factory()->create([
            'case_number' => 'AK-1999-0002', 'title' => 'Penipuan Daring Selatan', 'category' => 'Penipuan',
            'incident_date' => '2026-02-01', 'status' => 'selesai', 'assigned_to' => $lain->id,
        ]);
        Evidence::create(['case_id' => $this->milikLain->id, 'type' => 'digital', 'name' => 'Rekaman CCTV',
            'original_name' => 'rahasia-lain.pcap', 'uploaded_by' => $lain->id]);
        Evidence::create(['case_id' => $this->milikSaya->id, 'type' => 'fisik', 'name' => 'Linggis', 'uploaded_by' => $this->petugas->id]);
    }

    public function test_petugas_tidak_menemukan_kasus_orang_lain_lewat_pencarian(): void
    {
        $this->actingAs($this->petugas);

        // Tahu nomor kasus, judul, atau nama berkas orang lain tetap tidak ketemu
        // (kata kunci tampil lagi di kolom cari, jadi yang dicek tautan detail & judul lengkap)
        foreach (['AK-1999-0002', 'Penipuan Daring', 'rahasia-lain', 'CCTV'] as $q) {
            $this->get('/cases?archive_status=semua&q='.urlencode($q))->assertOk()
                ->assertDontSee(route('cases.show', $this->milikLain))->assertDontSee('Penipuan Daring Selatan')
                ->assertSee('0 kasus ditemukan');
        }
        // Kata kunci yang cocok ke kedua kasus hanya mengembalikan miliknya (OR tidak lolos dari batas akses)
        $this->get('/cases?q=AK-1999')->assertSee('AK-1999-0001')->assertDontSee('AK-1999-0002');
        $this->get("/cases/{$this->milikLain->id}")->assertForbidden();

        // Admin menemukannya
        $this->actingAs($this->admin)->get('/cases?q=rahasia-lain')->assertSee('AK-1999-0002')->assertDontSee('AK-1999-0001');
    }

    public function test_filter_bekerja_dan_terbawa_ke_paginasi(): void
    {
        $this->actingAs($this->admin);

        $this->get('/cases?category=Pencurian')->assertSee('AK-1999-0001')->assertDontSee('AK-1999-0002');
        $this->get('/cases?year=2026')->assertSee('AK-1999-0002')->assertDontSee('AK-1999-0001');
        $this->get('/cases?status=selesai')->assertSee('AK-1999-0002')->assertDontSee('AK-1999-0001');
        $this->get('/cases?evidence_type=fisik')->assertSee('AK-1999-0001')->assertDontSee('AK-1999-0002');
        $this->get('/cases?q=Linggis')->assertSee('AK-1999-0001')->assertDontSee('AK-1999-0002');
        $this->get('/cases?status=bukan')->assertSessionHasErrors('status');

        CaseRecord::factory()->count(16)->create(['category' => 'Perusakan']);
        $this->get('/cases?category=Perusakan')->assertSee('category=Perusakan&amp;page=2', false);
    }

    public function test_kasus_diarsipkan_hilang_dari_daftar_aktif_tapi_bisa_dicari(): void
    {
        $this->milikSaya->update(['archive_status' => 'diarsipkan', 'archived_at' => today(), 'archive_reason' => 'mencapai_6_bulan']);
        $this->actingAs($this->petugas);

        $this->get('/cases')->assertDontSee('AK-1999-0001');
        $this->get('/cases?archive_status=diarsipkan')->assertSee('AK-1999-0001');
        $this->get('/archives')->assertOk()->assertSee('AK-1999-0001')->assertSee('Mencapai enam bulan');
    }

    public function test_arsip_dan_buka_arsip_hanya_admin_dan_tercatat(): void
    {
        $id = $this->milikSaya->id;

        $this->actingAs($this->petugas)->post("/cases/{$id}/archive", ['archive_reason' => 'mencapai_6_bulan'])->assertForbidden();
        $this->actingAs($this->petugas)->delete("/cases/{$id}/archive")->assertForbidden();
        $this->assertSame('aktif', $this->milikSaya->fresh()->archive_status);

        $this->actingAs($this->admin)->post("/cases/{$id}/archive", ['archive_reason' => 'bukan'])->assertSessionHasErrors('archive_reason');
        $this->actingAs($this->admin)->post("/cases/{$id}/archive", ['archive_reason' => 'selesai_sebelum_6_bulan'])->assertRedirect("/cases/{$id}");
        $case = $this->milikSaya->fresh();
        $this->assertSame('diarsipkan', $case->archive_status);
        $this->assertTrue($case->archived_at->isToday());

        $log = AuditLog::where('action', 'case.archived')->sole();
        $this->assertSame($this->admin->id, $log->user_id);
        $this->assertSame('Selesai sebelum enam bulan', $log->changes['archive_reason']['new']);

        // Petugas pemilik tetap bisa melihat arsip kasusnya
        $this->actingAs($this->petugas)->get('/archives')->assertSee('AK-1999-0001');

        $this->actingAs($this->admin)->delete("/cases/{$id}/archive")->assertRedirect("/cases/{$id}");
        $this->assertSame('aktif', $this->milikSaya->fresh()->archive_status);
        $this->assertSame($this->admin->id, AuditLog::where('action', 'case.unarchived')->sole()->user_id);
    }
}
