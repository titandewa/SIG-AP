<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('DITINJAU', 'PENDING', 'DITOLAK', 'SIAP_TTD', 'DISETUJUI', 'DISETUJUI_TANPA_TTD', 'DITINJAU_DIREKSI', 'PENDING_DIREKSI') NOT NULL DEFAULT 'DITINJAU'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE documents MODIFY COLUMN status ENUM('DITINJAU', 'PENDING', 'DITOLAK', 'SIAP_TTD', 'DISETUJUI', 'DISETUJUI_TANPA_TTD') NOT NULL DEFAULT 'DITINJAU'");
    }
};