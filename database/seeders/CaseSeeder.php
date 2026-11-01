<?php

namespace Database\Seeders;

use App\Models\CaseRecord;
use App\Models\Suspect;
use App\Models\TimelineEvent;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class CaseSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('username', 'admin')->first();
        $petugas = User::where('username', 'petugas1')->first();

        $data = [
            ['Pencurian Laptop Kantor', 'Pencurian', '2026-01-10', '2026-01-12', 'selesai', '2026-03-05', $petugas->id],
            ['Akses Ilegal Server Internal', 'Kejahatan Siber', '2026-02-03', '2026-02-05', 'dalam_penanganan', null, $petugas->id],
            ['Penipuan Jual Beli Online', 'Penipuan', '2026-03-15', '2026-03-16', 'dalam_penanganan', null, $petugas->id],
            ['Perusakan Fasilitas Parkir', 'Perusakan', '2026-04-20', '2026-04-21', 'ditangguhkan', null, null],
            ['Kebocoran Data Pelanggan', 'Kejahatan Siber', '2026-05-02', '2026-05-03', 'selesai', '2026-06-20', null],
        ];

        foreach ($data as $i => [$title, $category, $incident, $start, $status, $done, $officerId]) {
            $startDate = Carbon::parse($start);

            $case = CaseRecord::create([
                'case_number' => sprintf('AK-2026-%04d', $i + 1),
                'title' => $title,
                'category' => $category,
                'incident_date' => $incident,
                'recorded_date' => $start,
                'investigation_start_date' => $start,
                'completed_date' => $done,
                'location' => 'Lokasi simulasi ' . ($i + 1),
                'description' => 'Deskripsi singkat kasus fiktif untuk pengujian.',
                'summary' => 'Ringkasan kasus fiktif nomor ' . ($i + 1) . '.',
                'suspected_motive' => 'Dugaan motif fiktif untuk pengujian.',
                'status' => $status,
                'archive_deadline' => $startDate->copy()->addMonths(6),
                'assigned_to' => $officerId,
                'created_by' => $admin->id,
            ]);

            Suspect::create([
                'case_id' => $case->id,
                'name' => 'Tersangka Samaran ' . ($i + 1),
                'legal_status' => 'dugaan',
                'relation_description' => 'Pihak terkait fiktif.',
            ]);

            TimelineEvent::create([
                'case_id' => $case->id,
                'event_time' => $incident . ' 09:00:00',
                'title' => 'Kejadian dilaporkan',
                'description' => 'Laporan awal diterima.',
                'recorded_by' => $admin->id,
            ]);
        }
    }
}