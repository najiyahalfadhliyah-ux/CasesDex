<?php

namespace Database\Factories;

use App\Models\CaseRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<CaseRecord>
 */
class CaseRecordFactory extends Factory
{
    protected $model = CaseRecord::class;

    public function definition(): array
    {
        return [
            'case_number' => fake()->unique()->bothify('AK-1999-####'), // tahun lampau agar tidak bentrok dengan nomor otomatis
            'title' => fake()->sentence(3),
            'category' => fake()->randomElement(CaseRecord::CATEGORIES),
            'location' => fake()->city(),
            'incident_date' => '2026-01-10',
            'recorded_date' => '2026-01-11',
            'investigation_start_date' => '2026-01-11',
            'description' => 'Deskripsi fiktif.',
            'status' => 'dalam_penanganan',
            'archive_deadline' => '2026-07-11',
            'assigned_to' => null,
            'created_by' => User::factory()->state(['role' => 'admin']),
        ];
    }
}
