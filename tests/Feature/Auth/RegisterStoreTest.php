<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Integration test White-Box untuk RegisteredUserController::store()
 * (fitur Register).
 *
 * Alasan memakai integration test: store() memakai validasi request, database (User::create),
 * event, dan session login sekaligus, sehingga diuji lewat HTTP request ke /register
 * (database MySQL khusus test: sigap_testing).
 *
 * Test Double yang dipakai:
 * - Fake : Event::fake([Registered::class]) menggantikan dispatcher event asli agar event
 *          Registered tidak menjalankan listener sungguhan, tetapi tetap bisa diverifikasi
 *          (assertDispatched / assertNotDispatched).
 *
 * Pemetaan ke analisis Pre-Lab (store):
 *   TC-RG-01 -> test_registrasi_valid_membuat_user_dan_login
 *   TC-RG-02 -> test_registrasi_tidak_valid_ditolak (semua data set)
 *               + test_email_yang_sudah_terdaftar_ditolak
 *   (tambahan) test_batas_panjang_phone_valid
 */
class RegisterStoreTest extends TestCase
{
    use RefreshDatabase;

    /** Data registrasi valid; bisa ditimpa per kasus. */
    private function dataValid(array $timpa = []): array
    {
        return array_merge([
            'name'                  => 'Dewa Uji',
            'email'                 => 'dewa.uji@example.com',
            'phone'                 => '081234567890',
            'role'                  => 'aspri',
            'password'              => 'Password123',
            'password_confirmation' => 'Password123',
        ], $timpa);
    }

    // ------------------------------------------------------------------
    // Jalur sukses (validasi lolos)
    // ------------------------------------------------------------------

    public function test_registrasi_valid_membuat_user_dan_login(): void
    {
        // Arrange
        Event::fake([Registered::class]); // Fake

        // Act
        $response = $this->post('/register', $this->dataValid());

        // Assert
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', [
            'name'  => 'Dewa Uji',
            'email' => 'dewa.uji@example.com',
            'phone' => '081234567890',
            'role'  => 'aspri',
        ]);

        $user = User::where('email', 'dewa.uji@example.com')->first();
        $this->assertTrue(Hash::check('Password123', $user->password));
        Event::assertDispatched(Registered::class);
    }

    public static function phoneValidProvider(): array
    {
        return [
            '10 digit (batas bawah)' => ['0812345678'],
            '15 digit (batas atas)'  => ['081234567890123'],
            'diawali tanda +'        => ['+6281234567890'],
        ];
    }

    #[DataProvider('phoneValidProvider')]
    public function test_batas_panjang_phone_valid(string $phone): void
    {
        // Arrange
        Event::fake([Registered::class]);

        // Act
        $response = $this->post('/register', $this->dataValid(['phone' => $phone]));

        // Assert
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users', ['phone' => $phone]);
    }

    // ------------------------------------------------------------------
    // Jalur gagal (validasi menolak, fungsi berhenti di S1)
    // ------------------------------------------------------------------

    public static function dataTidakValidProvider(): array
    {
        return [
            'nama kosong'              => [['name' => ''], 'name'],
            'email format salah'       => [['email' => 'bukan-email'], 'email'],
            'email huruf besar'        => [['email' => 'Dewa@Example.com'], 'email'],
            'phone 9 digit'            => [['phone' => '081234567'], 'phone'],
            'phone 16 digit'           => [['phone' => '0812345678901234'], 'phone'],
            'phone mengandung huruf'   => [['phone' => '08123abc7890'], 'phone'],
            'role tidak valid'         => [['role' => 'admin'], 'role'],
            'konfirmasi password beda' => [['password_confirmation' => 'Beda12345'], 'password'],
            'password kurang 8 karakter' => [['password' => 'Pass12', 'password_confirmation' => 'Pass12'], 'password'],
        ];
    }

    #[DataProvider('dataTidakValidProvider')]
    public function test_registrasi_tidak_valid_ditolak(array $timpa, string $fieldError): void
    {
        // Arrange
        Event::fake([Registered::class]);

        // Act
        $response = $this->post('/register', $this->dataValid($timpa));

        // Assert: ditolak, user tidak dibuat, event tidak dikirim, tidak login
        $response->assertSessionHasErrors($fieldError);
        $this->assertDatabaseCount('users', 0);
        $this->assertGuest();
        Event::assertNotDispatched(Registered::class);
    }

    public function test_email_yang_sudah_terdaftar_ditolak(): void
    {
        // Arrange
        Event::fake([Registered::class]);
        User::factory()->create(['email' => 'dewa.uji@example.com']);

        // Act
        $response = $this->post('/register', $this->dataValid());

        // Assert
        $response->assertSessionHasErrors('email');
        $this->assertDatabaseCount('users', 1);
        Event::assertNotDispatched(Registered::class);
    }
}