<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\Password;

// Pengaturan akun sendiri. Hanya nama dan kata sandi; peran, username, dan status tidak ada di aturan
// sehingga tidak ikut tersimpan walau dikirim. Pesan dan nama field diwarisi dari StoreUserRequest.
class UpdateAccountRequest extends StoreUserRequest
{
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            // current_password: cocokkan dengan kata sandi akun yang sedang login
            'current_password' => ['required_with:password', 'nullable', 'current_password'],
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        return parent::messages() + [
            'current_password.required_with' => 'Kata sandi lama wajib diisi untuk mengganti kata sandi.',
            'current_password.current_password' => 'Kata sandi lama tidak sesuai.',
        ];
    }

    public function attributes(): array
    {
        return parent::attributes() + ['current_password' => 'Kata sandi lama', 'password' => 'Kata sandi baru'];
    }
}
