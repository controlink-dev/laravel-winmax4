<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Recria a FK com cascade on delete
        Schema::table('winmax4_taxes_rates', function (Blueprint $table) {
            $table->foreign('tax_id')
                ->references('id')
                ->on('winmax4_taxes')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // 1. Reverter FK de license para restrict on delete
        Schema::table('winmax4_taxes_rates', function (Blueprint $table) {
            $table->dropForeign(['tax_id']);
            $table->foreignIdFor(Controlink\LaravelWinmax4\app\Models\Winmax4Article::class, 'tax_id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }
};