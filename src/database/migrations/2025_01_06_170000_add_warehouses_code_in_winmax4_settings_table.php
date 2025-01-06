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
        Schema::table('winmax4_settings', function (Blueprint $table) {
            $table->string('warehouse_code')->nullable()->after('n_terminal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('winmax4_settings', function (Blueprint $table) {
            $table->dropColumn('warehouse_code');
        });
    }
};
