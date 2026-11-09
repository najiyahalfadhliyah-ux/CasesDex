@extends('layouts.app')

@section('title', 'Pengaturan Akun - CasesDex')
@section('heading', 'Pengaturan Akun')

@section('page')
    <x-flash />

    <section class="card form-card">
        <div class="card-header"><h1 class="card-title">Pengaturan akun</h1></div>
        <div class="card-body p-3 p-md-4">
            @if ($errors->any())
                <div class="alert alert-danger" role="alert" tabindex="-1" id="error-summary">
                    <strong>Periksa kembali isian berikut:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($errors->keys() as $field)
                            <li><a href="#{{ $field }}" class="alert-link">{{ $errors->first($field) }}</a></li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('account.update') }}" method="POST" novalidate>
                @csrf
                @method('PUT')

                <div class="row g-0 gx-md-4">
                    <div class="col-md-6">
                        <x-field name="name" label="Nama lengkap" :value="$user->name" required maxlength="255" autocomplete="name" />
                    </div>
                    <div class="col-md-6">
                        <div class="mb-3">
                            <label for="role" class="form-label">Peran</label>
                            <input id="role" type="text" class="form-control text-capitalize" value="{{ $user->role }}" disabled aria-describedby="role-desc">
                            <div id="role-desc" class="form-text">Peran hanya dapat diubah oleh admin.</div>
                        </div>
                    </div>
                </div>

                <hr class="my-3">
                <h2 class="h6 fw-semibold mb-3">Ganti kata sandi</h2>
                <div class="row g-0 gx-md-4">
                    <div class="col-md-6">
                        <x-field name="current_password" label="Kata sandi lama" type="password" autocomplete="current-password"
                                 help="Wajib diisi jika ingin mengganti kata sandi." />
                    </div>
                </div>
                <div class="row g-0 gx-md-4">
                    <div class="col-md-6">
                        <x-field name="password" label="Kata sandi baru" type="password" autocomplete="new-password" minlength="8"
                                 help="Kosongkan jika tidak ingin mengganti. Minimal 8 karakter, ada huruf besar, huruf kecil, dan angka." />
                    </div>
                    <div class="col-md-6">
                        <x-field name="password_confirmation" label="Ulangi kata sandi baru" type="password" autocomplete="new-password" />
                    </div>
                </div>

                <button type="submit" class="btn btn-primary mt-2">
                    <i class="bi bi-check2" aria-hidden="true"></i> Simpan perubahan
                </button>
            </form>
        </div>
    </section>
@endsection

@push('scripts')
<script>
    document.getElementById('error-summary')?.focus();
</script>
@endpush
