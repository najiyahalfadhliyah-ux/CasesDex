<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'admin']);
    }

    public function test_petugas_mendapat_403(): void
    {
        $petugas = User::factory()->create();

        $this->actingAs($petugas)->get('/users')->assertForbidden();
        $this->actingAs($petugas)->post('/users', [])->assertForbidden();
    }

    public function test_admin_bisa_melihat_daftar_dan_menambah_pengguna_dengan_password_ter_hash(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get('/users')->assertOk()->assertSee($admin->name);
        $this->actingAs($admin)->get('/users/create')->assertOk();
        $this->actingAs($admin)->get("/users/{$admin->id}/edit")->assertOk()->assertSee('tidak dapat mengubah peran akun sendiri');

        $this->actingAs($admin)->post('/users', [
            'name' => 'Petugas Baru',
            'email' => 'baru@arsip.test',
            'username' => 'petugas_baru',
            'role' => 'petugas',
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ])->assertRedirect('/users')->assertSessionHas('success');

        $user = User::where('username', 'petugas_baru')->first();
        $this->assertTrue($user->is_active);
        $this->assertNotSame('rahasia123', $user->password);
        $this->assertTrue(Hash::check('rahasia123', $user->password));
    }

    public function test_validasi_gagal_mempertahankan_input_lama(): void
    {
        $this->actingAs($this->admin())->from('/users/create')->post('/users', [
            'name' => 'Tanpa Email',
            'username' => 'x',
            'role' => 'petugas',
            'password' => 'pendek',
            'password_confirmation' => 'beda',
        ])->assertRedirect('/users/create')
          ->assertSessionHasErrors(['email', 'password'])
          ->assertSessionHasInput('name', 'Tanpa Email');
    }

    public function test_ubah_tanpa_password_tidak_mengganti_password_lama(): void
    {
        $target = User::factory()->create();
        $hashLama = $target->password;

        $this->actingAs($this->admin())->put("/users/{$target->id}", [
            'name' => 'Nama Baru',
            'email' => $target->email,
            'username' => $target->username,
            'role' => 'admin',
            'password' => '',
            'password_confirmation' => '',
        ])->assertRedirect('/users');

        $target->refresh();
        $this->assertSame('Nama Baru', $target->name);
        $this->assertSame('admin', $target->role);
        $this->assertSame($hashLama, $target->password);
    }

    public function test_admin_tidak_bisa_menurunkan_peran_sendiri(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->put("/users/{$admin->id}", [
            'name' => $admin->name,
            'email' => $admin->email,
            'username' => $admin->username,
            'role' => 'petugas',
        ])->assertSessionHasErrors('role');

        $this->assertSame('admin', $admin->fresh()->role);
    }

    public function test_admin_tidak_bisa_menonaktifkan_diri_sendiri(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)->patch("/users/{$admin->id}/toggle-active")->assertSessionHas('error');

        $this->assertTrue($admin->fresh()->is_active);
    }

    public function test_admin_bisa_menonaktifkan_dan_mengaktifkan_pengguna_lain(): void
    {
        $admin = $this->admin();
        $target = User::factory()->create();

        $this->actingAs($admin)->patch("/users/{$target->id}/toggle-active")->assertSessionHas('success');
        $this->assertFalse($target->fresh()->is_active);
        $this->assertDatabaseHas('users', ['id' => $target->id]); // tidak terhapus

        $this->actingAs($admin)->patch("/users/{$target->id}/toggle-active");
        $this->assertTrue($target->fresh()->is_active);
    }
}
