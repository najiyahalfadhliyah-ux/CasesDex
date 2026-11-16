<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use RuntimeException;
use Tests\TestCase;

// Perbaikan temuan Tahap 10 (docs/laporan-pengujian.md)
class SecurityFixesTest extends TestCase
{
    use RefreshDatabase;

    // T-01: sesi akun yang dinonaktifkan berakhir pada request berikutnya, apa pun driver sesinya (tes memakai driver "array")
    public function test_sesi_akun_nonaktif_langsung_berakhir(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->get('/dashboard')->assertOk();

        $user->update(['is_active' => false]);

        $this->get('/dashboard')->assertRedirect('/login')
            ->assertSessionHasErrors(['login' => 'Akun Anda telah dinonaktifkan. Hubungi admin.']);
        $this->assertGuest();
    }

    public function test_akun_aktif_dan_tamu_tidak_terpengaruh(): void
    {
        $this->actingAs(User::factory()->create())->get('/dashboard')->assertOk();
        $this->get('/cases')->assertOk();
        $this->assertAuthenticated();

        auth()->logout();
        $this->get('/login')->assertOk();
    }

    // T-02: halaman error berbahasa Indonesia tanpa detail teknis (seperti di produksi, APP_DEBUG=false)
    public function test_halaman_error_jelas_dan_tidak_membocorkan_detail(): void
    {
        config(['app.debug' => false]);
        Route::middleware('web')->get('/_uji-419', fn () => abort(419));
        Route::middleware('web')->get('/_uji-500', fn () => throw new RuntimeException('SQLSTATE rahasia di storage/app'));
        $user = User::factory()->create();

        $this->get('/alamat-tidak-ada')->assertNotFound()->assertSee('Halaman tidak ditemukan');
        // Model tidak ditemukan: nama model/kelas tidak tampil
        $this->actingAs($user)->get('/cases/999999')->assertNotFound()->assertSee('Halaman tidak ditemukan')->assertDontSee('CaseRecord');
        $this->get('/_uji-419')->assertStatus(419)->assertSee('Sesi Anda telah berakhir')->assertSee('Masuk kembali');
        $this->get('/_uji-500')->assertStatus(500)->assertSee('Terjadi kesalahan pada sistem')
            ->assertDontSee('SQLSTATE')->assertDontSee('storage/app');
    }

    // T-03 / T-04: contoh konfigurasi sesuai proyek (MySQL, batas berkas, sesi 30 menit, catatan produksi)
    public function test_env_example_sesuai_proyek(): void
    {
        $example = file_get_contents(base_path('.env.example'));

        foreach (['DB_CONNECTION=mysql', 'DB_DATABASE=arsip_kasus', 'EVIDENCE_MAX_KB=', 'SESSION_LIFETIME=30', 'APP_DEBUG=false'] as $line) {
            $this->assertStringContainsString($line, $example);
        }
        $this->assertDoesNotMatchRegularExpression('/^DB_PASSWORD=.+$/m', $example); // tanpa kredensial asli
    }
}
