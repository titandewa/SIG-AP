<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('signature_placements', function (Blueprint $table) {
            $table->dropForeign(['document_approval_id']);
            $table->unsignedBigInteger('document_approval_id')->nullable()->change();
            $table->foreign('document_approval_id')->references('id')->on('document_approvals')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('signature_placements', function (Blueprint $table) {
            $table->dropForeign(['document_approval_id']);
            $table->unsignedBigInteger('document_approval_id')->nullable(false)->change();
            $table->foreign('document_approval_id')->references('id')->on('document_approvals')->cascadeOnDelete();
        });
    }
};