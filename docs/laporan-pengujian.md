# Laporan pengujian dan pengecekan keamanan (Tahap 10)

Tanggal audit: 16/11/2026. Cakupan: seluruh route, controller, Form Request, Policy, model, view Blade, konfigurasi, seeder, dan PRD.

Metode:
- Baca kode (statis) untuk tiap butir daftar periksa.
- Probe dinamis lewat tes sementara: petugas A mencoba 38 route dengan data milik petugas B, sesi akun nonaktif, halaman error dengan `APP_DEBUG=false`, serta kinerja dengan 5.000 kasus + 10.000 bukti dan `Model::preventLazyLoading()` untuk mendeteksi N+1.
- `.env` tidak dibaca (sesuai aturan proyek). Nilai yang bergantung pada `.env` ditandai "perlu dicek manual".

## Temuan

| ID | Temuan | Risiko | Lokasi file | Saran perbaikan | Status |
|----|--------|--------|-------------|-----------------|--------|
| T-01 | Akun yang dinonaktifkan tetap bisa memakai sesi yang sedang berjalan bila `SESSION_DRIVER` bukan `database` (file, cookie, redis). `toggleActive` hanya menghapus baris di tabel `sessions`, dan tidak ada pengecekan `is_active` di setiap request. Terbukti di probe: setelah dinonaktifkan, `/dashboard` tetap 200. | Sedang | `app/Http/Controllers/UserController.php` (`toggleActive`), `bootstrap/app.php` | Middleware kecil di grup `auth`: jika `is_active` false, logout + invalidate sesi lalu arahkan ke login dengan pesan. Tambah tes. | **Diperbaiki** |
| T-02 | Halaman error 404, 419, dan 500 masih bawaan Laravel: bahasa Inggris, gaya berbeda dari aplikasi. 419 ("Page Expired") membingungkan saat sesi habis. Tidak membocorkan detail teknis bila `APP_DEBUG=false`. Hanya 403 yang sudah dibuat. | Sedang | `resources/views/errors/` | Tambah `404`, `419`, `500` meniru `errors/403.blade.php`, pesan Bahasa Indonesia dan langkah berikutnya. Tambah tes. | **Diperbaiki** |
| T-03 | `.env.example` tidak cocok untuk proyek ini: `DB_CONNECTION=sqlite` (aplikasi memakai MySQL `arsip_kasus`), tidak ada `EVIDENCE_MAX_KB`, `APP_NAME=Laravel`, dan tanpa catatan bahwa `APP_DEBUG=true`/`APP_ENV=local`/`LOG_LEVEL=debug` hanya untuk lokal. Bila disalin ke server, stack trace bisa bocor. | Sedang | `.env.example` | Isi placeholder MySQL, `EVIDENCE_MAX_KB`, `SESSION_LIFETIME=30`, dan komentar nilai produksi (`APP_ENV=production`, `APP_DEBUG=false`). Tidak menyentuh `.env`. | **Diperbaiki** |
| T-04 | Sesi berakhir otomatis setelah 120 menit tidak aktif (nilai bawaan `.env.example`). Untuk data sensitif terlalu lama. Nilai di `.env` perlu dicek manual. | Rendah | `.env.example`, `config/session.php` | Turunkan ke 30 menit lewat `.env` (ikut perbaikan T-03). | **Diperbaiki** |
| T-05 | Mengganti kata sandi (akun sendiri atau oleh admin) tidak mengakhiri sesi lain milik akun itu. | Rendah | `AccountController@update`, `UserController@update` | Hapus sesi lain setelah ganti sandi (`Auth::logoutOtherDevices` atau hapus baris `sessions`). | Ditunda (risiko rendah) |
| T-06 | Batas percobaan login hanya per kombinasi login + IP. Satu IP bisa mencoba banyak username berbeda (password spraying). | Rendah | `AuthController@login` | Tambah batas kedua per IP (mis. 20 percobaan/menit) dengan `RateLimiter` yang sama. | Ditunda (risiko rendah) |
| T-07 | Kolom login dan sandi di form login tanpa batas panjang. Aturan sandi tanpa `max`, padahal bcrypt diam-diam memotong sandi lebih dari 72 byte. | Rendah | `AuthController@login`, `AppServiceProvider` (`Password::defaults`) | `max:255` pada login, `Password::min(8)->max(72)`. | Ditunda (risiko rendah) |
| T-08 | Header keamanan (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`) tidak dikirim, jadi halaman bisa dimuat di iframe situs lain (clickjacking). | Rendah | `bootstrap/app.php` | Middleware kecil yang menambah tiga header, atau atur di web server saat produksi. | Ditunda (risiko rendah) |
| T-09 | Route uji `/admin-test` masih tersisa (aman karena `role:admin`, tapi tidak dipakai). | Rendah | `routes/web.php` | Hapus route (cek tes yang memakainya). | Ditunda (risiko rendah) |
| T-10 | Karakter `%` dan `_` di kata kunci pencarian berlaku sebagai wildcard LIKE. Hanya memperluas hasil di dalam hak akses pengguna sendiri; query tetap berparameter. | Rendah | `CaseRecord::scopeFilter` | Terima sebagai keterbatasan. | Diterima sebagai keterbatasan |
| T-11 | Di SQLite (lingkungan tes) `evidence.case_id` tidak berindeks, sehingga pencarian nama bukti dengan 5.000 kasus/10.000 bukti butuh 31,7 detik. Di MySQL indeks FK `evidence_case_id_foreign` sudah ada; dengan indeks yang sama hasilnya 171 ms. Kolom `cases.status`, `category`, `incident_date` belum berindeks (data kecil, belum berdampak). | Rendah | migration `cases`, `evidence` | Terima untuk MySQL. Tambah indeks bila data membesar. | Diterima sebagai keterbatasan |
| T-12 | Login gagal mencatat teks yang diketik di kolom login (maks. 100 karakter). Bila pengguna salah mengetik sandi di kolom login, teks itu masuk audit log. Sandi dari kolom sandi tidak pernah dicatat. | Rendah | `AuthController@login` | Terima (praktik umum, berguna untuk deteksi serangan). | Diterima sebagai keterbatasan |
| T-13 | Penyimpangan PRD yang disengaja: menu "Barang Bukti" dan "Pihak Terkait" tidak berdiri sendiri (ada di detail kasus); "pengarsipan oleh sistem" tidak dibuat (keputusan Tahap 8, tanpa job otomatis). | Rendah | `layouts/app.blade.php`, `ArchiveController` | Terima, catat di laporan akhir. | Diterima sebagai keterbatasan |
| T-14 | Kata sandi seeder `password123` tertulis di kode. Hanya untuk lokal; `db:seed` di produksi meminta `--force`. | Rendah | `database/seeders/UserSeeder.php` | Terima; jangan jalankan seeder di server. | Diterima sebagai keterbatasan |

## Butir yang lolos

| Area | Hasil |
|------|-------|
| Route dan login | Semua route internal di grup `auth`; tamu diarahkan ke `/login` (302). Route admin memakai `role:admin`. |
| Akses silang (probe 38 route) | Kasus, ubah status/analisis, nonaktifkan, arsip/buka arsip, bukti (lihat, ubah, unduh signed URL, cek hash, unggah ulang, nonaktifkan), pihak terkait, kronologi: semuanya 403 untuk petugas lain. |
| Daftar dan statistik | `/cases`, `/archives`, `/dashboard`, `/reports` tidak memuat kasus milik petugas lain (`visibleTo` di semua daftar). |
| Khusus admin | Tambah kasus, nonaktifkan kasus, manajemen pengguna, audit log, arsip/buka arsip: 403 untuk petugas. |
| Kata sandi | Di-hash bcrypt lewat cast `hashed` (12 putaran). Aturan: min. 8, huruf besar-kecil, angka. Disembunyikan dari serialisasi (`#[Hidden]`). |
| Login | Batas 5x per login+IP lalu kunci 5 menit; pesan gagal umum; sesi diregenerasi saat login, di-invalidate saat logout; akun nonaktif ditolak (`is_active => true` di `Auth::attempt`). |
| Validasi dan mass assignment | Semua input lewat Form Request; controller hanya menyimpan `validated()`/`safe()`; semua model memakai `$fillable`/`#[Fillable]`. Petugas tidak bisa mengubah `assigned_to`/`archive_deadline` (aturan dibuang). |
| SQL injection | Hanya query builder berparameter. `selectRaw`/`groupByRaw` di `CaseStatistics` memakai ekspresi tetap dari kode, bukan input. |
| XSS | Tidak ada `{!! !!}`. Teks dinamis di `data-confirm` lewat `{{ }}`; data grafik lewat `@json`. |
| CSRF | Semua form POST/PUT/PATCH/DELETE punya `@csrf`; logout lewat POST. |
| Unggah berkas | `extensions` + `mimes` (tipe asli dari isi berkas), batas ukuran `EVIDENCE_MAX_KB`; disimpan di disk `local` (`storage/app/private`) dengan nama acak. |
| Unduh berkas | Signed URL 30 menit + `Gate::authorize('view')`; tanpa signature 403. Path diambil dari database, bukan dari input (tidak ada path traversal); versi unggah ulang dibatasi `scopeBindings`. Diunduh sebagai attachment. |
| SHA-256 | Dihitung dari berkas yang diterima server; cek ulang dibandingkan dengan hash asli memakai `hash_equals`; berkas hilang = tidak cocok; riwayat di `hash_checks`. |
| Audit log | Sandi dan token tidak pernah disimpan (diuji); model menolak update/delete; tidak ada route ubah/hapus (diuji 404). |
| Git | `.env` di `.gitignore` dan tidak dilacak Git; tidak ada kredensial selain akun seeder lokal. |
| N+1 | Semua halaman utama lolos dengan `preventLazyLoading`. Jumlah query tetap: `/cases` 3, `/dashboard` 7, `/reports` 4, detail kasus 5. |
| Kinerja | 5.000 kasus: `/cases` 171 ms, `/dashboard` 202 ms, `/reports` 64 ms (SQLite, mesin lokal). Target PRD 3 detik terpenuhi. |
| Halaman 403 | Bahasa Indonesia, tanpa detail teknis. |
| Data uji | Seeder fiktif: "Tersangka Samaran", "Lokasi simulasi", email `@arsip.test`. |

## Kesesuaian PRD

| Kebutuhan | Status |
|-----------|--------|
| Login/logout, email atau username (FR-001 s.d. FR-006) | Ada |
| Dashboard: kartu, grafik kategori & tahun, kasus terbaru, pengingat arsip | Ada |
| Kasus: daftar aktif, tambah, detail, ubah, soft delete, nomor unik, tanggal mulai investigasi wajib | Ada |
| Barang bukti fisik/digital, validasi tipe & ukuran, SHA-256, cek integritas + riwayat, unduh sesuai hak akses | Ada |
| Pihak terkait dengan status hukum terpisah | Ada |
| Kronologi urut waktu kejadian, ringkasan & dugaan motif di atas timeline | Ada |
| Pencarian & filter sesuai hak akses | Ada |
| Arsip kasus (status, tanggal, alasan) dan buka arsip | Ada; pengarsipan otomatis oleh sistem tidak dibuat (T-13) |
| Statistik & laporan cetak dengan filter periode | Ada |
| Audit log aktivitas penting, admin saja, append-only | Ada |
| Manajemen pengguna, peran, penugasan | Ada |
| Struktur menu PRD 4.2 | Menyimpang: Barang Bukti & Pihak Terkait di dalam detail kasus (T-13) |
| NFR keandalan (pesan error jelas) | Sebagian: 403 ada, 404/419/500 belum (T-02) |
| NFR sesi berakhir otomatis | Ada (120 menit), disarankan 30 (T-04) |
| NFR pencadangan | Panduan di `docs/backup.md` |

## Perbaikan dan verifikasi

| ID | Perbaikan | Tes |
|----|-----------|-----|
| T-01 | Middleware `App\Http\Middleware\EnsureUserIsActive` ditambahkan ke grup `web` (`bootstrap/app.php`). Setiap request memeriksa `is_active`; akun nonaktif di-logout, sesi di-invalidate, lalu diarahkan ke login dengan pesan "Akun Anda telah dinonaktifkan. Hubungi admin." Berlaku untuk semua driver sesi. | `SecurityFixesTest::test_sesi_akun_nonaktif_langsung_berakhir`, `test_akun_aktif_dan_tamu_tidak_terpengaruh` |
| T-02 | `resources/views/errors/layout.blade.php` + `404`, `419`, `500` (gaya sama dengan 403, Bahasa Indonesia). Pesan tetap, tidak mengambil pesan exception, sehingga nama model, query, dan path tidak tampil. | `SecurityFixesTest::test_halaman_error_jelas_dan_tidak_membocorkan_detail` (dengan `APP_DEBUG=false`) |
| T-03, T-04 | `.env.example`: `APP_NAME=CasesDex`, catatan wajib produksi (`APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=error`), koneksi MySQL `arsip_kasus` tanpa sandi, `EVIDENCE_MAX_KB=10240`, `SESSION_LIFETIME=30`. `.env` tidak diubah. | `SecurityFixesTest::test_env_example_sesuai_proyek` |

Hasil `php artisan test` setelah perbaikan: **57 tes lolos, 400 assertion, 0 gagal.**

Tindakan manual untuk pemilik proyek (tidak dilakukan otomatis karena `.env` tidak boleh disentuh):
- Ubah `SESSION_LIFETIME=30` di `.env` bila masih 120.
- Saat dipasang di server: `APP_ENV=production`, `APP_DEBUG=false`, `LOG_LEVEL=error`.

Temuan rendah yang ditunda (T-05 s.d. T-09) bisa dikerjakan kapan saja; masing-masing perubahan kecil dan tidak memengaruhi fitur.
