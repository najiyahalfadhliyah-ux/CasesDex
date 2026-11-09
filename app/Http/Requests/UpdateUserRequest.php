<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

// Mewarisi authorize, messages, dan attributes dari StoreUserRequest
class UpdateUserRequest extends StoreUserRequest
{
    public function rules(): array
    {
        $user = $this->route('user');
        $isSelf = $user->is($this->user());

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'username' => ['required', 'alpha_dash', 'max:50', Rule::unique('users', 'username')->ignore($user)],
            // Admin tidak boleh menurunkan perannya sendiri
            'role' => ['required', $isSelf ? 'in:admin' : 'in:admin,petugas'],
            // Kosongkan jika kata sandi tidak diganti
            'password' => ['nullable', 'confirmed', Password::defaults()],
        ];
    }

    public function messages(): array
    {
        $messages = parent::messages();

        if ($this->route('user')->is($this->user())) {
            $messages['role.in'] = 'Anda tidak dapat menurunkan peran akun sendiri.';
        }

        return $messages;
    }
}
