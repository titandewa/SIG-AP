<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\DocumentApproval;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Integration test White-Box untuk DocumentController::addComment()
 * (fitur Komentar Dokumen), termasuk pengecekan hak akses assertCanAccessDiscussion().
 *
 * Alasan memakai integration test: addComment() memakai validasi, database, dan data pengguna
 * yang sedang login, sehingga diuji lewat HTTP request ke route document.comment
 * (database MySQL khusus test: sigap_testing).
 *
 * Test Double: tidak ada yang dipakai pada fungsi ini. Semua ketergantungan (database dan sesi
 * login) dipakai langsung sebagai integration test. Test Double untuk fitur lain terdapat pada
 * SkStoreTest (Mock, Fake), SkRevisiTest (Spy, Fake), dan LoginStoreTest (Stub, Mock).
 *
 * Pemetaan ke analisis Pre-Lab (addComment):
 *   TC-KM-01 -> test_pemilik_dokumen_mengirim_komentar_biasa
 *   TC-KM-02 -> test_reviewer_mengirim_komentar_lewat_json
 *   TC-KM-03 -> test_pengguna_tanpa_akses_ditolak_403
 *   TC-KM-04 -> test_komentar_tidak_valid_ditolak (semua data set)
 *   TC-KM-05 -> test_dokumen_tidak_ditemukan_menghasilkan_404
 *   (tambahan) test_komentar_batas_panjang_valid
 */
class DocumentCommentTest extends TestCase
{
    use RefreshDatabase;

    // ------------------------------------------------------------------
    // Helper (Arrange bersama)
    // ------------------------------------------------------------------

    /** @return array{0: User, 1: User, 2: User, 3: Document} pemilik, reviewer, orang lain, dokumen */
    private function siapkan(): array
    {
        $pemilik  = User::factory()->create(['role' => 'aspri']);
        $reviewer = User::factory()->create(['role' => 'petinggi']);
        $orangLain = User::factory()->create(['role' => 'petinggi']);

        $dokumen = Document::create([
            'user_id'         => $pemilik->id,
            'title'           => 'SK Pengangkatan Staf',
            'document_number' => '001/SK/2026',
            'status'          => 'DITINJAU',
        ]);
        DocumentApproval::create([
            'document_id' => $dokumen->id, 'user_id' => $reviewer->id,
            'role' => 'validator', 'status' => 'MENUNGGU', 'need_sign' => 0,
        ]);

        return [$pemilik, $reviewer, $orangLain, $dokumen];
    }

    // ------------------------------------------------------------------
    // Jalur sukses
    // ------------------------------------------------------------------

    public function test_pemilik_dokumen_mengirim_komentar_biasa(): void
    {
        // Arrange
        [$pemilik, $reviewer, $orangLain, $dokumen] = $this->siapkan();

        // Act
        $response = $this->actingAs($pemilik)->post(route('document.comment', $dokumen->id), [
            'comment' => 'Mohon cek ulang pasal 2.',
        ]);

        // Assert
        $response->assertSessionHas('success', 'Pesan terkirim.');
        $this->assertDatabaseHas('comments', [
            'document_id' => $dokumen->id,
            'user_id'     => $pemilik->id,
            'comment'     => 'Mohon cek ulang pasal 2.',
        ]);
    }

    public function test_reviewer_mengirim_komentar_lewat_json(): void
    {
        // Arrange
        [$pemilik, $reviewer, $orangLain, $dokumen] = $this->siapkan();

        // Act
        $response = $this->actingAs($reviewer)->postJson(route('document.comment', $dokumen->id), [
            'comment' => 'Sudah saya tinjau.',
        ]);

        // Assert
        $response->assertOk();
        $response->assertJson([
            'success' => true,
            'comment' => ['comment' => 'Sudah saya tinjau.'],
        ]);
        $this->assertDatabaseHas('comments', [
            'document_id' => $dokumen->id,
            'user_id'     => $reviewer->id,
            'comment'     => 'Sudah saya tinjau.',
        ]);
    }

    public static function komentarBatasValidProvider(): array
    {
        return [
            '1 karakter (batas bawah)'    => [1],
            '2000 karakter (batas atas)'  => [2000],
        ];
    }

    #[DataProvider('komentarBatasValidProvider')]
    public function test_komentar_batas_panjang_valid(int $panjang): void
    {
        // Arrange
        [$pemilik, $reviewer, $orangLain, $dokumen] = $this->siapkan();

        // Act
        $response = $this->actingAs($pemilik)->post(route('document.comment', $dokumen->id), [
            'comment' => str_repeat('a', $panjang),
        ]);

        // Assert
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseCount('comments', 1);
    }

    // ------------------------------------------------------------------
    // Jalur gagal
    // ------------------------------------------------------------------

    public function test_pengguna_tanpa_akses_ditolak_403(): void
    {
        // Arrange: bukan pemilik dan bukan reviewer dokumen ini
        [$pemilik, $reviewer, $orangLain, $dokumen] = $this->siapkan();

        // Act
        $response = $this->actingAs($orangLain)->post(route('document.comment', $dokumen->id), [
            'comment' => 'Boleh ikut komentar?',
        ]);

        // Assert
        $response->assertForbidden();
        $this->assertDatabaseCount('comments', 0);
    }

    public static function komentarTidakValidProvider(): array
    {
        return [
            'komentar kosong'            => [['comment' => '']],
            'komentar tidak dikirim'     => [[]],
            '2001 karakter (melebihi)'   => [['comment' => str_repeat('a', 2001)]],
            'komentar bukan teks (array)' => [['comment' => ['a', 'b']]],
        ];
    }

    #[DataProvider('komentarTidakValidProvider')]
    public function test_komentar_tidak_valid_ditolak(array $data): void
    {
        // Arrange
        [$pemilik, $reviewer, $orangLain, $dokumen] = $this->siapkan();

        // Act
        $response = $this->actingAs($pemilik)->post(route('document.comment', $dokumen->id), $data);

        // Assert
        $response->assertSessionHasErrors('comment');
        $this->assertDatabaseCount('comments', 0);
    }

    public function test_dokumen_tidak_ditemukan_menghasilkan_404(): void
    {
        // Arrange
        $pengguna = User::factory()->create(['role' => 'aspri']);

        // Act
        $response = $this->actingAs($pengguna)->post(route('document.comment', 999999), [
            'comment' => 'Halo',
        ]);

        // Assert
        $response->assertNotFound();
    }
}