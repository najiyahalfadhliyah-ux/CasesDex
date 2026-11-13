# Sistem Informasi CasesDex dan Dokumentasi Barang Bukti (Laravel 13)

Aplikasi web untuk mengarsipkan kasus, barang bukti fisik/digital, pihak terkait, kronologi, dengan cek integritas SHA-256 dan audit log. Proyek kuliah (RPL), memakai data fiktif saja.

## Stack
- Laravel 13, PHP, MySQL (database `arsip_kasus`), Blade, Chart.js untuk grafik.
- Bootstrap 5.3 + Bootstrap Icons 1.11 (CDN), font IBM Plex Sans/Mono. Tanpa Tailwind, tanpa build step.
- Autentikasi ditulis sendiri (tanpa Breeze/Jetstream).
- Bahasa UI dan pesan: Bahasa Indonesia.

## Cara kerja dengan saya
- Jelaskan singkat apa yang dibuat dan kenapa. Pengguna masih belajar Laravel.
- Kerjakan satu tahap/konsep per kali, jangan loncat tahap.
- Tulis kode seminimal mungkin. Jangan tambah library atau lapisan yang tidak diminta.
- Jangan commit atau push kecuali diminta. Jangan sentuh `.env`.

## Konvensi penting (Laravel 13)
- Model tabel `cases` bernama **`CaseRecord`** (`Case` kata terlarang PHP).
- Tabel `evidence` (bukan `evidences`), jadi `Evidence` wajib `$table = 'evidence'`.
- `User` memakai atribut `#[Fillable([...])]` dan method `casts()`, bukan properti `$fillable`.
- Password otomatis di-hash lewat cast `'password' => 'hashed'`.
- Controller tipis. Validasi lewat Form Request. Logika rumit ke Service class (`app/Services`).
- Hak akses data lewat Policy. Pembatasan halaman lewat middleware `role:admin`.
- Gunakan eager loading (`with()`) untuk daftar. Hindari query di Blade.
- Output Blade pakai `{{ }}`. Semua form POST wajib `@csrf`.

## Struktur database
`users` (name, email, username, password, role admin|petugas, is_active)
`cases` (case_number unik AK-2026-0001, title, category, incident_date, recorded_date, investigation_start_date, completed_date, location, description, summary, suspected_motive, status dalam_penanganan|selesai|ditutup|ditangguhkan, archive_deadline, assigned_to, created_by, soft delete)
`evidence` (case_id, type fisik|digital, name, description, photo_path, storage_location, file_path, original_name, format, size, sha256_hash, uploaded_by)
`hash_checks`, `suspects`, `timeline_events`, `audit_logs` (audit log tidak boleh diubah/dihapus dari aplikasi).
Relasi: satu kasus punya banyak evidence/suspects/timeline_events. Satu evidence punya banyak hash_checks. Satu petugas per kasus (`assigned_to`).

## Hak akses
- Admin: semua kasus, tambah/nonaktifkan kasus, manajemen pengguna, penugasan, audit log.
- Petugas: hanya kasus yang ditugaskan. Boleh ubah kasus, bukti, pihak terkait, kronologi, cek hash. Tidak boleh tambah/nonaktifkan kasus, lihat audit log, atau kelola pengguna.
- Akun `is_active = false` tidak bisa login.
- Hasil pencarian, dashboard, dan statistik mengikuti hak akses.

## Aturan keamanan
- Berkas bukti disimpan di `storage` non-publik dengan nama acak. Validasi tipe (PDF, JPG, PNG, LOG/TXT, PCAP) dan ukuran. Unduh lewat controller yang cek otorisasi.
- SHA-256 dihitung saat unggah. Cek integritas hitung ulang lalu simpan ke `hash_checks`.
- Setiap aksi penting dicatat ke audit log (Observer atau satu service).
- Hapus kasus = soft delete oleh admin saja.
- Data sensitif (pihak terkait, motif, bukti) jangan tampil ke yang tidak berwenang.
- Seeder: admin `admin`/`password123`, petugas `petugas1`/`password123` (hanya lokal).

## Status pengerjaan
- [x] Tahap 0: setup, MySQL, GitHub
- [x] Tahap 1: migration 7 tabel, model + relasi, seeder (sudah di `main`)
- [x] Tahap 2a: login/logout (`AuthController`, view `auth.login`, `dashboard`), batas percobaan login 5x per login+IP lalu kunci 5 menit (`RateLimiter`, tes `LoginThrottleTest`)
- [x] Tahap 2b: middleware `EnsureRole` + alias `role` di `bootstrap/app.php`
- [x] Tahap 2c: **landing page publik** di `/` (lihat di bawah)
- [x] Tahap 2d: layout navbar + sidebar, menu sesuai peran, dashboard kosong (`layouts.guest` + `layouts.app`, `errors/403`), desain ulang "Berkas Resmi" (lihat Sistem desain)
- [x] Tahap 3: manajemen pengguna (`UserController` resource tanpa show/destroy + `users.toggle-active`, `StoreUserRequest`/`UpdateUserRequest`, tes `UserManagementTest`)
- [x] Tahap 4: CRUD kasus (`CaseController` resource, `CaseRecordPolicy` dengan `before()` admin + `assign`, `Store/UpdateCaseRequest`, `App\Services\CaseNumberGenerator` di dalam transaksi + retry, scope `CaseRecord::visibleTo($user)` untuk semua daftar/statistik, konstanta `CaseRecord::STATUSES`/`CATEGORIES`, `archive_deadline` otomatis = mulai investigasi + 6 bulan, tes `CaseManagementTest`)
- [x] Tahap 5: barang bukti + SHA-256 + hash_checks (`EvidenceController` shallow di bawah `cases`, `EvidencePolicy` mengikuti kasus + nonaktif khusus admin, `App\Services\EvidenceIntegrity`, `config/evidence.php` + `EVIDENCE_MAX_KB`, kolom baru `recorded_date`/`notes`/`is_active`, komponen `<x-hash-result>`, tes `EvidenceTest`)
- [ ] Tahap 6: pihak terkait, kronologi, ringkasan, dugaan motif
- [ ] Tahap 7: audit log
- [ ] Tahap 8: pencarian dan filter
- [ ] Tahap 9: dashboard statistik (Chart.js) dan laporan cetak
- [ ] Tahap 10: pengujian dan pengecekan keamanan
- [ ] Tahap 11: desain ulang UI/UX (ui-ux-pro-max + frontend-design)

## Aturan UI selama tahap fungsi (Tahap 3-9)
- Ikuti sistem desain "Berkas Resmi" yang sudah ada. Jangan
  mendesain ulang atau mengubah gaya.
- Pakai komponen dan class yang sudah ada; kalau butuh komponen
  baru, buat sebagai Blade component dan tambahkan gayanya di
  public/css/app.css dengan CSS variable.
- Tampilan fungsional dan konsisten saja. Polesan UI/UX dikerjakan
  di tahap akhir (Tahap 11).

## Sistem desain "Berkas Resmi" (wajib diikuti Tahap 3 dst.)
Gaya flat, tenang, rata kiri, nuansa map arsip instansi. Semua warna dan gaya hanya di `public/css/app.css` lewat CSS variable. Di Blade jangan menulis hex; pakai kelas atau `var(--...)`.

- **Warna**: `--primary #1E3A8A` (navy: sidebar, judul, tombol utama `.btn-primary`), `--primary-dark #172554` (hero, hover), `--accent #B45309` (emas tua: CTA `.btn-accent`, fokus), `--bg #F8FAFC`, `--surface #FFF`, `--text #0F172A`, `--muted #475569`, `--border #E2E8F0`, `--danger #DC2626`. Variabel `--bs-*` sudah dipetakan ke token ini.
- **Font**: IBM Plex Sans untuk semua teks. IBM Plex Mono (`.font-mono` / `.case-number`) **hanya** untuk nomor kasus, hash SHA-256, dan kode error, bukan untuk label biasa. Angka statistik memakai Plex Sans `tabular-nums`.
- **Ikon**: Bootstrap Icons `<i class="bi bi-..." aria-hidden="true"></i>`, selalu berdampingan dengan teks. Tombol yang hanya berisi ikon wajib punya `aria-label`. Jangan pakai emoji.
- **Teks UI**: sentence case ("Kasus terbaru", bukan "KASUS TERBARU"). Tanpa label ALL-CAPS di atas judul.
- **Kartu**: `.card` (border 1px, radius 10px, tanpa bayangan). Judul kartu: `.card-header` + `<h2 class="card-title">`. Statistik: `.card.stat-card` berisi `.stat-icon`, `.stat-value`, `.stat-label`.
- **Tabel**: bungkus dengan `.card` lalu `.table-responsive` > `table.table.table-app`, `<th scope="col">`. Data kosong: satu baris `<td colspan class="empty-state">` dengan ikon dan kalimat ajakan.
- **Form**: label selalu terlihat (`.form-label`), jangan hanya placeholder. Error di bawah field (`.is-invalid` + `.invalid-feedback`, ditambah `aria-describedby`). Input dan tombol tinggi minimal 44px (sudah diatur CSS). Ikon depan pakai `.input-group-text`.
- **Badge status kasus**: `<span class="badge-status badge-{{ $case->status }}">`. Kelasnya `badge-dalam_penanganan`, `badge-selesai`, `badge-ditutup`, `badge-ditangguhkan`. Latarnya lembut dengan titik di depan teks. Badge peran: `.badge.bg-brand`.
- **Blade component** (`resources/views/components`): `<x-flash />` menampilkan `session('success'|'error')`. `<x-field name label type :value help />` adalah input + label + error + `old()`; atribut lain (required, maxlength, autocomplete) diteruskan ke input. Nilai tanggal: `->format('Y-m-d')`. `<x-select name label :options value placeholder help />` (options = nilai => label). `<x-textarea name label value help rows />`. `<x-status-badge :status="$case->status" />`. Detail label-nilai: `<dl class="detail-list">`; teks panjang: `.text-pre-line`. Form panjang memakai ringkasan error di atas (`#error-summary`, lihat `users/_form`). Kartu form: `.card.form-card`.
- **Konfirmasi aksi**: `<form data-confirm="...">` + script kecil `confirm()` lewat `@push('scripts')` (lihat `users/index`). Jangan menaruh teks dinamis di `onsubmit`.
- **Badge akun**: `.badge-aktif`, `.badge-nonaktif`, peran `.bg-brand` (admin) / `.badge-role-petugas`.
- **Keadaan kosong**: `.empty-state` dengan ikon `bi` dan kalimat yang menjelaskan langkah berikutnya.
- **Layout**: halaman setelah login memakai `@extends('layouts.app')`, `@section('title')`, `@section('heading')` (judul navbar), dan `@section('page')`. Menu sidebar berupa `.nav-link` dengan ikon, grup `.nav-section` ("Utama", "Administrasi" khusus admin), menu aktif lewat `request()->routeIs()` + `aria-current="page"`.
- **Chart.js**: muat lewat `@push('scripts')` di halaman yang memakainya (layout punya `@stack('scripts')`). Dashboard sudah punya `<canvas id="chartKasus" hidden>`; hapus `hidden` dan empty-state saat data ada. Warna grafik diambil dari variabel status.
- **Aksesibilitas**: skip link "Lewati ke konten" (`#konten`), outline fokus emas jangan dihapus, `prefers-reduced-motion` dihormati, kontras teks minimal 4,5:1.
- **Responsif**: sidebar `offcanvas-lg` (di bawah 992px jadi menu geser). Grid Bootstrap `col-6 col-xl-3` untuk statistik, `col-lg-8/4` untuk panel.

## Landing page (belum ada di PRD)
Halaman publik di `/` untuk tamu yang belum login: nama sistem, penjelasan singkat fitur, tombol "Masuk" ke `/login`. Pengguna yang sudah login langsung diarahkan ke `/dashboard`. Tidak boleh menampilkan data kasus apa pun. Route `/` saat ini masih redirect ke dashboard di dalam grup `auth`, perlu dipindah ke luar grup.

## Perintah
- `php artisan serve`, `php artisan migrate:fresh --seed`, `php artisan route:list`, `php artisan test`
- Branch per tahap besar (`feature/auth`, dst.), merge ke `main` setelah tes manual.

<laravel-boost-guidelines>
# Laravel Application

This repository contains a Laravel application. Complete the following setup before working on the user's request.

## Prerequisites

Verify that PHP and Composer are available:

```sh
php -v
composer -V
```

If either command is unavailable, detect the user's operating system and install the prerequisites with the appropriate command:

macOS:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac/8.5)"
```

Windows PowerShell:

```powershell
Set-ExecutionPolicy Bypass -Scope Process -Force; [System.Net.ServicePointManager]::SecurityProtocol = [System.Net.ServicePointManager]::SecurityProtocol -bor 3072; iex ((New-Object System.Net.WebClient).DownloadString('https://php.new/install/windows/8.5'))
```

Linux:

```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux/8.5)"
```

After installation, ask the user to restart their terminal. If the agent needs the restarted shell to continue, ask the user to reopen their terminal and rerun their original prompt.

## Agent Setup

Install Laravel Boost from the application root before making application changes:

```sh
composer require laravel/boost --dev
php artisan boost:install
```

Boost replaces these bootstrap instructions with guidelines tailored to the application. After installation, read `AGENTS.md` again and continue with the user's original request using the generated guidelines.
</laravel-boost-guidelines>
