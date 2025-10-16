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
        //create a winmax4_documents_relation table with document_id and related_document_id columns
        Schema::create('winmax4_documents_relation', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('winmax4_documents')->onDelete('cascade');
            $table->foreignId('related_document_id')->constrained('winmax4_documents')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winmax4_documents_relation');
    }
};
