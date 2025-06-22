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
            if(config('winmax4.use_soft_deletes')){
                $table->softDeletes();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('winmax4_articles', function (Blueprint $table) {
            if(config('winmax4.use_soft_deletes')){
                $table->dropSoftDeletes();
            }
        });
    }
};
