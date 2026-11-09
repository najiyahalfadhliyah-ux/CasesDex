<?php

namespace App\Http\Controllers;

use App\Http\Requests\UpdateAccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

// Pengaturan akun milik pengguna yang sedang login (admin dan petugas)
class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('account.edit', ['user' => $request->user()]);
    }

    public function update(UpdateAccountRequest $request): RedirectResponse
    {
        $data = ['name' => $request->validated('name')];

        if (filled($request->validated('password'))) {
            $data['password'] = $request->validated('password'); // di-hash otomatis oleh cast
        }

        $request->user()->update($data);

        return redirect()->route('account.edit')->with('success', 'Pengaturan akun berhasil disimpan.');
    }
}
