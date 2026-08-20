<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('document_approvals', function (Blueprint $table) {
            $table->boolean('need_sign')->default(false)->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('document_approvals', function (Blueprint $table) {
            $table->dropColumn('need_sign');
        });
    }
};
