<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Alterar FK de license para cascade on delete
        Schema::table('winmax4_payment_types', function (Blueprint $table) {
            $table->dropForeign([config('winmax4.license_column')]);

            $table->foreign(config('winmax4.license_column'))
                ->references('id')
                ->on(config('winmax4.licenses_table'))
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // 1. Reverter FK de license para restrict on delete
        Schema::table('winmax4_payment_types', function (Blueprint $table) {
            $table->dropForeign([config('winmax4.license_column')]);

            $table->foreign(config('winmax4.license_column'))
                ->references('id')
                ->on(config('winmax4.licenses_table'))
                ->restrictOnDelete();
        });
    }
};