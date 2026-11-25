<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Integration test White-Box untuk PasswordResetLinkController::store()
 * (fitur Forgot Password).
 *
 * Alasan memakai integration test: store() memvalidasi email lewat database
 * (aturan exists:users,email) lalu mengubah data user, sehingga diuji lewat HTTP request
 * ke /forgot-password (database MySQL khusus test: sigap_testing).
 *
 * Pemetaan ke analisis Pre-Lab (store):
 *   TC-FP-01 -> test_password_berhasil_diubah_untuk_email_terdaftar
 *   TC-FP-02 -> test_email_tidak_terdaftar_ditolak
 *   TC-FP-03 -> test_input_tidak_valid_ditolak (semua data set)
 */
class ForgotPasswordStoreTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Jalur sukses (validasi lolos)
    // ------------------------------------------------------------------

    public function test_password_berhasil_diubah_untuk_email_terdaftar(): void
    {
        // Arrange: akun berstatus force_password_reset = true
        $user = User::factory()->create([
            'email'                => 'dewa.uji@example.com',
            'force_password_reset' => true,
        ]);

        // Act
        $response = $this->post('/forgot-password', [
            'email'                 => 'dewa.uji@example.com',
            'password'              => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ]);

        // Assert
        $response->assertRedirect(route('login'));
        $response->assertSessionHas('status', 'Password berhasil diubah. Silakan login dengan password baru.');

        $user->refresh();
        $this->assertTrue(Hash::check('PasswordBaru123', $user->password));
        $this->assertFalse((bool) $user->force_password_reset);
    }

    // ------------------------------------------------------------------
    // Jalur gagal (validasi menolak, fungsi berhenti di S1)
    // ------------------------------------------------------------------

    public function test_email_tidak_terdaftar_ditolak(): void
    {
        // Act
        $response = $this->post('/forgot-password', [
            'email'                 => 'tidak.ada@example.com',
            'password'              => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ]);

        // Assert
        $response->assertSessionHasErrors(['email' => 'Email tidak terdaftar.']);
    }

    public static function inputTidakValidProvider(): array
    {
        return [
            'email kosong'             => [['email' => ''], 'email'],
            'email format salah'       => [['email' => 'bukan-email'], 'email'],
            'password kosong'          => [['password' => '', 'password_confirmation' => ''], 'password'],
            'password kurang 8 karakter' => [['password' => 'Pass12', 'password_confirmation' => 'Pass12'], 'password'],
            'konfirmasi password beda' => [['password_confirmation' => 'Beda12345'], 'password'],
        ];
    }

    #[DataProvider('inputTidakValidProvider')]
    public function test_input_tidak_valid_ditolak(array $timpa, string $fieldError): void
    {
        // Arrange
        $user = User::factory()->create(['email' => 'dewa.uji@example.com']);
        $passwordLama = $user->password;

        // Act
        $response = $this->post('/forgot-password', array_merge([
            'email'                 => 'dewa.uji@example.com',
            'password'              => 'PasswordBaru123',
            'password_confirmation' => 'PasswordBaru123',
        ], $timpa));

        // Assert: ditolak dan password lama tidak berubah
        $response->assertSessionHasErrors($fieldError);
        $this->assertSame($passwordLama, $user->fresh()->password);
    }
}