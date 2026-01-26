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
            DELETE ast
            FROM winmax4_articles_sale_taxes ast
            LEFT JOIN winmax4_articles a ON a.id = ast.article_id
            WHERE a.id IS NULL
        ");

        // 1. Recria a FK com cascade on delete
        Schema::table('winmax4_articles_sale_taxes', function (Blueprint $table) {
            $table->foreign('article_id')
                ->references('id')
                ->on('winmax4_articles')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // 1. Reverter FK de article para restrict on delete
        Schema::table('winmax4_articles_sale_taxes', function (Blueprint $table) {
            $table->dropForeign(['article_id']);
            $table->foreignIdFor(Controlink\LaravelWinmax4\app\Models\Winmax4Article::class, 'article_id')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }
};