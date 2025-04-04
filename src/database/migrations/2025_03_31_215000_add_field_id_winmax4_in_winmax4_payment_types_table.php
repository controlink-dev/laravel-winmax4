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
        Schema::table('winmax4_payment_types', function (Blueprint $table) {
            $table->integer('id_winmax4');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('winmax4_payment_types', function (Blueprint $table) {
            $table->dropColumn('id_winmax4');
        });
    }
};
