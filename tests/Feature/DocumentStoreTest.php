<?php

namespace Tests\Feature;

use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_aspri_dapat_mengajukan_sk_baru()
    {
        Storage::fake('public');

        $aspri = User::factory()->create(['role' => 'aspri']);
        $petinggi = User::factory()->create(['role' => 'petinggi']);

        $response = $this->actingAs($aspri)->post(route('aspri.store'), [
            'title' => 'SK Uji Coba',
            'pdf_file' => UploadedFile::fake()->create('sk-test.pdf', 500, 'application/pdf'),
            'petinggi_ids' => [$petinggi->id],
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('documents', [
            'title' => 'SK Uji Coba',
            'status' => 'DITINJAU',
        ]);
        $this->assertDatabaseHas('document_approvals', [
            'user_id' => $petinggi->id,
            'status' => 'MENUNGGU',
        ]);
    }

    public function test_nomor_dokumen_selalu_unik_walau_diajukan_berkali_kali()
    {
        Storage::fake('public');

        $aspri = User::factory()->create(['role' => 'aspri']);
        $petinggi = User::factory()->create(['role' => 'petinggi']);

        foreach (range(1, 5) as $i) {
            $this->actingAs($aspri)->post(route('aspri.store'), [
                'title' => "SK Uji Coba $i",
                'pdf_file' => UploadedFile::fake()->create("sk-test-$i.pdf", 500, 'application/pdf'),
                'petinggi_ids' => [$petinggi->id],
            ]);
        }

        $documentNumbers = Document::pluck('document_number');

        $this->assertCount(5, $documentNumbers);
        $this->assertCount(5, $documentNumbers->unique());
    }

    public function test_pengajuan_gagal_tanpa_judul()
    {
        Storage::fake('public');

        $aspri = User::factory()->create(['role' => 'aspri']);
        $petinggi = User::factory()->create(['role' => 'petinggi']);

        $response = $this->actingAs($aspri)->post(route('aspri.store'), [
            'pdf_file' => UploadedFile::fake()->create('sk-test.pdf', 500, 'application/pdf'),
            'petinggi_ids' => [$petinggi->id],
        ]);

        $response->assertSessionHasErrors('title');
    }
}