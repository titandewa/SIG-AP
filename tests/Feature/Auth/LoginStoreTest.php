<?php

namespace Tests\Feature\Auth;

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Integration test White-Box untuk AuthenticatedSessionController::store()
 * (fitur Login).
 *
 * Alasan memakai integration test: store() terhubung langsung ke database (User::where),
 * session, dan LoginRequest, sehingga tidak bisa dipisahkan menjadi unit murni.
 * Alur utama diuji lewat HTTP request ke /login (database MySQL khusus test: sigap_testing);
 * satu cabang yang mustahil dicapai lewat database (role di luar enum) diuji dengan memanggil
 * controller langsung memakai Test Double.
 *
 * Test Double yang dipakai (pada test_role_tidak_dikenal_diarahkan_ke_login):
 * - Stub : $request->input('email') selalu mengembalikan email tetap, dan Auth::user()
 *          selalu mengembalikan objek dengan role 'tamu'.
 * - Mock : $request->authenticate() harus dipanggil tepat sekali, dan
 *          session()->regenerate() harus dipanggil tepat sekali.
 *
 * Pemetaan ke analisis Pre-Lab (store):
 *   TC-LG-01 -> test_redirect_sesuai_role ('aspri')
 *   TC-LG-02 -> test_redirect_sesuai_role ('petinggi')
 *   TC-LG-03 -> test_redirect_sesuai_role ('dirut')
 *   TC-LG-04 -> test_redirect_sesuai_role ('admin')
 *   TC-LG-05 -> test_akun_force_reset_menampilkan_modal_reset
 *   TC-LG-06 -> test_role_tidak_dikenal_diarahkan_ke_login
 *   (tambahan) test_password_salah_tidak_membuat_sesi_login
 */
class LoginStoreTest extends TestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    // ------------------------------------------------------------------
    // Cabang D2 (switch role) - jalur sukses
    // ------------------------------------------------------------------

    public static function roleProvider(): array
    {
        return [
            'TC-LG-01 aspri'    => ['aspri', 'app.aspri.dashboard'],
            'TC-LG-02 petinggi' => ['petinggi', 'app.petinggi.dashboard'],
            'TC-LG-03 dirut'    => ['dirut', 'app.dirut.dashboard'],
            'TC-LG-04 admin'    => ['admin', 'app.admin.dashboard'],
        ];
    }

    #[DataProvider('roleProvider')]
    public function test_redirect_sesuai_role(string $role, string $namaRoute): void
    {
        // Arrange
        $user = User::factory()->create(['role' => $role]);

        // Act
        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        // Assert
        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route($namaRoute, absolute: false));
    }

    // ------------------------------------------------------------------
    // Cabang D1 (akun berstatus force_password_reset)
    // ------------------------------------------------------------------

    public function test_akun_force_reset_menampilkan_modal_reset(): void
    {
        // Arrange
        $user = User::factory()->create([
            'role'                 => 'aspri',
            'force_password_reset' => true,
        ]);

        // Act
        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password',
        ]);

        // Assert: tidak login, modal reset muncul
        $this->assertGuest();
        $response->assertSessionHas('show_reset_modal', true);
        $response->assertSessionHas('reset_email', $user->email);
    }

    // ------------------------------------------------------------------
    // Jalur pengecualian dari authenticate() (password salah)
    // ------------------------------------------------------------------

    public function test_password_salah_tidak_membuat_sesi_login(): void
    {
        // Arrange
        $user = User::factory()->create(['role' => 'aspri']);

        // Act
        $response = $this->post('/login', [
            'email'    => $user->email,
            'password' => 'password-salah',
        ]);

        // Assert
        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    // ------------------------------------------------------------------
    // Cabang default pada switch (role di luar enum database)
    // ------------------------------------------------------------------

    public function test_role_tidak_dikenal_diarahkan_ke_login(): void
    {
        // Arrange
        $session = Mockery::mock(Session::class);
        $session->shouldReceive('regenerate')->once();          // Mock

        $request = Mockery::mock(LoginRequest::class);
        $request->shouldReceive('input')                         // Stub
            ->with('email')
            ->andReturn('tidak.ada@example.com');
        $request->shouldReceive('authenticate')->once();         // Mock
        $request->shouldReceive('session')->andReturn($session);

        Auth::shouldReceive('user')                              // Stub
            ->andReturn((object) ['role' => 'tamu']);

        // Act
        $response = (new AuthenticatedSessionController())->store($request);

        // Assert
        $this->assertSame(route('login'), $response->getTargetUrl());
    }
}