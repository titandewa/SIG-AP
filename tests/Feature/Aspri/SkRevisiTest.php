<?php

namespace Tests\Feature\Aspri;

use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\DocumentVersion;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Integration test White-Box untuk Aspri\SkController::uploadRevision()
 * (fitur Revisi dan Riwayat Versi).
 *
 * Alasan memakai integration test: uploadRevision() memakai validasi, upload file, transaksi
 * database, dan FcmService sekaligus, sehingga diuji lewat HTTP request ke route aspri.revisi
 * (database MySQL khusus test: sigap_testing).
 *
 * Test Double yang dipakai:
 * - Fake : Storage::fake('public') menggantikan disk asli untuk file PDF revisi.
 * - Spy  : FcmService diganti Spy ($this->spy). Spy membiarkan semua pemanggilan lewat,
 *          lalu pemanggilan diperiksa SETELAH request selesai (shouldHaveReceived /
 *          shouldNotHaveReceived): siapa penerima notifikasi, isi data, dan jumlah panggilan.
 *
 * Pemetaan ke analisis Pre-Lab (uploadRevision):
 *   TC-RV-01 -> test_revisi_dari_validator_mengulang_semua_peninjauan
 *   TC-RV-02 -> test_revisi_dari_direksi_hanya_mengulang_peninjauan_direksi
 *   TC-RV-03 -> test_status_bukan_revisi_ditolak (semua data set)
 *   TC-RV-04 -> test_input_tidak_valid_ditolak (semua data set)
 *   TC-RV-05 -> test_dokumen_tidak_ditemukan_menghasilkan_404
 */
class SkRevisiTest extends TestCase
{
    use RefreshDatabase;
    use MockeryPHPUnitIntegration;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public'); // Fake
    }

    // ------------------------------------------------------------------
    // Helper (Arrange bersama)
    // ------------------------------------------------------------------

    /** Dokumen dengan 1 versi, 1 validator, dan 1 direksi (keduanya sudah TERIMA). */
    private function buatDokumen(string $status): array
    {
        $aspri    = User::factory()->create(['role' => 'aspri']);
        $petinggi = User::factory()->create(['role' => 'petinggi']);
        $direksi  = User::factory()->create(['role' => 'dirut']);

        $dokumen = Document::create([
            'user_id'         => $aspri->id,
            'title'           => 'SK Lama',
            'document_number' => '001/SK/2026',
            'status'          => $status,
            'need_direksi'    => true,
        ]);
        DocumentVersion::create([
            'document_id'       => $dokumen->id,
            'version_number'    => 1,
            'file_path'         => 'documents/v1/sk-lama.pdf',
            'original_filename' => 'sk-lama.pdf',
        ]);
        DocumentApproval::create([
            'document_id' => $dokumen->id, 'user_id' => $petinggi->id,
            'role' => 'validator', 'status' => 'TERIMA', 'need_sign' => 0,
        ]);
        DocumentApproval::create([
            'document_id' => $dokumen->id, 'user_id' => $direksi->id,
            'role' => 'direksi', 'status' => 'TERIMA', 'need_sign' => 0,
        ]);

        return [$aspri, $petinggi, $direksi, $dokumen];
    }

    private function kirimRevisi(User $aspri, int $id, array $timpa = [])
    {
        return $this->actingAs($aspri)->post(route('aspri.revisi', $id), array_merge([
            'title'    => 'SK Revisi',
            'pdf_file' => UploadedFile::fake()->create('revisi.pdf', 300, 'application/pdf'),
        ], $timpa));
    }

    // ------------------------------------------------------------------
    // Jalur sukses
    // ------------------------------------------------------------------

    public function test_revisi_dari_validator_mengulang_semua_peninjauan(): void
    {
        // Arrange: status PENDING = revisi diminta validator
        [$aspri, $petinggi, $direksi, $dokumen] = $this->buatDokumen('PENDING');
        $fcm = $this->spy(FcmService::class); // Spy

        // Act
        $response = $this->kirimRevisi($aspri, $dokumen->id);

        // Assert
        $response->assertSessionHas('success', 'Revisi berhasil diunggah, menunggu peninjauan ulang dari Validator.');
        $this->assertDatabaseHas('documents', ['id' => $dokumen->id, 'status' => 'DITINJAU', 'title' => 'SK Revisi']);
        $this->assertDatabaseHas('document_versions', [
            'document_id' => $dokumen->id, 'version_number' => 2, 'original_filename' => 'SK Revisi.pdf',
        ]);
        $this->assertSame(2, DocumentApproval::where('document_id', $dokumen->id)->where('status', 'MENUNGGU')->count());

        $versiBaru = DocumentVersion::where('document_id', $dokumen->id)->where('version_number', 2)->first();
        Storage::disk('public')->assertExists($versiBaru->file_path);

        $fcm->shouldHaveReceived('sendToUser')->once()->with(
            Mockery::on(fn ($user) => $user->id === $petinggi->id),
            'Revisi SK Diunggah',
            Mockery::type('string'),
            Mockery::on(fn ($data) => $data['type'] === 'revisi_diunggah'
                && $data['badge_count'] === '1'
                && $data['url'] === route('app.petinggi.dashboard', ['doc' => $dokumen->id]))
        );
    }

    public function test_revisi_dari_direksi_hanya_mengulang_peninjauan_direksi(): void
    {
        // Arrange: status PENDING_DIREKSI = revisi diminta direksi
        [$aspri, $petinggi, $direksi, $dokumen] = $this->buatDokumen('PENDING_DIREKSI');
        $fcm = $this->spy(FcmService::class); // Spy

        // Act
        $response = $this->kirimRevisi($aspri, $dokumen->id);

        // Assert
        $response->assertSessionHas('success', 'Revisi berhasil diunggah, menunggu peninjauan ulang dari Direksi.');
        $this->assertDatabaseHas('documents', ['id' => $dokumen->id, 'status' => 'DITINJAU_DIREKSI']);
        $this->assertDatabaseHas('document_approvals', ['document_id' => $dokumen->id, 'role' => 'validator', 'status' => 'TERIMA']);
        $this->assertDatabaseHas('document_approvals', ['document_id' => $dokumen->id, 'role' => 'direksi', 'status' => 'MENUNGGU']);

        $fcm->shouldHaveReceived('sendToUser')->once()->with(
            Mockery::on(fn ($user) => $user->id === $direksi->id),
            'Revisi SK Diunggah',
            Mockery::type('string'),
            Mockery::on(fn ($data) => $data['type'] === 'revisi_diunggah'
                && !array_key_exists('badge_count', $data)
                && $data['url'] === route('app.dirut.dashboard', ['doc' => $dokumen->id]))
        );
    }

    // ------------------------------------------------------------------
    // Jalur gagal
    // ------------------------------------------------------------------

    public static function statusBukanRevisiProvider(): array
    {
        return [
            'DITINJAU'            => ['DITINJAU'],
            'DITINJAU_DIREKSI'    => ['DITINJAU_DIREKSI'],
            'SIAP_TTD'            => ['SIAP_TTD'],
            'DISETUJUI_TANPA_TTD' => ['DISETUJUI_TANPA_TTD'],
            'DISETUJUI'           => ['DISETUJUI'],
            'DITOLAK'             => ['DITOLAK'],
        ];
    }

    #[DataProvider('statusBukanRevisiProvider')]
    public function test_status_bukan_revisi_ditolak(string $status): void
    {
        // Arrange
        [$aspri, $petinggi, $direksi, $dokumen] = $this->buatDokumen($status);
        $fcm = $this->spy(FcmService::class); // Spy

        // Act
        $response = $this->kirimRevisi($aspri, $dokumen->id);

        // Assert: tidak ada perubahan dan tidak ada notifikasi
        $response->assertSessionHas('error', 'Dokumen ini tidak sedang dalam status revisi.');
        $this->assertDatabaseCount('document_versions', 1);
        $this->assertDatabaseHas('documents', ['id' => $dokumen->id, 'status' => $status, 'title' => 'SK Lama']);
        $fcm->shouldNotHaveReceived('sendToUser');
    }

    public static function inputTidakValidProvider(): array
    {
        return [
            'judul kosong'      => ['judul kosong', 'title'],
            'file bukan PDF'    => ['file bukan pdf', 'pdf_file'],
            'file 10241 KB'     => ['file 10241 kb', 'pdf_file'],
            'file tidak dikirim' => ['file tidak ada', 'pdf_file'],
        ];
    }

    #[DataProvider('inputTidakValidProvider')]
    public function test_input_tidak_valid_ditolak(string $kasus, string $fieldError): void
    {
        // Arrange
        [$aspri, $petinggi, $direksi, $dokumen] = $this->buatDokumen('PENDING');
        $fcm = $this->spy(FcmService::class); // Spy

        $data = [
            'title'    => 'SK Revisi',
            'pdf_file' => UploadedFile::fake()->create('revisi.pdf', 300, 'application/pdf'),
        ];
        match ($kasus) {
            'judul kosong'   => $data['title'] = '',
            'file bukan pdf' => $data['pdf_file'] = UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg'),
            'file 10241 kb'  => $data['pdf_file'] = UploadedFile::fake()->create('besar.pdf', 10241, 'application/pdf'),
            'file tidak ada' => $data = ['title' => 'SK Revisi'],
        };

        // Act
        $response = $this->actingAs($aspri)->post(route('aspri.revisi', $dokumen->id), $data);

        // Assert
        $response->assertSessionHasErrors($fieldError);
        $this->assertDatabaseCount('document_versions', 1);
        $this->assertDatabaseHas('documents', ['id' => $dokumen->id, 'status' => 'PENDING']);
        $fcm->shouldNotHaveReceived('sendToUser');
    }

    public function test_dokumen_tidak_ditemukan_menghasilkan_404(): void
    {
        // Arrange
        $aspri = User::factory()->create(['role' => 'aspri']);
        $this->spy(FcmService::class);

        // Act
        $response = $this->kirimRevisi($aspri, 999999);

        // Assert
        $response->assertNotFound();
    }
}