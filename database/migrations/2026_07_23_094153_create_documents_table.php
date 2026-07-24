<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Aspri/Pengaju
            $table->string('title');
            $table->string('document_number')->unique()->nullable();
            $table->enum('status', ['DITINJAU', 'PENDING', 'DITOLAK', 'SIAP_TTD', 'DISETUJUI'])->default('DITINJAU');
            $table->string('final_pdf_path')->nullable(); // Lokasi PDF Final setelah TTD Dirut
            $table->string('qr_code_hash')->unique()->nullable(); // Unique code untuk verifikasi QR
            $table->timestamp('signed_at')->nullable();
            $table->timestamps();
        });
    }
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
