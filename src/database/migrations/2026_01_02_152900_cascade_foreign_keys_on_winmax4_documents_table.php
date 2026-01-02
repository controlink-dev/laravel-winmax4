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
        Schema::table('winmax4_documents', function (Blueprint $table) {
            $table->dropForeign([config('winmax4.license_column')]);

            $table->foreign(config('winmax4.license_column'))
                ->references('id')
                ->on(config('winmax4.licenses_table'))
                ->cascadeOnDelete();
        });

        // 2. Adicionar colunas *_id (mantém os *_code por agora)
        Schema::table('winmax4_documents', function (Blueprint $table) {
            $table->unsignedBigInteger('currency_id')->nullable()->after('currency_code');
            $table->unsignedBigInteger('source_warehouse_id')->nullable()->after('source_warehouse_code');
            $table->unsignedBigInteger('target_warehouse_id')->nullable()->after('target_warehouse_code');

            $table->index('currency_id');
            $table->index('source_warehouse_id');
            $table->index('target_warehouse_id');
        });

        // 3. Backfill currency_id por (license_id, currency_code)
        DB::statement("
            UPDATE winmax4_documents d
            JOIN winmax4_currencies c
              ON c.license_id = d.license_id
             AND c.code       = d.currency_code
            SET d.currency_id = c.id
            WHERE d.currency_code IS NOT NULL
              AND d.currency_id IS NULL
        ");

        // 4. Backfill source_warehouse_id por (license_id, source_warehouse_code)
        DB::statement("
            UPDATE winmax4_documents d
            JOIN winmax4_warehouses w
              ON w.license_id = d.license_id
             AND w.code       = d.source_warehouse_code
            SET d.source_warehouse_id = w.id
            WHERE d.source_warehouse_code IS NOT NULL
              AND d.source_warehouse_id IS NULL
        ");

        // 5. Backfill target_warehouse_id por (license_id, target_warehouse_code)
        DB::statement("
            UPDATE winmax4_documents d
            JOIN winmax4_warehouses w
              ON w.license_id = d.license_id
             AND w.code       = d.target_warehouse_code
            SET d.target_warehouse_id = w.id
            WHERE d.target_warehouse_code IS NOT NULL
              AND d.target_warehouse_id IS NULL
        ");

        // 6. Criar FKs (eu recomendo RESTRICT para moedas/armazéns, mas podes trocar para cascade)
        Schema::table('winmax4_documents', function (Blueprint $table) {
            $table->foreign('currency_id')
                ->references('id')
                ->on('winmax4_currencies')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('source_warehouse_id')
                ->references('id')
                ->on('winmax4_warehouses')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('target_warehouse_id')
                ->references('id')
                ->on('winmax4_warehouses')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();

            // 6.1 Remover colunas *_code (só faz isto se tiveres a certeza que o backfill preencheu tudo)
            $table->dropColumn(['currency_code', 'source_warehouse_code', 'target_warehouse_code']);
        });
    }

    public function down(): void
    {
        // 1. Reverter FK de license para restrict on delete
        Schema::table('winmax4_documents', function (Blueprint $table) {
            $table->dropForeign([config('winmax4.license_column')]);

            $table->foreign(config('winmax4.license_column'))
                ->references('id')
                ->on(config('winmax4.licenses_table'))
                ->restrictOnDelete();

            $table->dropColumn(['currency_id', 'source_warehouse_id', 'target_warehouse_id']);
            $table->string('currency_code')->after('external_identification');
            $table->integer('source_warehouse_code')->nullable()->after('terminal_code');
            $table->integer('target_warehouse_code')->nullable()->after('source_warehouse_code');
        });
    }
};