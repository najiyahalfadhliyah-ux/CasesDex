<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

// Append-only: catatan hanya boleh dibuat, tidak bisa diubah atau dihapus lewat model.
// Semua catatan dibuat lewat App\Services\AuditLogger.
class AuditLog extends Model
{
    public $timestamps = false;

    // Kode aktivitas => label tampilan (dipakai filter dan daftar)
    public const ACTIONS = [
        'auth.login' => 'Login',
        'auth.logout' => 'Logout',
        'auth.login_failed' => 'Login gagal',
        'auth.locked' => 'Login terkunci',
        'case.created' => 'Tambah kasus',
        'case.updated' => 'Ubah informasi kasus',
        'case.status' => 'Ubah status kasus',
        'case.assigned' => 'Penugasan petugas',
        'case.analysis' => 'Ubah ringkasan/dugaan motif',
        'case.deleted' => 'Nonaktifkan kasus',
        'case.archived' => 'Arsipkan kasus',
        'case.unarchived' => 'Buka arsip kasus',
        'evidence.created' => 'Tambah barang bukti',
        'evidence.updated' => 'Ubah barang bukti',
        'evidence.deactivated' => 'Nonaktifkan barang bukti',
        'evidence.activated' => 'Aktifkan barang bukti',
        'evidence.reupload' => 'Unggah ulang berkas bukti',
        'evidence.hash_check' => 'Pemeriksaan hash SHA-256',
        'suspect.created' => 'Tambah pihak terkait',
        'suspect.updated' => 'Ubah pihak terkait',
        'suspect.deactivated' => 'Nonaktifkan pihak terkait',
        'suspect.activated' => 'Aktifkan pihak terkait',
        'timeline.created' => 'Tambah kronologi',
        'timeline.updated' => 'Ubah kronologi',
        'timeline.deactivated' => 'Nonaktifkan kronologi',
        'timeline.activated' => 'Aktifkan kronologi',
        'user.created' => 'Tambah akun',
        'user.updated' => 'Ubah akun',
        'user.role' => 'Ubah peran akun',
        'user.deactivated' => 'Nonaktifkan akun',
        'user.activated' => 'Aktifkan akun',
    ];

    // Nama kolom => label field di ringkasan perubahan
    public const FIELDS = [
        'title' => 'Judul', 'category' => 'Kategori', 'incident_date' => 'Tanggal kejadian',
        'investigation_start_date' => 'Mulai investigasi', 'completed_date' => 'Tanggal selesai',
        'location' => 'Lokasi', 'description' => 'Deskripsi', 'summary' => 'Ringkasan',
        'suspected_motive' => 'Dugaan motif', 'status' => 'Status', 'archive_deadline' => 'Tenggat arsip',
        'assigned_to' => 'Petugas', 'archive_status' => 'Status arsip', 'archived_at' => 'Tanggal arsip', 'archive_reason' => 'Alasan arsip',
        'name' => 'Nama', 'notes' => 'Catatan', 'recorded_date' => 'Tanggal dicatat',
        'storage_location' => 'Lokasi penyimpanan', 'photo_path' => 'Foto', 'type' => 'Jenis',
        'legal_status' => 'Status hukum', 'relation_description' => 'Keterangan hubungan',
        'event_time' => 'Waktu peristiwa', 'evidence_id' => 'Bukti terkait',
        'email' => 'Email', 'username' => 'Username', 'password' => 'Kata sandi', 'role' => 'Peran',
        'is_active' => 'Status aktif', 'sha256' => 'SHA-256 (asli → hasil hitung)', 'is_match' => 'Hasil', 'original_name' => 'Nama berkas',
    ];

    // Label jenis data yang terpengaruh
    public const SUBJECTS = [
        CaseRecord::class => 'Kasus', Evidence::class => 'Barang bukti', Suspect::class => 'Pihak terkait',
        TimelineEvent::class => 'Kronologi', User::class => 'Akun',
    ];

    protected $fillable = [
        'user_id', 'action', 'auditable_type',
        'auditable_id', 'summary', 'changes',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'changes' => 'array',
    ];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('Audit log tidak boleh diubah.'));
        static::deleting(fn () => throw new LogicException('Audit log tidak boleh dihapus.'));
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return self::ACTIONS[$this->action] ?? $this->action;
    }

    public function subjectLabel(): ?string
    {
        return $this->auditable_type ? (self::SUBJECTS[$this->auditable_type] ?? class_basename($this->auditable_type)) : null;
    }
}
