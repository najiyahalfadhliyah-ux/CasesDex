# Sistem Informasi Arsip Kasus dan Dokumentasi Barang Bukti (Laravel 13)

Aplikasi web untuk mengarsipkan kasus, barang bukti fisik/digital, pihak terkait, kronologi, dengan cek integritas SHA-256 dan audit log. Proyek kuliah (RPL), memakai data fiktif saja.

## Stack
- Laravel 13, PHP, MySQL (database `arsip_kasus`), Blade, Chart.js untuk grafik.
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
- [x] Tahap 2a: login/logout (`AuthController`, view `auth.login`, `dashboard`)
- [x] Tahap 2b: middleware `EnsureRole` + alias `role` di `bootstrap/app.php`
- [x] Tahap 2c: **landing page publik** di `/` (lihat di bawah)
- [ ] Tahap 2d: layout navbar + sidebar, menu sesuai peran, dashboard kosong
- [ ] Tahap 3: manajemen pengguna (admin)
- [ ] Tahap 4: CRUD kasus (Form Request, nomor otomatis `AK-2026-0001`, Policy, soft delete)
- [ ] Tahap 5: barang bukti + SHA-256 + hash_checks
- [ ] Tahap 6: pihak terkait, kronologi, ringkasan, dugaan motif
- [ ] Tahap 7: audit log
- [ ] Tahap 8: pencarian dan filter
- [ ] Tahap 9: dashboard statistik (Chart.js) dan laporan cetak
- [ ] Tahap 10: pengujian dan pengecekan keamanan

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
