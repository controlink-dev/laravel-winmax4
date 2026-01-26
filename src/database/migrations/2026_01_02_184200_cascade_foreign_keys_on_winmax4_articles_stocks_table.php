<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        /**
         * 0) Remove linhas órfãs (prices com article_id que não existe em winmax4_articles)
         *    Isso garante que a FK pode ser criada sem falhar.
         */
        DB::statement("
            DELETE astock
              FROM winmax4_articles_stocks astock
             LEFT JOIN winmax4_articles a
               ON a.id = astock.article_id
             WHERE a.id IS NULL
        ");

        // 1. Recria a FK com cascade on delete
        Schema::table('winmax4_articles_stocks', function (Blueprint $table) {
            $table->foreign('article_id')
                ->references('id')
                ->on('winmax4_articles')
                ->cascadeOnDelete();
        });

        // 2. Adiciona currency_id (mantém currency_code por agora)
        Schema::table('winmax4_articles_stocks', function (Blueprint $table) {
            $table->unsignedBigInteger('warehouse_id')->nullable()->after('warehouse_code');
            $table->index('warehouse_id');
        });

        // 2. Backfill: code -> id
        DB::statement("
            UPDATE winmax4_articles_stocks astock
            JOIN winmax4_articles a
              ON a.id = astock.article_id
            JOIN winmax4_warehouses w
              ON w.license_id = a.license_id
             AND w.code       = astock.warehouse_code
            SET astock.warehouse_id = w.id
            WHERE astock.warehouse_code IS NOT NULL
              AND astock.warehouse_id IS NULL
        ");

        // 3. Cria FK para currency_id e remove currency_code
        Schema::table('winmax4_articles_stocks', function (Blueprint $table) {
            $table->foreign('warehouse_id')
                ->references('id')
                ->on('winmax4_warehouses')
                ->cascadeOnDelete();

            $table->dropColumn('warehouse_code');
        });
    }

    public function down(): void
    {
        // 1. Reverter FK de article para restrict on delete
        Schema::table('winmax4_articles_stocks', function (Blueprint $table) {
            $table->dropForeign(['article_id']);
            $table->foreignIdFor(Controlink\LaravelWinmax4\app\Models\Winmax4Article::class, 'article_id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->dropColumn('warehouse_id');
            $table->string('warehouse_code', 10)->after('article_id');
        });
    }
};