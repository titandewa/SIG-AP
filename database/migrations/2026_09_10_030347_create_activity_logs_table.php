<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('actor_id')->constrained('users')->cascadeOnDelete();
            $table->string('actor_role'); // admin, dirut, validator, superadmin
            $table->string('action'); // kirim_sk, setuju, revisi, tanda_tangan, sahkan_tanpa_ttd, dll
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->text('description'); // kalimat narasi lengkap
            $table->json('meta')->nullable(); // data tambahan (target validator, alasan revisi, dll)
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('activity_logs');
    }
};