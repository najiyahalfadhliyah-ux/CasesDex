<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginThrottleTest extends TestCase
{
    use RefreshDatabase;

    private function login(string $login, string $password)
    {
        return $this->from('/login')->post('/login', compact('login', 'password'));
    }

    public function test_terkunci_setelah_5_kali_salah_walau_sandi_benar(): void
    {
        User::factory()->create(['username' => 'petugas1', 'password' => 'password123']);

        foreach (range(1, 5) as $i) {
            $this->login('petugas1', 'salah')->assertSessionHasErrors(['login' => 'Email/username atau kata sandi salah.']);
        }

        // Percobaan ke-6 ditolak meski sandinya benar
        $this->login('petugas1', 'password123')->assertSessionHasErrors(['login' => 'Terlalu banyak percobaan, coba lagi dalam 5 menit.']);
        $this->assertGuest();

        // Setelah 5 menit lewat, bisa masuk lagi
        $this->travel(301)->seconds();
        $this->login('petugas1', 'password123')->assertRedirect('/dashboard');
    }

    public function test_login_berhasil_mereset_hitungan(): void
    {
        User::factory()->create(['username' => 'petugas1', 'password' => 'password123']);

        foreach (range(1, 4) as $i) {
            $this->login('petugas1', 'salah');
        }
        $this->login('petugas1', 'password123')->assertRedirect('/dashboard');
        $this->post('/logout');

        // Hitungan mulai dari nol: 4 salah lagi belum mengunci
        foreach (range(1, 4) as $i) {
            $this->login('petugas1', 'salah');
        }
        $this->login('petugas1', 'password123')->assertRedirect('/dashboard');
    }

    public function test_pesan_gagal_sama_untuk_username_tidak_ada_dan_sandi_salah(): void
    {
        User::factory()->create(['username' => 'petugas1', 'password' => 'password123']);
        $pesan = ['login' => 'Email/username atau kata sandi salah.'];

        $this->login('tidak_ada', 'apa_saja')->assertSessionHasErrors($pesan);
        $this->login('petugas1', 'salah')->assertSessionHasErrors($pesan);
    }
}
