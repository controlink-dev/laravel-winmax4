<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('winmax4_articles', function (Blueprint $table) {

            // First, drop the existing foreign key constraint on license_id
            $table->dropForeign(['license_id']);
            $table->foreign('license_id')
                ->references('id')->on('licenses')
                ->cascadeOnDelete();

            // Now, modify other foreign keys to cascade on delete
            $table->foreign('family_code')->references('id')->on('winmax4_families')->cascadeOnDelete();
            $table->foreign('sub_family_code')->references('id')->on('winmax4_sub_families')->cascadeOnDelete();
            $table->foreign('sub_sub_family_code')->references('id')->on('winmax4_sub_sub_families')->cascadeOnDelete();
            $table->foreign('sub_sub_sub_family_code')->references('id')->on('winmax4_sub_sub_sub_families')->cascadeOnDelete();
            $table->foreign('stock_unit_code')->references('id')->on('winmax4_stock_units')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('winmax4_articles', function (Blueprint $table) {

            $table->dropForeign(['family_code']);
            $table->dropForeign(['sub_family_code']);
            $table->dropForeign(['sub_sub_family_code']);
            $table->dropForeign(['sub_sub_sub_family_code']);
            $table->dropForeign(['stock_unit_code']);

            $table->dropForeign(['license_id']);
            $table->foreign('license_id')->references('id')->on('licenses');
        });
    }
};
