<?php

namespace Tests\Feature\Aspri;

use App\Models\Document;
use App\Models\NotificationHistory;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Integration test White-Box untuk Aspri\SkController::saveTtd()
 * (fitur Tanda Tangan / penempelan TTD oleh Aspri).
 *
 * Alasan memakai integration test: saveTtd() memakai validasi, database, dan penyimpanan file
 * sekaligus, sehingga diuji lewat HTTP request ke route aspri.ttd.save
 * (database MySQL khusus test: sigap_testing).
 *
 * Catatan: Storage::fake() TIDAK dipakai di file ini. Fungsi saveTtd() menyimpan file lewat
 * Storage::disk('public') tetapi menghitung hash dari storage_path('app/public/...') (path asli),
 * sehingga disk palsu membuat hash_file() gagal membaca file. Test memakai disk asli dan
 * menghapus berkas hasil test pada tearDown().
 *
 * Test Double: tidak ada yang dipakai pada fungsi ini. Semua ketergantungan (database dan disk)
 * dipakai langsung sebagai integration test. Test Double untuk fitur lain terdapat pada
 * SkStoreTest (Mock, Fake), SkRevisiTest (Spy, Fake), dan LoginStoreTest (Stub, Mock).
 *
 * Pemetaan ke analisis Pre-Lab (saveTtd):
 *   TC-TT-01 -> test_ttd_berhasil_ditempel_dan_hash_baru_dipakai
 *   TC-TT-02 -> test_hash_dan_waktu_ttd_yang_sudah_ada_tidak_ditimpa
 *   TC-TT-03 -> test_status_bukan_menunggu_ttd_ditolak (semua data set)
 *   TC-TT-04 -> test_input_tidak_valid_ditolak (semua data set)
 *   TC-TT-05 -> test_dokumen_milik_aspri_lain_menghasilkan_404
 *   (tambahan) test_dokumen_tidak_ditemukan_menghasilkan_404
 */
class SkTtdTest extends TestCase
{
    use RefreshDatabase;

    /** @var string[] berkas hasil test yang dihapus pada tearDown */
    private array $berkasTtd = [];

    protected function tearDown(): void
    {
        foreach ($this->berkasTtd as $path) {
            Storage::disk('public')->delete($path);
        }

        parent::tearDown();
    }

    // ------------------------------------------------------------------
    // Helper (Arrange bersama)
    // ------------------------------------------------------------------

    private function buatDokumen(User $aspri, string $status = 'DISETUJUI_TANPA_TTD', array $timpa = []): Document
    {
        return Document::create(array_merge([
            'user_id'         => $aspri->id,
            'title'           => 'SK Pengangkatan Staf',
            'document_number' => '001/SK/2026',
            'status'          => $status,
        ], $timpa));
    }

    private function kirimTtd(User $aspri, int $id, array $timpa = [])
    {
        return $this->actingAs($aspri)->post(route('aspri.ttd.save', $id), array_merge([
            'signed_pdf' => UploadedFile::fake()->create('signed.pdf', 100, 'application/pdf'),
            'qr_hash'    => 'HASH-BARU',
        ], $timpa));
    }

    // ------------------------------------------------------------------
    // Jalur sukses
    // ------------------------------------------------------------------

    public function test_ttd_berhasil_ditempel_dan_hash_baru_dipakai(): void
    {
        // Arrange
        $aspri   = User::factory()->create(['role' => 'aspri']);
        $dokumen = $this->buatDokumen($aspri);

        NotificationHistory::create([
            'user_id' => $aspri->id, 'title' => 'Notif A', 'body' => 'x',
            'data'    => ['document_id' => (string) $dokumen->id],
        ]);
        NotificationHistory::create([
            'user_id' => $aspri->id, 'title' => 'Notif lain', 'body' => 'y',
            'data'    => ['document_id' => '999999'],
        ]);

        // Act
        $response = $this->kirimTtd($aspri, $dokumen->id);

        // Assert
        $dokumen->refresh();
        $this->berkasTtd[] = $dokumen->signed_file_path;

        $response->assertRedirect(route('app.aspri.dashboard'));
        $response->assertSessionHas('success', 'Tanda tangan berhasil ditempel, dokumen kini sah sepenuhnya!');

        $this->assertSame('DISETUJUI', $dokumen->status);
        $this->assertSame('HASH-BARU', $dokumen->qr_code_hash);
        $this->assertNotNull($dokumen->signed_at);
        $this->assertStringStartsWith('documents/signed/signed_', $dokumen->signed_file_path);
        Storage::disk('public')->assertExists($dokumen->signed_file_path);
        $this->assertSame(
            hash_file('sha256', Storage::disk('public')->path($dokumen->signed_file_path)),
            $dokumen->file_hash
        );

        // Riwayat notifikasi dokumen ini dihapus, dokumen lain tidak
        $this->assertDatabaseMissing('notification_histories', ['title' => 'Notif A']);
        $this->assertDatabaseHas('notification_histories', ['title' => 'Notif lain']);
    }

    public function test_hash_dan_waktu_ttd_yang_sudah_ada_tidak_ditimpa(): void
    {
        // Arrange: dokumen sudah punya hash QR dan waktu tanda tangan dari proses sebelumnya
        $aspri   = User::factory()->create(['role' => 'aspri']);
        $waktuLama = Carbon::parse('2026-11-20 08:00:00');
        $dokumen = $this->buatDokumen($aspri, 'DISETUJUI_TANPA_TTD', [
            'qr_code_hash' => 'HASH-LAMA',
            'signed_at'    => $waktuLama,
        ]);

        // Act
        $this->kirimTtd($aspri, $dokumen->id, ['qr_hash' => 'HASH-BARU']);

        // Assert
        $dokumen->refresh();
        $this->berkasTtd[] = $dokumen->signed_file_path;

        $this->assertSame('DISETUJUI', $dokumen->status);
        $this->assertSame('HASH-LAMA', $dokumen->qr_code_hash);
        $this->assertTrue($dokumen->signed_at->equalTo($waktuLama));
    }

    public function test_qr_hash_boleh_tidak_dikirim(): void
    {
        // Arrange
        $aspri   = User::factory()->create(['role' => 'aspri']);
        $dokumen = $this->buatDokumen($aspri);

        // Act
        $response = $this->actingAs($aspri)->post(route('aspri.ttd.save', $dokumen->id), [
            'signed_pdf' => UploadedFile::fake()->create('signed.pdf', 100, 'application/pdf'),
        ]);

        // Assert
        $dokumen->refresh();
        $this->berkasTtd[] = $dokumen->signed_file_path;

        $response->assertSessionHasNoErrors();
        $this->assertSame('DISETUJUI', $dokumen->status);
        $this->assertNull($dokumen->qr_code_hash);
    }

    // ------------------------------------------------------------------
    // Jalur gagal
    // ------------------------------------------------------------------

    public static function statusBukanMenungguTtdProvider(): array
    {
        return [
            'DITINJAU'  => ['DITINJAU'],
            'PENDING'   => ['PENDING'],
            'SIAP_TTD'  => ['SIAP_TTD'],
            'DISETUJUI' => ['DISETUJUI'],
            'DITOLAK'   => ['DITOLAK'],
        ];
    }

    #[DataProvider('statusBukanMenungguTtdProvider')]
    public function test_status_bukan_menunggu_ttd_ditolak(string $status): void
    {
        // Arrange
        $aspri   = User::factory()->create(['role' => 'aspri']);
        $dokumen = $this->buatDokumen($aspri, $status);

        // Act
        $response = $this->kirimTtd($aspri, $dokumen->id);

        // Assert
        $response->assertSessionHas('error', 'Dokumen ini tidak sedang menunggu penempelan TTD.');
        $this->assertDatabaseHas('documents', [
            'id' => $dokumen->id, 'status' => $status, 'signed_file_path' => null,
        ]);
    }

    public static function inputTidakValidProvider(): array
    {
        return [
            'file tidak dikirim' => ['file tidak ada'],
            'file bukan PDF'     => ['file bukan pdf'],
        ];
    }

    #[DataProvider('inputTidakValidProvider')]
    public function test_input_tidak_valid_ditolak(string $kasus): void
    {
        // Arrange
        $aspri   = User::factory()->create(['role' => 'aspri']);
        $dokumen = $this->buatDokumen($aspri);

        $data = $kasus === 'file bukan pdf'
            ? ['signed_pdf' => UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg')]
            : [];

        // Act
        $response = $this->actingAs($aspri)->post(route('aspri.ttd.save', $dokumen->id), $data);

        // Assert
        $response->assertSessionHasErrors('signed_pdf');
        $this->assertDatabaseHas('documents', ['id' => $dokumen->id, 'status' => 'DISETUJUI_TANPA_TTD']);
    }

    public function test_dokumen_milik_aspri_lain_menghasilkan_404(): void
    {
        // Arrange
        $pemilik = User::factory()->create(['role' => 'aspri']);
        $aspriLain = User::factory()->create(['role' => 'aspri']);
        $dokumen = $this->buatDokumen($pemilik);

        // Act
        $response = $this->kirimTtd($aspriLain, $dokumen->id);

        // Assert
        $response->assertNotFound();
        $this->assertDatabaseHas('documents', ['id' => $dokumen->id, 'status' => 'DISETUJUI_TANPA_TTD']);
    }

    public function test_dokumen_tidak_ditemukan_menghasilkan_404(): void
    {
        // Arrange
        $aspri = User::factory()->create(['role' => 'aspri']);

        // Act
        $response = $this->kirimTtd($aspri, 999999);

        // Assert
        $response->assertNotFound();
    }
}