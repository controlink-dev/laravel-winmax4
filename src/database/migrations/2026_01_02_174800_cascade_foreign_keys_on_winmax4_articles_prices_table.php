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
        Schema::table('winmax4_articles_prices', function (Blueprint $table) {
            $table->foreign('article_id')
                ->references('id')
                ->on('winmax4_articles')
                ->cascadeOnDelete();
        });

        // 2. Adiciona currency_id (mantém currency_code por agora)
        Schema::table('winmax4_articles_prices', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->after('currency_code');
            $table->index('currency_id');
        });

        // 2. Backfill: code -> id
        DB::statement("
            UPDATE winmax4_articles_prices ap
            JOIN winmax4_articles a
              ON a.id = ap.article_id
            JOIN winmax4_currencies c
              ON c.license_id = a.license_id
             AND c.code       = ap.currency_code
            SET ap.currency_id = c.id
            WHERE ap.currency_code IS NOT NULL
              AND ap.currency_id IS NULL
        ");

        // 3. Cria FK para currency_id e remove currency_code
        Schema::table('winmax4_articles_prices', function (Blueprint $table) {
            $table->foreign('currency_id')
                ->references('id')
                ->on('winmax4_currencies')
                ->cascadeOnDelete();

            $table->dropColumn('currency_code');
        });
    }

    public function down(): void
    {
        // 1. Reverter FK de license para restrict on delete
        Schema::table('winmax4_articles_prices', function (Blueprint $table) {
            $table->dropForeign(['article_id']);
            $table->foreignIdFor(Controlink\LaravelWinmax4\app\Models\Winmax4Article::class, 'article_id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->dropColumn('currency_id');
            $table->string('currency_code', 10)->after('article_id');
        });
    }
};