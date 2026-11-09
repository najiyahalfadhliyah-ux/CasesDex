<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    // Akses sudah dibatasi middleware role:admin di route
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'username' => ['required', 'alpha_dash', 'max:50', 'unique:users,username'],
            'role' => ['required', 'in:admin,petugas'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ];
    }

    // Folder lang belum ada, jadi pesan Bahasa Indonesia ditulis di sini
    public function messages(): array
    {
        return [
            'required' => ':attribute wajib diisi.',
            'email' => ':attribute harus berupa alamat email yang valid.',
            'max' => ':attribute maksimal :max karakter.',
            'unique' => ':attribute sudah dipakai akun lain.',
            'alpha_dash' => ':attribute hanya boleh berisi huruf, angka, tanda hubung, dan garis bawah.',
            'in' => ':attribute tidak valid.',
            'confirmed' => 'Konfirmasi :attribute tidak cocok.',
            'password.min' => ':attribute minimal :min karakter.',
            'password.mixed' => ':attribute harus berisi huruf besar dan huruf kecil.',
            'password.numbers' => ':attribute harus berisi minimal satu angka.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama',
            'email' => 'Email',
            'username' => 'Username',
            'role' => 'Peran',
            'password' => 'Kata sandi',
        ];
    }
}
