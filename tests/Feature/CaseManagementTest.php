<?php

namespace Tests\Feature;

use App\Models\CaseRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaseManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $petugas;

    protected function setUp(): void
    {
        parent::setUp();
        $this->travelTo('2026-11-08');
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->petugas = User::factory()->create();
    }

    private function payload(array $override = []): array
    {
        return $override + [
            'title' => 'Pencurian Motor',
            'category' => 'Pencurian',
            'location' => 'Parkiran Kampus',
            'incident_date' => '2026-11-01',
            'investigation_start_date' => '2026-11-02',
            'description' => 'Motor hilang di parkiran.',
            'status' => 'dalam_penanganan',
            'assigned_to' => $this->petugas->id,
        ];
    }

    public function test_admin_menambah_kasus_dengan_nomor_otomatis_berurutan(): void
    {
        $this->actingAs($this->admin)->get('/cases/create')->assertOk();

        $this->actingAs($this->admin)->post('/cases', $this->payload())->assertSessionHas('success');
        $this->actingAs($this->admin)->post('/cases', $this->payload());

        $this->assertDatabaseHas('cases', ['case_number' => 'AK-2026-0001', 'created_by' => $this->admin->id, 'assigned_to' => $this->petugas->id]);
        $this->assertDatabaseHas('cases', ['case_number' => 'AK-2026-0002']);

        $case = CaseRecord::where('case_number', 'AK-2026-0001')->first();
        $this->assertSame('2026-11-08', $case->recorded_date->format('Y-m-d'));
        $this->assertSame('2027-05-02', $case->archive_deadline->format('Y-m-d')); // mulai investigasi + 6 bulan
    }

    public function test_nomor_reset_tiap_tahun_dan_nomor_kasus_nonaktif_tidak_dipakai_ulang(): void
    {
        $this->actingAs($this->admin)->post('/cases', $this->payload());
        CaseRecord::where('case_number', 'AK-2026-0001')->first()->delete();

        $this->actingAs($this->admin)->post('/cases', $this->payload());
        $this->assertDatabaseHas('cases', ['case_number' => 'AK-2026-0002']);

        $this->travelTo('2027-01-02');
        $this->actingAs($this->admin)->post('/cases', $this->payload());
        $this->assertDatabaseHas('cases', ['case_number' => 'AK-2027-0001']);
    }

    public function test_status_selesai_wajib_tanggal_selesai_dan_input_lama_dipertahankan(): void
    {
        $this->actingAs($this->admin)->from('/cases/create')
            ->post('/cases', $this->payload(['status' => 'selesai']))
            ->assertRedirect('/cases/create')
            ->assertSessionHasErrors('completed_date')
            ->assertSessionHasInput('title', 'Pencurian Motor');

        $this->actingAs($this->admin)
            ->post('/cases', $this->payload(['status' => 'selesai', 'completed_date' => '2026-11-05']))
            ->assertSessionHasNoErrors();
    }

    public function test_petugas_hanya_melihat_dan_mengubah_kasus_sendiri(): void
    {
        $milikSendiri = CaseRecord::factory()->create(['assigned_to' => $this->petugas->id, 'title' => 'Kasus Saya']);
        $milikOrang = CaseRecord::factory()->create(['title' => 'Kasus Orang Lain']);

        $this->actingAs($this->petugas)->get('/cases')->assertOk()->assertSee('Kasus Saya')->assertDontSee('Kasus Orang Lain');

        $this->actingAs($this->petugas)->get("/cases/{$milikOrang->id}")->assertForbidden();
        $this->actingAs($this->petugas)->get("/cases/{$milikOrang->id}/edit")->assertForbidden();
        $this->actingAs($this->petugas)->put("/cases/{$milikOrang->id}", $this->payload())->assertForbidden();

        $this->actingAs($this->petugas)->get("/cases/{$milikSendiri->id}")->assertOk();
        $this->actingAs($this->petugas)->get("/cases/{$milikSendiri->id}/edit")->assertOk();
    }

    public function test_petugas_tidak_bisa_memindahkan_penugasan_saat_mengubah(): void
    {
        $case = CaseRecord::factory()->create(['assigned_to' => $this->petugas->id]);
        $lain = User::factory()->create();

        $this->actingAs($this->petugas)
            ->put("/cases/{$case->id}", $this->payload(['title' => 'Judul Baru', 'assigned_to' => $lain->id]))
            ->assertRedirect("/cases/{$case->id}");

        $case->refresh();
        $this->assertSame('Judul Baru', $case->title);
        $this->assertSame($this->petugas->id, $case->assigned_to);
    }

    public function test_petugas_tidak_bisa_menambah_atau_menonaktifkan_kasus(): void
    {
        $case = CaseRecord::factory()->create(['assigned_to' => $this->petugas->id]);

        $this->actingAs($this->petugas)->get('/cases/create')->assertForbidden();
        $this->actingAs($this->petugas)->post('/cases', $this->payload())->assertForbidden();
        $this->actingAs($this->petugas)->delete("/cases/{$case->id}")->assertForbidden();
        $this->assertNotSoftDeleted($case);
    }

    public function test_admin_menonaktifkan_kasus_dengan_soft_delete(): void
    {
        $case = CaseRecord::factory()->create(['assigned_to' => $this->petugas->id]);

        $this->actingAs($this->admin)->get("/cases/{$case->id}")->assertOk()->assertSee('Nonaktifkan');
        $this->actingAs($this->admin)->get("/cases/{$case->id}/edit")->assertOk()->assertSee('Tenggat arsip')->assertSee($this->petugas->name);

        $this->actingAs($this->admin)->delete("/cases/{$case->id}")->assertRedirect('/cases');

        $this->assertSoftDeleted($case);
        $this->actingAs($this->admin)->get("/cases/{$case->id}")->assertNotFound();
    }

    public function test_tidak_bisa_menugaskan_petugas_nonaktif_atau_admin(): void
    {
        $nonaktif = User::factory()->create(['is_active' => false]);

        $this->actingAs($this->admin)->post('/cases', $this->payload(['assigned_to' => $nonaktif->id]))->assertSessionHasErrors('assigned_to');
        $this->actingAs($this->admin)->post('/cases', $this->payload(['assigned_to' => $this->admin->id]))->assertSessionHasErrors('assigned_to');
    }

    public function test_kolom_petugas_hanya_untuk_admin_dan_ada_tombol_detail_ubah(): void
    {
        $case = CaseRecord::factory()->create(['assigned_to' => $this->petugas->id]);

        $this->actingAs($this->admin)->get('/cases')
            ->assertSee('<th scope="col" class="d-none d-md-table-cell">Petugas</th>', false)
            ->assertSee(route('cases.show', $case))->assertSee(route('cases.edit', $case));

        $this->actingAs($this->petugas)->get('/cases')
            ->assertDontSee('<th scope="col" class="d-none d-md-table-cell">Petugas</th>', false)
            ->assertSee(route('cases.show', $case))->assertSee(route('cases.edit', $case));
    }

    public function test_daftar_urut_nomor_kasus_terbesar_di_atas(): void
    {
        // Dibuat sengaja tidak berurutan; created_at terbaru justru nomor terkecil
        CaseRecord::factory()->create(['case_number' => 'AK-2026-0002']);
        CaseRecord::factory()->create(['case_number' => 'AK-2025-0009']);
        CaseRecord::factory()->create(['case_number' => 'AK-2026-0010']);
        $this->travel(1)->minutes();
        CaseRecord::factory()->create(['case_number' => 'AK-2026-0001']);

        $this->actingAs($this->admin)->get('/cases')
            ->assertSeeInOrder(['AK-2026-0010', 'AK-2026-0002', 'AK-2026-0001', 'AK-2025-0009']);
    }

    public function test_ubah_status_dari_detail_dan_selesai_wajib_tanggal(): void
    {
        $case = CaseRecord::factory()->create(['assigned_to' => $this->petugas->id]);
        $lain = CaseRecord::factory()->create();

        $this->actingAs($this->petugas)->get("/cases/{$case->id}")->assertSee('Simpan status');

        $this->actingAs($this->petugas)->patch("/cases/{$case->id}/status", ['status' => 'selesai'])
            ->assertSessionHasErrors('completed_date');
        $this->assertSame('dalam_penanganan', $case->fresh()->status);

        $this->actingAs($this->petugas)->patch("/cases/{$case->id}/status", ['status' => 'selesai', 'completed_date' => '2026-11-05'])
            ->assertRedirect("/cases/{$case->id}")->assertSessionHas('success');
        $this->assertSame('selesai', $case->fresh()->status);
        $this->assertSame('2026-11-05', $case->fresh()->completed_date->format('Y-m-d'));

        // Kasus milik orang lain: 403
        $this->actingAs($this->petugas)->patch("/cases/{$lain->id}/status", ['status' => 'ditutup'])->assertForbidden();

        // Admin boleh ubah status kasus mana pun
        $this->actingAs($this->admin)->patch("/cases/{$lain->id}/status", ['status' => 'ditangguhkan'])->assertSessionHasNoErrors();
        $this->assertSame('ditangguhkan', $lain->fresh()->status);
    }
}
