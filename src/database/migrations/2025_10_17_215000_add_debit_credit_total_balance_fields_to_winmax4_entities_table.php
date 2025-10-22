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
            $table->decimal('total_debit', 15, 2)->default(0)->after('zip_code');
            $table->decimal('total_credit', 15, 2)->default(0)->after('total_debit');
            $table->decimal('total_balance', 15, 2)->default(0)->after('total_credit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('winmax4_entities', function (Blueprint $table) {
            $table->dropColumn('total_debit');
            $table->dropColumn('total_credit');
            $table->dropColumn('total_balance');
        });
    }
};
