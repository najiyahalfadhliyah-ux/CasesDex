# Panduan backup dan restore

Data aplikasi ada di dua tempat, dan keduanya harus dicadangkan **pada waktu yang sama** supaya cocok satu sama lain:

1. **Database MySQL** `arsip_kasus`: kasus, bukti (metadata + hash SHA-256), pihak terkait, kronologi, pengguna, audit log.
2. **Folder berkas bukti** `storage/app/private/evidence/`: berkas digital dan foto bukti fisik (nama acak). Tanpa folder ini, unduhan dan cek integritas gagal ("berkas tidak ditemukan").

Simpan juga salinan `.env` di tempat terpisah yang aman (bukan di folder backup biasa, bukan di Git). `APP_KEY` di dalamnya dipakai untuk tanda tangan link unduh dan sesi.

## Backup

Jalankan dari folder proyek (PowerShell). Ganti `root` dengan user MySQL Anda; sandi akan ditanyakan.

```powershell
$tgl = Get-Date -Format "yyyyMMdd-HHmm"
New-Item -ItemType Directory -Force "D:\backup-arsip\$tgl" | Out-Null

# 1. Database (--single-transaction: konsisten tanpa mengunci tabel)
mysqldump -u root -p --single-transaction --routines --triggers arsip_kasus > "D:\backup-arsip\$tgl\arsip_kasus.sql"

# 2. Berkas bukti
Compress-Archive -Path "storage\app\private\evidence" -DestinationPath "D:\backup-arsip\$tgl\evidence.zip"
```

Saran:
- Lakukan saat tidak ada yang sedang mengunggah bukti.
- Simpan backup di luar laptop/server (drive eksternal atau cloud pribadi), karena isinya data sensitif.
- Uji restore secara berkala; backup yang belum pernah dipulihkan belum terbukti.

## Restore

```powershell
$dir = "D:\backup-arsip\20261116-1200"   # folder backup yang dipilih

# 1. Database: buat ulang lalu isi dari file SQL
mysql -u root -p -e "DROP DATABASE IF EXISTS arsip_kasus; CREATE DATABASE arsip_kasus CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
Get-Content "$dir\arsip_kasus.sql" -Raw | mysql -u root -p arsip_kasus

# 2. Berkas bukti (ganti folder lama)
Remove-Item -Recurse -Force "storage\app\private\evidence" -ErrorAction SilentlyContinue
Expand-Archive "$dir\evidence.zip" -DestinationPath "storage\app\private"

# 3. Bersihkan cache Laravel
php artisan optimize:clear
```

Restore menimpa data yang ada. Cadangkan kondisi sekarang dulu bila masih diperlukan.

## Cek setelah restore

1. Login sebagai admin, buka beberapa kasus dan pastikan barang buktinya tampil.
2. Buka satu bukti digital, klik **Periksa integritas**. Hasil harus **cocok**; ini membuktikan berkas dan hash di database berasal dari backup yang sama.
3. Buka **Riwayat Aktivitas** dan pastikan catatan terakhir sesuai waktu backup.
