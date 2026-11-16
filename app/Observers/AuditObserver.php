<?php

namespace App\Observers;

use App\Models\CaseRecord;
use App\Models\Evidence;
use App\Models\HashCheck;
use App\Models\Suspect;
use App\Models\TimelineEvent;
use App\Models\User;
use App\Services\AuditLogger;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

// Mencatat perubahan data ke audit log. Didaftarkan di AppServiceProvider.
// Event Eloquent berjalan di dalam save(), jadi kalau save dibungkus transaksi, catatan ikut transaksi itu.
class AuditObserver
{
    private const PREFIX = [
        CaseRecord::class => 'case', Evidence::class => 'evidence', Suspect::class => 'suspect',
        TimelineEvent::class => 'timeline', User::class => 'user',
    ];

    // Field yang punya jenis aktivitas sendiri; field lain masuk "<prefix>.updated"
    private const SPECIAL = [
        CaseRecord::class => [
            'status' => 'case.status', 'assigned_to' => 'case.assigned',
            'summary' => 'case.analysis', 'suspected_motive' => 'case.analysis',
        ],
        User::class => ['role' => 'user.role'],
    ];

    private const IGNORED = ['created_at', 'updated_at', 'remember_token'];

    public function __construct(private AuditLogger $logger) {}

    public function created(Model $model): void
    {
        if ($model instanceof HashCheck) {
            $this->hashCheck($model);

            return;
        }

        $this->logger->log(self::PREFIX[$model::class].'.created', $model, $this->label($model));
    }

    public function updated(Model $model): void
    {
        $groups = [];

        foreach ($model->getChanges() as $field => $new) {
            if (in_array($field, self::IGNORED, true)) {
                continue;
            }

            $action = match (true) {
                $field === 'is_active' => self::PREFIX[$model::class].($model->is_active ? '.activated' : '.deactivated'),
                // Tiga kolom arsip berubah bersama -> satu catatan arsip / buka arsip
                $model instanceof CaseRecord && in_array($field, ['archive_status', 'archived_at', 'archive_reason'], true)
                    => $model->archive_status === 'diarsipkan' ? 'case.archived' : 'case.unarchived',
                default => self::SPECIAL[$model::class][$field] ?? self::PREFIX[$model::class].'.updated',
            };

            $groups[$action][$field] = [
                'old' => $this->display($field, $model->getOriginal($field)),
                'new' => $this->display($field, $model->getAttribute($field)),
            ];
        }

        // Satu simpan bisa menghasilkan beberapa catatan, mis. ubah judul + ganti petugas
        foreach ($groups as $action => $changes) {
            $this->logger->log($action, $model, $this->label($model), $changes);
        }
    }

    public function deleted(Model $model): void
    {
        $this->logger->log(self::PREFIX[$model::class].'.deleted', $model, $this->label($model));
    }

    private function hashCheck(HashCheck $check): void
    {
        $result = $check->computed_hash === '' ? 'berkas tidak ditemukan' : ($check->is_match ? 'cocok' : 'tidak cocok');
        $reupload = $check->action === 'unggah_ulang';

        $this->logger->log(
            $reupload ? 'evidence.reupload' : 'evidence.hash_check',
            $check->evidence,
            $this->label($check->evidence).' · hasil: '.$result.($reupload ? " · berkas \"{$check->original_name}\"" : ''),
            ['sha256' => ['old' => $check->stored_hash, 'new' => $check->computed_hash ?: null]],
        );
    }

    // Nama singkat data agar baris log mudah dikenali tanpa membuka datanya
    private function label(Model $model): string
    {
        return match (true) {
            $model instanceof CaseRecord => "{$model->case_number} · {$model->title}",
            $model instanceof User => "{$model->name} ({$model->username})",
            $model instanceof TimelineEvent => "{$model->title} · kasus {$model->case?->case_number}",
            default => "{$model->name} · kasus {$model->case?->case_number}",
        };
    }

    // Ubah nilai mentah menjadi teks yang mudah dibaca sebelum disimpan
    private function display(string $field, mixed $value): mixed
    {
        return match (true) {
            $value instanceof DateTimeInterface => $value->format($value->format('H:i') === '00:00' ? 'd/m/Y' : 'd/m/Y H:i'),
            $field === 'assigned_to' => $value ? User::find($value)?->name : null,
            $field === 'status' => CaseRecord::STATUSES[$value] ?? $value,
            $field === 'archive_status' => CaseRecord::ARCHIVE_STATUSES[$value] ?? $value,
            $field === 'archive_reason' => CaseRecord::ARCHIVE_REASONS[$value] ?? $value,
            default => $value,
        };
    }
}
