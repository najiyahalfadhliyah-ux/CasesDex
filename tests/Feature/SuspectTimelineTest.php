<?php

namespace Tests\Feature;

use App\Models\CaseRecord;
use App\Models\Evidence;
use App\Models\Suspect;
use App\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuspectTimelineTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $petugas;
    private User $petugasLain;
    private CaseRecord $case;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->petugas = User::factory()->create();
        $this->petugasLain = User::factory()->create();
        $this->case = CaseRecord::factory()->create(['assigned_to' => $this->petugas->id]);
    }

    private function addEvent(User $user, string $time, string $title, array $extra = [])
    {
        return $this->actingAs($user)->post("/cases/{$this->case->id}/timeline-events", $extra + [
            'event_time' => $time,
            'title' => $title,
            'description' => "Uraian {$title}.",
        ]);
    }

    public function test_petugas_mengelola_pihak_terkait_kasusnya_dan_status_hukum_divalidasi(): void
    {
        $this->actingAs($this->petugas)->post("/cases/{$this->case->id}/suspects", [
            'name' => 'Inisial AB',
            'legal_status' => 'dugaan',
            'relation_description' => 'Terlihat di sekitar lokasi.',
            'address' => 'Jl. Contoh 1', // field tidak dikenal diabaikan, tidak tersimpan
        ])->assertSessionHasNoErrors();

        $suspect = Suspect::sole();
        $this->assertSame('dugaan', $suspect->legal_status);
        $this->actingAs($this->petugas)->get("/cases/{$this->case->id}/suspects/create")->assertOk()->assertSee('Jangan isi alamat');
        $this->actingAs($this->petugas)->get("/suspects/{$suspect->id}/edit")->assertOk()->assertSee('Inisial AB');
        $this->actingAs($this->petugas)->get("/cases/{$this->case->id}")->assertSee('Inisial AB')->assertSee('Dugaan keterlibatan');

        $this->actingAs($this->petugas)->put("/suspects/{$suspect->id}", [
            'name' => 'Inisial AB', 'legal_status' => 'bersalah', 'relation_description' => 'x',
        ])->assertSessionHasErrors('legal_status');

        $this->actingAs($this->petugas)->patch("/suspects/{$suspect->id}/toggle-active");
        $this->assertFalse($suspect->fresh()->is_active);
        $this->actingAs($this->petugas)->get("/suspects/{$suspect->id}/edit")->assertForbidden(); // nonaktif tidak bisa diubah
    }

    public function test_petugas_tidak_bisa_mengakses_pihak_terkait_atau_kronologi_kasus_orang_lain(): void
    {
        $suspect = $this->case->suspects()->create(['name' => 'Inisial CD', 'legal_status' => 'tersangka', 'relation_description' => 'x']);
        $event = $this->case->timelineEvents()->create([
            'event_time' => '2026-01-10 08:00', 'title' => 'Laporan masuk', 'description' => 'x', 'recorded_by' => $this->petugas->id,
        ]);
        $as = $this->actingAs($this->petugasLain);

        $as->get("/cases/{$this->case->id}/suspects/create")->assertForbidden();
        $as->post("/cases/{$this->case->id}/suspects", ['name' => 'X', 'legal_status' => 'dugaan', 'relation_description' => 'x'])->assertForbidden();
        $as->get("/suspects/{$suspect->id}/edit")->assertForbidden();
        $as->put("/suspects/{$suspect->id}", ['name' => 'X', 'legal_status' => 'dugaan', 'relation_description' => 'x'])->assertForbidden();
        $as->patch("/suspects/{$suspect->id}/toggle-active")->assertForbidden();

        $as->get("/cases/{$this->case->id}/timeline-events")->assertForbidden();
        $as->get("/cases/{$this->case->id}/timeline-events/create")->assertForbidden();
        $this->addEvent($this->petugasLain, '2026-01-11 09:00', 'Palsu')->assertForbidden();
        $as->get("/timeline-events/{$event->id}/edit")->assertForbidden();
        $as->patch("/timeline-events/{$event->id}/toggle-active")->assertForbidden();
        $as->patch("/cases/{$this->case->id}/analysis", ['summary' => 'Diubah'])->assertForbidden();

        $this->assertSame(1, Suspect::count());
        $this->assertSame(1, TimelineEvent::count());
        $this->assertTrue($suspect->fresh()->is_active);

        // Admin boleh semua kasus
        $this->actingAs($this->admin)->get("/cases/{$this->case->id}/timeline-events")->assertOk();
        $this->actingAs($this->admin)->get("/suspects/{$suspect->id}/edit")->assertOk();
    }

    public function test_timeline_terurut_menurut_waktu_kejadian_bukan_waktu_input(): void
    {
        $evidence = Evidence::create([
            'case_id' => $this->case->id, 'type' => 'fisik', 'name' => 'Linggis', 'storage_location' => 'Lemari A', 'uploaded_by' => $this->petugas->id,
        ]);

        // Dimasukkan acak: ketiga, pertama, kedua
        $this->addEvent($this->petugas, '2026-01-12 10:00', 'Peristiwa ketiga')->assertSessionHasNoErrors();
        $this->addEvent($this->petugas, '2026-01-10 07:30', 'Peristiwa pertama', ['evidence_id' => $evidence->id])->assertSessionHasNoErrors();
        $this->addEvent($this->admin, '2026-01-11 22:15', 'Peristiwa kedua')->assertSessionHasNoErrors();

        $this->actingAs($this->petugas)->get("/cases/{$this->case->id}/timeline-events")
            ->assertOk()
            ->assertSeeInOrder(['Peristiwa pertama', 'Peristiwa kedua', 'Peristiwa ketiga'])
            ->assertSee('Linggis');

        $this->assertDatabaseHas('timeline_events', ['title' => 'Peristiwa kedua', 'recorded_by' => $this->admin->id]);

        $event = TimelineEvent::where('title', 'Peristiwa pertama')->sole();
        $this->actingAs($this->petugas)->get("/cases/{$this->case->id}/timeline-events/create")->assertOk()->assertSee('Linggis');
        $this->actingAs($this->petugas)->get("/timeline-events/{$event->id}/edit")->assertOk()->assertSee('2026-01-10T07:30', false);
    }

    public function test_bukti_terkait_harus_dari_kasus_yang_sama(): void
    {
        $kasusLain = CaseRecord::factory()->create();
        $buktiLain = Evidence::create([
            'case_id' => $kasusLain->id, 'type' => 'fisik', 'name' => 'Bukti lain', 'storage_location' => 'B', 'uploaded_by' => $this->admin->id,
        ]);

        $this->addEvent($this->petugas, '2026-01-10 07:30', 'Uji', ['evidence_id' => $buktiLain->id])->assertSessionHasErrors('evidence_id');
    }

    public function test_ringkasan_dan_dugaan_motif_tersimpan_di_tabel_cases(): void
    {
        $this->actingAs($this->petugas)->patch("/cases/{$this->case->id}/analysis", ['summary' => 'Ringkasan baru.'])
            ->assertRedirect("/cases/{$this->case->id}/timeline-events");
        $this->actingAs($this->petugas)->patch("/cases/{$this->case->id}/analysis", ['suspected_motive' => 'Motif ekonomi.']);

        // Mengirim satu field tidak menghapus field yang lain
        $this->assertDatabaseHas('cases', [
            'id' => $this->case->id, 'summary' => 'Ringkasan baru.', 'suspected_motive' => 'Motif ekonomi.',
        ]);
        $this->actingAs($this->petugas)->get("/cases/{$this->case->id}/timeline-events")
            ->assertSeeInOrder(['Ringkasan kasus', 'Ringkasan baru.', 'Dugaan motif', 'Motif ekonomi.']);
    }
}
