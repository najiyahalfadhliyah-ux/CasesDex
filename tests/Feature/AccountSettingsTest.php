<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dan_petugas_bisa_membuka_pengaturan_akun(): void
    {
        foreach (['admin', 'petugas'] as $role) {
            $user = User::factory()->create(['role' => $role]);
            $this->actingAs($user)->get('/account')->assertOk()->assertSee('Pengaturan Akun');
        }
        $this->post('/logout');
        $this->get('/account')->assertRedirect('/login');
    }

    public function test_ubah_nama_tanpa_kata_sandi_dan_peran_tidak_bisa_diubah(): void
    {
        $user = User::factory()->create(['role' => 'petugas']);
        $hashLama = $user->password;

        $this->actingAs($user)->put('/account', ['name' => 'Nama Baru', 'role' => 'admin', 'is_active' => 0])
            ->assertRedirect('/account')->assertSessionHasNoErrors();

        $user->refresh();
        $this->assertSame('Nama Baru', $user->name);
        $this->assertSame('petugas', $user->role);
        $this->assertTrue($user->is_active);
        $this->assertSame($hashLama, $user->password);
    }

    public function test_ganti_kata_sandi_wajib_kata_sandi_lama_yang_benar_dan_aturan_kuat(): void
    {
        $user = User::factory()->create(); // kata sandi factory: "password"
        $base = ['name' => $user->name, 'password' => 'BaruSekali1', 'password_confirmation' => 'BaruSekali1'];

        $this->actingAs($user)->put('/account', $base)->assertSessionHasErrors('current_password');
        $this->actingAs($user)->put('/account', $base + ['current_password' => 'salah'])->assertSessionHasErrors('current_password');

        // kurang dari 8, tanpa huruf besar, tanpa huruf kecil, tanpa angka
        foreach (['Pend1', 'semuakecil1', 'SEMUABESAR1', 'TanpaAngka'] as $lemah) {
            $this->actingAs($user)->put('/account', ['name' => $user->name, 'current_password' => 'password',
                'password' => $lemah, 'password_confirmation' => $lemah])->assertSessionHasErrors('password');
        }
        $this->assertTrue(Hash::check('password', $user->fresh()->password));

        $this->actingAs($user)->put('/account', $base + ['current_password' => 'password'])->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('BaruSekali1', $user->fresh()->password));
    }
}
