@extends('layouts.app')

@section('title', 'Manajemen Pengguna - Arsip Kasus')
@section('heading', 'Manajemen Pengguna')

@section('page')
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1">Pengguna</h1>
            <p class="text-muted mb-0">Kelola akun admin dan petugas. Akun tidak dihapus, hanya dinonaktifkan.</p>
        </div>
        <a href="{{ route('users.create') }}" class="btn btn-primary">
            <i class="bi bi-person-plus" aria-hidden="true"></i> Tambah pengguna
        </a>
    </div>

    <x-flash />

    <section class="card">
        <div class="table-responsive">
            <table class="table table-app">
                <thead>
                    <tr>
                        <th scope="col">Nama</th>
                        <th scope="col">Username dan email</th>
                        <th scope="col">Peran</th>
                        <th scope="col">Status</th>
                        <th scope="col" class="d-none d-md-table-cell">Kasus ditugaskan</th>
                        <th scope="col" class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        @php($isSelf = $user->is(auth()->user()))
                        <tr>
                            <td class="fw-semibold">
                                {{ $user->name }}
                                @if ($isSelf) <span class="text-muted fw-normal">(Anda)</span> @endif
                            </td>
                            <td>
                                <div>{{ $user->username }}</div>
                                <small class="text-muted">{{ $user->email }}</small>
                            </td>
                            <td>
                                <span class="badge {{ $user->role === 'admin' ? 'bg-brand' : 'badge-role-petugas' }} text-capitalize">{{ $user->role }}</span>
                            </td>
                            <td>
                                <span class="badge-status {{ $user->is_active ? 'badge-aktif' : 'badge-nonaktif' }}">
                                    {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="d-none d-md-table-cell">{{ $user->assigned_cases_count }}</td>
                            <td class="text-end text-nowrap">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-outline-secondary btn-sm">
                                    <i class="bi bi-pencil" aria-hidden="true"></i> Ubah
                                </a>
                                @unless ($isSelf)
                                    <form action="{{ route('users.toggle-active', $user) }}" method="POST" class="d-inline"
                                          data-confirm="{{ $user->is_active ? "Nonaktifkan akun {$user->name}? Pengguna ini tidak akan bisa masuk sampai diaktifkan kembali." : "Aktifkan kembali akun {$user->name}?" }}">
                                        @csrf
                                        @method('PATCH')
                                        @if ($user->is_active)
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-slash-circle" aria-hidden="true"></i> Nonaktifkan
                                            </button>
                                        @else
                                            <button type="submit" class="btn btn-outline-success btn-sm">
                                                <i class="bi bi-check-circle" aria-hidden="true"></i> Aktifkan
                                            </button>
                                        @endif
                                    </form>
                                @endunless
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="empty-state">
                                <i class="bi bi-people" aria-hidden="true"></i>
                                Belum ada pengguna. Tambahkan akun pertama dengan tombol di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <div class="mt-3">{{ $users->links() }}</div>
@endsection

@push('scripts')
<script>
    // Konfirmasi sebelum aktifkan/nonaktifkan. Teks diambil dari data-confirm (sudah di-escape Blade).
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (event) {
            if (!confirm(form.dataset.confirm)) event.preventDefault();
        });
    });
</script>
@endpush
