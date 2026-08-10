<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE document_approvals MODIFY status ENUM('MENUNGGU', 'TERIMA', 'PENDING', 'TOLAK', 'DIBATALKAN') DEFAULT 'MENUNGGU'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE document_approvals MODIFY status ENUM('MENUNGGU', 'TERIMA', 'PENDING', 'TOLAK') DEFAULT 'MENUNGGU'");
    }
};