<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // First, make the foreign key columns nullable
        Schema::table('winmax4_articles', function (Blueprint $table) {
            $table->foreign('family_code')->references('code')->on('winmax4_families')->onDeleteCascade()->change();
            $table->foreign('sub_family_code')->references('code')->on('winmax4_sub_families')->onDeleteCascade()->change();
            $table->foreign('sub_sub_family_code')->references('code')->on('winmax4_sub_sub_families')->onDeleteCascade()->change();
            $table->foreign('sub_sub_sub_family_code')->references('code')->on('winmax4_sub_sub_sub_families')->change();
            $table->foreign('stock_unit_code')->references('code')->on('winmax4_stock_units')->onDeleteCascade()->change();
        });
    }

    public function down(): void
    {
        // Revert the foreign key constraints to their original state
        Schema::table('winmax4_articles', function (Blueprint $table) {

        });
    }
};
