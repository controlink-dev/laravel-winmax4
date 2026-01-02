<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('winmax4_articles', function (Blueprint $table) {
            // First, drop existing foreign keys
            $table->dropForeign(['family_code']);
            $table->dropForeign(['sub_family_code']);
            $table->dropForeign(['sub_sub_family_code']);
            $table->dropForeign(['sub_sub_sub_family_code']);
            $table->dropForeign(['stock_unit_code']);

            // Drop license foreign key if it exists
            if (config('winmax4.use_license')) {
                $table->dropForeign([config('winmax4.license_column')]);
            }

            // Then, re-add them with cascade on delete
            $table->foreign('family_code')->references('id')->on('winmax4_families')->cascadeOnDelete();
            $table->foreign('sub_family_code')->references('id')->on('winmax4_sub_families')->cascadeOnDelete();
            $table->foreign('sub_sub_family_code')->references('id')->on('winmax4_sub_sub_families')->cascadeOnDelete();
            $table->foreign('sub_sub_sub_family_code')->references('id')->on('winmax4_sub_sub_sub_families')->cascadeOnDelete();
            $table->foreign('stock_unit_code')->references('id')->on('winmax4_stock_units')->cascadeOnDelete();

            // Re-add license foreign key with cascade on delete if it is used
            if (config('winmax4.use_license')) {
                $table->foreign(config('winmax4.license_column'))
                    ->references('id')->on(config('winmax4.licenses_table'))
                    ->cascadeOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('winmax4_articles', function (Blueprint $table) {
            // Drop the foreign keys with cascade on delete
            $table->dropForeign(['family_code']);
            $table->dropForeign(['sub_family_code']);
            $table->dropForeign(['sub_sub_family_code']);
            $table->dropForeign(['sub_sub_sub_family_code']);
            $table->dropForeign(['stock_unit_code']);

            // Drop license foreign key if it exists
            if (config('winmax4.use_license')) {
                $table->dropForeign([config('winmax4.license_column')]);
            }

            // Re-add them without cascade on delete
            $table->foreign('family_code')->references('id')->on('winmax4_families');
            $table->foreign('sub_family_code')->references('id')->on('winmax4_sub_families');
            $table->foreign('sub_sub_family_code')->references('id')->on('winmax4_sub_sub_families');
            $table->foreign('sub_sub_sub_family_code')->references('id')->on('winmax4_sub_sub_sub_families');
            $table->foreign('stock_unit_code')->references('id')->on('winmax4_stock_units');

            // Re-add license foreign key without cascade on delete if it is used
            if (config('winmax4.use_license')) {
                $table->foreign(config('winmax4.license_column'))
                    ->references('id')->on(config('winmax4.licenses_table'));
            }
        });
    }
};

