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
        Schema::table('winmax4_entities', function (Blueprint $table) {
            $table->date('birthdate')->nullable();
            $table->text('observations')->nullable();
            $table->boolean('newsletter')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('winmax4_entities', function (Blueprint $table) {
            $table->dropColumn('birthdate');
            $table->dropColumn('observations');
            $table->dropColumn('newsletter');
        });
    }
};
