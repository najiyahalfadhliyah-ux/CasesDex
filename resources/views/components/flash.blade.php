{{-- Pesan sukses/gagal setelah aksi, tampil sebagai toast di pojok kanan bawah.
     Isi dari redirect()->with('success'|'error', '...'). Tetap tampil sampai ditutup pengguna. --}}
@if (session('success') || session('error'))
    <div class="toast-stack">
        @if (session('success'))
            <div class="alert toast-app toast-success fade show" role="status">
                <i class="bi bi-check2-circle" aria-hidden="true"></i>
                <span class="toast-text">{{ session('success') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup pesan"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert toast-app toast-danger fade show" role="alert">
                <i class="bi bi-exclamation-triangle" aria-hidden="true"></i>
                <span class="toast-text">{{ session('error') }}</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup pesan"></button>
            </div>
        @endif
    </div>
@endif
