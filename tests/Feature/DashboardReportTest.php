<?php

namespace Tests\Feature;

use App\Models\CaseRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardReportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $petugas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->petugas = User::factory()->create();
        $lain = User::factory()->create();
        $jauh = today()->addYear()->format('Y-m-d'); // tenggat jauh: tidak masuk pengingat

        // Milik petugas: 2 kasus (satu selesai + diarsipkan), satu mendekati tenggat arsip
        CaseRecord::factory()->create(['case_number' => 'AK-1999-0001', 'category' => 'Pencurian', 'incident_date' => '2025-05-01',
            'assigned_to' => $this->petugas->id, 'archive_deadline' => today()->addDays(10)->format('Y-m-d')]);
        CaseRecord::factory()->create(['case_number' => 'AK-1999-0002', 'category' => 'Penipuan', 'incident_date' => '2026-02-01',
            'status' => 'selesai', 'archive_status' => 'diarsipkan', 'assigned_to' => $this->petugas->id, 'archive_deadline' => $jauh]);
        // Milik petugas lain: sudah lewat tenggat
        CaseRecord::factory()->create(['case_number' => 'AK-1999-0003', 'category' => 'Pencurian', 'incident_date' => '2026-03-01',
            'assigned_to' => $lain->id, 'archive_deadline' => today()->subDays(3)->format('Y-m-d')]);
        // Nonaktif (soft delete): tidak pernah dihitung
        CaseRecord::factory()->create(['category' => 'Perusakan', 'incident_date' => '2026-04-01', 'archive_deadline' => $jauh])->delete();
    }

    public function test_angka_dashboard_admin_benar(): void
    {
        $this->actingAs($this->admin)->get('/dashboard')->assertOk()
            ->assertViewHas('stats', function ($stats) {
                $this->assertSame(['total' => 3, 'dalam_penanganan' => 2, 'selesai' => 1, 'diarsipkan' => 1], $stats['summary']);
                $this->assertSame([2025 => 1, 2026 => 2], $stats['by_year']);
                $this->assertSame(['Pencurian' => 2, 'Penipuan' => 1, 'Kejahatan Siber' => 0, 'Perusakan' => 0, 'Lainnya' => 0], $stats['by_category']);
                $this->assertSame(['dalam_penanganan' => 2, 'selesai' => 1, 'ditutup' => 0, 'ditangguhkan' => 0], $stats['by_status']);

                return true;
            })
            // Pengingat: dekat tenggat + sudah lewat, urut tenggat; kasus diarsipkan tidak diingatkan
            ->assertViewHas('reminders', fn ($r) => $r->pluck('case_number')->all() === ['AK-1999-0003', 'AK-1999-0001'])
            ->assertSee('Lewat 3 hari')->assertSee('Sisa 10 hari');
    }

    public function test_petugas_hanya_menghitung_kasus_miliknya(): void
    {
        $this->actingAs($this->petugas)->get('/dashboard')->assertOk()
            ->assertViewHas('stats', fn ($s) => $s['summary'] === ['total' => 2, 'dalam_penanganan' => 1, 'selesai' => 1, 'diarsipkan' => 1]
                && $s['by_year'] === [2025 => 1, 2026 => 1] && $s['by_category']['Pencurian'] === 1)
            ->assertViewHas('reminders', fn ($r) => $r->pluck('case_number')->all() === ['AK-1999-0001'])
            ->assertViewHas('latest', fn ($l) => ! $l->contains('case_number', 'AK-1999-0003'))
            ->assertDontSee('AK-1999-0003');

        $this->actingAs($this->petugas)->get('/reports')->assertOk()
            ->assertViewHas('stats', fn ($s) => $s['summary']['total'] === 2);
    }

    public function test_filter_periode_laporan_bekerja(): void
    {
        $this->actingAs($this->admin);

        $this->get('/reports?from=2026-01-01')->assertOk()
            ->assertViewHas('stats', fn ($s) => $s['summary']['total'] === 2 && $s['by_year'] === [2026 => 2]);
        $this->get('/reports?from=2026-01-01&to=2026-02-28')
            ->assertViewHas('stats', fn ($s) => $s['summary']['total'] === 1 && $s['by_category']['Penipuan'] === 1);
        $this->get('/reports?from=2026-05-01&to=2026-01-01')->assertSessionHasErrors('to');
    }
}
