<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('document_approvals', 'role')) {
            Schema::table('document_approvals', function (Blueprint $table) {
                $table->string('role')->default('validator')->after('user_id');
            });
        }

        if (Schema::hasColumn('documents', 'dirut_id')) {
            Schema::table('documents', function (Blueprint $table) {
                $table->dropForeign(['dirut_id']);
                $table->dropColumn('dirut_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('document_approvals', function (Blueprint $table) {
            $table->dropColumn('role');
        });

        Schema::table('documents', function (Blueprint $table) {
            $table->foreignId('dirut_id')->nullable()->constrained('users');
        });
    }
};