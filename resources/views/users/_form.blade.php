{{-- Dipakai bersama oleh users.create dan users.edit --}}
@php($isSelf = $user->exists && $user->is(auth()->user()))

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

<div class="row g-0 gx-md-4">
    <div class="col-md-6">
        <x-field name="name" label="Nama lengkap" :value="$user->name" required maxlength="255" autocomplete="name" />
    </div>
    <div class="col-md-6">
        <x-field name="username" label="Username" :value="$user->username" required maxlength="50" autocomplete="off"
                 help="Huruf, angka, tanda hubung, atau garis bawah. Dipakai untuk masuk." />
    </div>
    <div class="col-md-6">
        <x-field name="email" label="Email" type="email" :value="$user->email" required maxlength="255" autocomplete="off" />
    </div>
    <div class="col-md-6">
        <div class="mb-3">
            <label for="role" class="form-label">Peran</label>
            @if ($isSelf)
                {{-- Admin tidak boleh menurunkan perannya sendiri; aturan yang sama juga dicek di UpdateUserRequest --}}
                <input type="hidden" name="role" value="admin">
                <input id="role" type="text" class="form-control" value="Admin" disabled aria-describedby="role-desc">
                <div id="role-desc" class="form-text">Anda tidak dapat mengubah peran akun sendiri.</div>
            @else
                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror" required
                        @error('role') aria-describedby="role-desc" @enderror>
                    <option value="petugas" @selected(old('role', $user->role) === 'petugas')>Petugas</option>
                    <option value="admin" @selected(old('role', $user->role) === 'admin')>Admin</option>
                </select>
                @error('role')
                    <div id="role-desc" class="invalid-feedback">{{ $message }}</div>
                @enderror
            @endif
        </div>
    </div>
</div>

<hr class="my-3">
<h2 class="h6 fw-semibold mb-3">Kata sandi</h2>
<div class="row g-0 gx-md-4">
    <div class="col-md-6">
        <x-field name="password" label="Kata sandi" type="password" autocomplete="new-password" minlength="8"
                 :required="! $user->exists"
                 :help="$user->exists ? 'Kosongkan jika tidak ingin mengganti. Minimal 8 karakter.' : 'Minimal 8 karakter.'" />
    </div>
    <div class="col-md-6">
        <x-field name="password_confirmation" label="Ulangi kata sandi" type="password" autocomplete="new-password"
                 :required="! $user->exists" />
    </div>
</div>

@push('scripts')
<script>
    // Arahkan fokus ke ringkasan error setelah validasi gagal (untuk pengguna keyboard/pembaca layar)
    document.getElementById('error-summary')?.focus();
</script>
@endpush
