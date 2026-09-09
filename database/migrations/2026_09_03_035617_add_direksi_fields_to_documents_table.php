<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->boolean('need_direksi')->default(true)->after('status');
            $table->foreignId('dirut_id')->nullable()->after('need_direksi')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('documents', function (Blueprint $table) {
            $table->dropForeign(['dirut_id']);
            $table->dropColumn(['need_direksi', 'dirut_id']);
        });
    }
};