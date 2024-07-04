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
        Schema::create('winmax4_sub_sub_families', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_family_id')->constrained('winmax4_sub_families')->onDelete('cascade');
            $table->string('code');
            $table->string('designation');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winmax4_sub_sub_families');
    }
};
