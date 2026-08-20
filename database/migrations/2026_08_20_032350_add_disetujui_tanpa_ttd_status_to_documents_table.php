<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('DITINJAU', 'PENDING', 'DITOLAK', 'SIAP_TTD', 'DISETUJUI', 'DISETUJUI_TANPA_TTD') NOT NULL DEFAULT 'DITINJAU'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('DITINJAU', 'PENDING', 'DITOLAK', 'SIAP_TTD', 'DISETUJUI') NOT NULL DEFAULT 'DITINJAU'");
    }
};
