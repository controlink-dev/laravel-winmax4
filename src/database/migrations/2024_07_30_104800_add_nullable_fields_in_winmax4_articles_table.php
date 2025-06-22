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
        Schema::table('winmax4_articles', function (Blueprint $table) {
            //I want add property nullable to all fields
            $table->string('designation')->nullable()->change();
            $table->string('short_description')->nullable()->change();
            $table->string('sub_family_code')->nullable()->change();
            $table->string('sub_sub_family_code')->nullable()->change();
            $table->string('sub_sub_sub_family_code')->nullable()->change();
            $table->string('stock_unit_code')->nullable()->change();
            $table->string('image_url')->nullable()->change();
            $table->json('extras')->nullable()->change();
            $table->json('holds')->nullable()->change();
            $table->json('descriptives')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('winmax4_articles', function (Blueprint $table) {
            $table->dropColumn('designation');
            $table->dropColumn('short_description');
            $table->dropColumn('sub_family_code');
            $table->dropColumn('sub_sub_family_code');
            $table->dropColumn('sub_sub_sub_family_code');
            $table->dropColumn('stock_unit_code');
            $table->dropColumn('image_url');
            $table->dropColumn('extras');
            $table->dropColumn('holds');
            $table->dropColumn('descriptives');
        });
    }
};
