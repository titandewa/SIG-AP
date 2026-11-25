<?php

namespace Tests\Unit;

use App\Services\WhatsAppNotificationService;
use Carbon\Carbon;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Unit test White-Box untuk WhatsAppNotificationService
 * (fitur Kirim WA peninjauan: linkPeninjauan() dan formatNomorWA()).
 *
 * Test Double yang dipakai:
 * - Dummy : $dummyDokumen (new \stdClass) pada kasus validator null / phone kosong,
 *           karena parameter wajib diisi tetapi tidak pernah dipakai (fungsi berhenti di D1).
 * - Stub  : salamWaktu() dibuat selalu mengembalikan "Pagi" agar hasil tidak bergantung jam sistem,
 *           serta objek validator/dokumen dengan data tetap.
 * - Mock  : waLink() diverifikasi dipanggil tepat sekali dengan nomor yang benar,
 *           dan tidak boleh dipanggil sama sekali saat fungsi mengembalikan null.
 *
 * Pemetaan ke analisis Pre-Lab (linkPeninjauan):
 *   TC-01 -> test_link_memuat_data_dokumen_dan_nama_pengaju
 *   TC-02 -> test_mengembalikan_null_jika_validator_null
 *   TC-03 -> test_pengaju_bawaan_strip_jika_user_dokumen_null
 *   TC-04 -> test_mengembalikan_null_jika_phone_validator_kosong
 */
class WhatsAppNotificationServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    // ------------------------------------------------------------------
    // Helper (Arrange bersama)
    // ------------------------------------------------------------------

    /** Stub validator: data tetap. */
    private function buatValidatorStub(?string $phone = '081234567890'): object
    {
        return (object) [
            'name'  => 'Budi Santoso',
            'phone' => $phone,
        ];
    }

    /** Stub dokumen: data tetap, pengaju bisa dikosongkan. */
    private function buatDokumenStub(?string $namaPengaju = 'Aspri Uji'): object
    {
        return (object) [
            'title'           => 'SK Pengangkatan Staf',
            'document_number' => '001/SK/2026',
            'created_at'      => Carbon::parse('2026-11-25 10:00:00'),
            'user'            => $namaPengaju === null ? null : (object) ['name' => $namaPengaju],
        ];
    }

    /** Service dengan salamWaktu() di-stub; method lain memakai implementasi asli. */
    private function buatServiceDenganSalamStub(): MockInterface
    {
        $service = Mockery::mock(WhatsAppNotificationService::class)->makePartial();
        $service->shouldReceive('salamWaktu')->andReturn('Pagi');

        return $service;
    }

    // ------------------------------------------------------------------
    // linkPeninjauan() - cabang D1 (validator / nomor WA)
    // ------------------------------------------------------------------

    public function test_mengembalikan_null_jika_validator_null(): void
    {
        // Arrange
        $service = $this->buatServiceDenganSalamStub();
        $service->shouldNotReceive('waLink'); // Mock: tidak boleh ada link dibuat
        $dummyDokumen = new \stdClass();      // Dummy: tidak dipakai

        // Act
        $hasil = $service->linkPeninjauan(null, $dummyDokumen);

        // Assert
        $this->assertNull($hasil);
    }

    public function test_mengembalikan_null_jika_phone_validator_kosong(): void
    {
        // Arrange
        $service = $this->buatServiceDenganSalamStub();
        $service->shouldNotReceive('waLink');
        $validator = $this->buatValidatorStub('');
        $dummyDokumen = new \stdClass();

        // Act
        $hasil = $service->linkPeninjauan($validator, $dummyDokumen);

        // Assert
        $this->assertNull($hasil);
    }

    // ------------------------------------------------------------------
    // linkPeninjauan() - jalur sukses dan cabang D2 (nama pengaju)
    // ------------------------------------------------------------------

    public function test_link_memuat_data_dokumen_dan_nama_pengaju(): void
    {
        // Arrange
        $validator = $this->buatValidatorStub();
        $dokumen   = $this->buatDokumenStub('Aspri Uji');
        $pesanDikirim = null;

        $service = $this->buatServiceDenganSalamStub();
        $service->shouldReceive('waLink')
            ->once()
            ->with('081234567890', Mockery::type('string'))
            ->andReturnUsing(function ($nomor, $pesan) use (&$pesanDikirim) {
                $pesanDikirim = $pesan;

                return 'https://wa.me/stub';
            });

        // Act
        $hasil = $service->linkPeninjauan($validator, $dokumen);

        // Assert
        $this->assertSame('https://wa.me/stub', $hasil);
        $this->assertStringContainsString('Selamat Pagi Bapak/Ibu Budi Santoso', $pesanDikirim);
        $this->assertStringContainsString('Judul Surat : SK Pengangkatan Staf', $pesanDikirim);
        $this->assertStringContainsString('Nomor Surat : 001/SK/2026', $pesanDikirim);
        $this->assertStringContainsString(
            $dokumen->created_at->translatedFormat('d F Y, H:i'),
            $pesanDikirim
        );
        $this->assertStringContainsString('Diajukan oleh : Aspri Uji', $pesanDikirim);
        $this->assertStringContainsString(route('app.petinggi.dashboard'), $pesanDikirim);
    }

    public function test_pengaju_bawaan_strip_jika_user_dokumen_null(): void
    {
        // Arrange
        $validator = $this->buatValidatorStub();
        $dokumen   = $this->buatDokumenStub(null); // user = null
        $pesanDikirim = null;

        $service = $this->buatServiceDenganSalamStub();
        $service->shouldReceive('waLink')
            ->once()
            ->andReturnUsing(function ($nomor, $pesan) use (&$pesanDikirim) {
                $pesanDikirim = $pesan;

                return 'https://wa.me/stub';
            });

        // Act
        $service->linkPeninjauan($validator, $dokumen);

        // Assert
        $this->assertStringContainsString('Diajukan oleh : -', $pesanDikirim);
    }

    public function test_link_wa_me_menggunakan_nomor_berawalan_62_dan_pesan_terenkode(): void
    {
        // Arrange: waLink() memakai implementasi asli, hanya salamWaktu() yang di-stub
        $validator = $this->buatValidatorStub('081234567890');
        $dokumen   = $this->buatDokumenStub('Aspri Uji');
        $service   = $this->buatServiceDenganSalamStub();

        // Act
        $hasil = $service->linkPeninjauan($validator, $dokumen);

        // Assert
        $this->assertStringStartsWith('https://wa.me/6281234567890?text=', $hasil);
        $this->assertStringContainsString(rawurlencode('Diajukan oleh : Aspri Uji'), $hasil);
    }

    // ------------------------------------------------------------------
    // formatNomorWA() - cabang D1 (awalan 0) dan pembersihan karakter
    // ------------------------------------------------------------------

    public static function nomorProvider(): array
    {
        return [
            'awalan 0 diganti 62'            => ['081234567890', '6281234567890'],
            'sudah berawalan 62'             => ['6281234567890', '6281234567890'],
            'format +62 dengan spasi/strip'  => ['+62 812-3456-7890', '6281234567890'],
            'awalan 0 dengan tanda strip'    => ['0812-3456-7890', '6281234567890'],
            'string kosong'                  => ['', ''],
        ];
    }

    #[DataProvider('nomorProvider')]
    public function test_format_nomor_wa(string $input, string $diharapkan): void
    {
        // Arrange
        $service = new WhatsAppNotificationService();

        // Act
        $hasil = $service->formatNomorWA($input);

        // Assert
        $this->assertSame($diharapkan, $hasil);
    }
}