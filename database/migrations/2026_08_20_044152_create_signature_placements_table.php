<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
{
    Schema::create('signature_placements', function (Blueprint $table) {
        $table->id();
        $table->foreignId('document_id')->constrained()->cascadeOnDelete();
        $table->foreignId('document_approval_id')->constrained()->cascadeOnDelete();
        $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // validator yang taruh TTD
        $table->unsignedInteger('page_number');
        $table->float('x');
        $table->float('y');
        $table->float('width');
        $table->float('height');
        $table->string('signature_path'); // snapshot path TTD user saat itu
        $table->timestamps();
    });
}

public function down(): void
{
    Schema::dropIfExists('signature_placements');
}
};
