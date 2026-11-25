<?php

namespace Tests\Feature\Aspri;

use App\Models\Document;
use App\Models\DocumentVersion;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Integration test White-Box untuk Aspri\SkController::store()
 * (fitur Pengajuan SK).
 *
 * Alasan memakai integration test: store() memakai validasi, upload file, transaksi database,
 * ActivityLogger, dan FcmService sekaligus, sehingga diuji lewat HTTP request ke
 * route aspri.store (database MySQL khusus test: sigap_testing).
 *
 * Test Double yang dipakai:
 * - Fake : Storage::fake('public') menggantikan disk asli, sehingga file PDF yang diunggah
 *          tidak tersimpan sungguhan di storage proyek.
 * - Mock : FcmService (suntikan lewat constructor) diganti Mock. Diverifikasi sendToUser()
 *          dipanggil tepat sekali dengan validator yang benar, judul "Pengajuan SK Baru",
 *          dan data type = pengajuan_baru; pada kasus gagal, tidak boleh dipanggil sama sekali.
 *          Notifikasi FCM ke server Google tidak ikut terkirim saat test.
 *
 * Pemetaan ke analisis Pre-Lab (store):
 *   TC-SK-01 -> test_pengajuan_valid_tanpa_direksi
 *   TC-SK-02 -> test_pengajuan_valid_dengan_direksi
 *   TC-SK-03 -> test_nomor_sk_duplikat_ditolak
 *   TC-SK-04 -> test_pengajuan_tidak_valid_ditolak (semua data set)
 *   (tambahan) test_file_tepat_10240_kb_diterima
 */
class SkStoreTest extends TestCase
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

    private function dataValid(User $petinggi, array $timpa = []): array
    {
        return array_merge([
            'title'           => 'SK Pengangkatan Staf',
            'document_number' => '001/SK/2026',
            'pdf_file'        => UploadedFile::fake()->create('sk.pdf', 500, 'application/pdf'),
            'petinggi_ids'    => [$petinggi->id],
        ], $timpa);
    }

    // ------------------------------------------------------------------
    // Jalur sukses
    // ------------------------------------------------------------------

    public function test_pengajuan_valid_tanpa_direksi(): void
    {
        // Arrange
        $aspri    = User::factory()->create(['role' => 'aspri']);
        $petinggi = User::factory()->create(['role' => 'petinggi']);

        $this->mock(FcmService::class, function (MockInterface $mock) use ($petinggi) { // Mock
            $mock->shouldReceive('sendToUser')
                ->once()
                ->with(
                    Mockery::on(fn ($user) => $user->id === $petinggi->id),
                    'Pengajuan SK Baru',
                    Mockery::type('string'),
                    Mockery::on(fn ($data) => $data['type'] === 'pengajuan_baru' && $data['badge_count'] === '1')
                );
        });

        // Act
        $response = $this->actingAs($aspri)->post(route('aspri.store'), $this->dataValid($petinggi, [
            'need_sign' => [$petinggi->id => 1],
        ]));

        // Assert
        $dokumen = Document::where('document_number', '001/SK/2026')->first();
        $this->assertNotNull($dokumen);
        $response->assertRedirect(route('aspri.detail', $dokumen->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('documents', [
            'id'          => $dokumen->id,
            'user_id'     => $aspri->id,
            'title'       => 'SK Pengangkatan Staf',
            'status'      => 'DITINJAU',
            'need_direksi' => 0,
        ]);
        $this->assertDatabaseHas('document_versions', [
            'document_id'       => $dokumen->id,
            'version_number'    => 1,
            'original_filename' => 'sk.pdf',
        ]);
        $this->assertDatabaseHas('document_approvals', [
            'document_id' => $dokumen->id,
            'user_id'     => $petinggi->id,
            'role'        => 'validator',
            'status'      => 'MENUNGGU',
            'need_sign'   => 1,
        ]);
        $this->assertDatabaseCount('document_approvals', 1);

        $versi = DocumentVersion::where('document_id', $dokumen->id)->first();
        Storage::disk('public')->assertExists($versi->file_path);
    }

    public function test_pengajuan_valid_dengan_direksi(): void
    {
        // Arrange
        $aspri    = User::factory()->create(['role' => 'aspri']);
        $petinggi = User::factory()->create(['role' => 'petinggi']);
        $direksi  = User::factory()->create(['role' => 'dirut']);

        $this->mock(FcmService::class, function (MockInterface $mock) use ($petinggi) { // Mock
            // Hanya validator yang diberi notifikasi saat pengajuan baru
            $mock->shouldReceive('sendToUser')
                ->once()
                ->with(
                    Mockery::on(fn ($user) => $user->id === $petinggi->id),
                    'Pengajuan SK Baru',
                    Mockery::type('string'),
                    Mockery::type('array')
                );
        });

        // Act
        $this->actingAs($aspri)->post(route('aspri.store'), $this->dataValid($petinggi, [
            'perlu_direksi'    => 1,
            'direksi_ids'      => [$direksi->id],
            'need_sign_direksi' => [$direksi->id => 1],
        ]));

        // Assert
        $dokumen = Document::where('document_number', '001/SK/2026')->first();
        $this->assertNotNull($dokumen);
        $this->assertDatabaseHas('documents', ['id' => $dokumen->id, 'need_direksi' => 1]);
        $this->assertDatabaseHas('document_approvals', [
            'document_id' => $dokumen->id,
            'user_id'     => $petinggi->id,
            'role'        => 'validator',
        ]);
        $this->assertDatabaseHas('document_approvals', [
            'document_id' => $dokumen->id,
            'user_id'     => $direksi->id,
            'role'        => 'direksi',
            'status'      => 'MENUNGGU',
            'need_sign'   => 1,
        ]);
    }

    public function test_file_tepat_10240_kb_diterima(): void
    {
        // Arrange (batas atas ukuran file: max:10240 KB)
        $aspri    = User::factory()->create(['role' => 'aspri']);
        $petinggi = User::factory()->create(['role' => 'petinggi']);
        $this->mock(FcmService::class)->shouldReceive('sendToUser');

        // Act
        $response = $this->actingAs($aspri)->post(route('aspri.store'), $this->dataValid($petinggi, [
            'pdf_file' => UploadedFile::fake()->create('sk-besar.pdf', 10240, 'application/pdf'),
        ]));

        // Assert
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('documents', 1);
    }

    // ------------------------------------------------------------------
    // Jalur gagal
    // ------------------------------------------------------------------

    public function test_nomor_sk_duplikat_ditolak(): void
    {
        // Arrange
        $aspri    = User::factory()->create(['role' => 'aspri']);
        $aspriLama = User::factory()->create(['role' => 'aspri', 'name' => 'Aspri Lama']);
        $petinggi = User::factory()->create(['role' => 'petinggi']);

        Document::create([
            'user_id'         => $aspriLama->id,
            'title'           => 'SK Lama',
            'document_number' => '001/SK/2026',
            'status'          => 'DITINJAU',
        ]);

        $this->mock(FcmService::class)->shouldNotReceive('sendToUser'); // Mock: tidak boleh dipanggil

        // Act
        $response = $this->actingAs($aspri)->post(route('aspri.store'), $this->dataValid($petinggi));

        // Assert
        $response->assertSessionHasErrors([
            'document_number' => 'SK ini tidak bisa diupload karena nomor SK sudah pernah diajukan oleh Aspri Lama.',
        ]);
        $this->assertDatabaseCount('documents', 1);
        $this->assertDatabaseCount('document_versions', 0);
    }

    public static function dataTidakValidProvider(): array
    {
        return [
            'judul kosong'               => ['judul kosong', 'title'],
            'nomor SK kosong'            => ['nomor kosong', 'document_number'],
            'file bukan PDF'             => ['file bukan pdf', 'pdf_file'],
            'file 10241 KB (melebihi)'   => ['file 10241 kb', 'pdf_file'],
            'file tidak dikirim'         => ['file tidak ada', 'pdf_file'],
            'petinggi kosong'            => ['petinggi kosong', 'petinggi_ids'],
            'perlu direksi tanpa direksi' => ['direksi wajib', 'direksi_ids'],
            'id direksi tidak ada'       => ['direksi tidak ada', 'direksi_ids.0'],
        ];
    }

    #[DataProvider('dataTidakValidProvider')]
    public function test_pengajuan_tidak_valid_ditolak(string $kasus, string $fieldError): void
    {
        // Arrange
        $aspri    = User::factory()->create(['role' => 'aspri']);
        $petinggi = User::factory()->create(['role' => 'petinggi']);
        $this->mock(FcmService::class)->shouldNotReceive('sendToUser'); // Mock

        $data = $this->dataValid($petinggi);
        match ($kasus) {
            'judul kosong'      => $data['title'] = '',
            'nomor kosong'      => $data['document_number'] = '',
            'file bukan pdf'    => $data['pdf_file'] = UploadedFile::fake()->create('foto.jpg', 100, 'image/jpeg'),
            'file 10241 kb'     => $data['pdf_file'] = UploadedFile::fake()->create('besar.pdf', 10241, 'application/pdf'),
            'file tidak ada'    => $data = array_diff_key($data, ['pdf_file' => true]),
            'petinggi kosong'   => $data['petinggi_ids'] = [],
            'direksi wajib'     => $data['perlu_direksi'] = 1,
            'direksi tidak ada' => $data = array_merge($data, ['perlu_direksi' => 1, 'direksi_ids' => [999999]]),
        };

        // Act
        $response = $this->actingAs($aspri)->post(route('aspri.store'), $data);

        // Assert: ditolak di S1, tidak ada data tersimpan
        $response->assertSessionHasErrors($fieldError);
        $this->assertDatabaseCount('documents', 0);
        $this->assertDatabaseCount('document_versions', 0);
        $this->assertDatabaseCount('document_approvals', 0);
    }
}