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
            $table->string('designation')->nullable();
            $table->string('short_description')->nullable();
            $table->foreignId('sub_family_code')->nullable();
            $table->foreignId('sub_sub_family_code')->nullable();
            $table->foreignId('sub_sub_sub_family_code')->nullable();
            $table->foreignId('stock_unit_code')->nullable();
            $table->longText('image_url')->nullable();
            $table->longText('extras')->nullable();
            $table->longText('holds')->nullable();
            $table->integer('descriptives')->nullable();
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
