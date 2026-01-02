<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 1) Adicionar colunas *_id
        Schema::table('winmax4_articles', function (Blueprint $table) {
            $table->unsignedBigInteger('family_id')->nullable()->after('family_code');
            $table->unsignedBigInteger('sub_family_id')->nullable()->after('sub_family_code');
            $table->unsignedBigInteger('sub_sub_family_id')->nullable()->after('sub_sub_family_code');

            $table->index('family_id');
            $table->index('sub_family_id');
            $table->index('sub_sub_family_id');
        });

        // 2. Backfill por ordem (importante!)
        // 2.1. Families: (license_id, code)
        DB::statement("
            UPDATE winmax4_articles a
            JOIN winmax4_families f
              ON f.license_id = a.license_id
             AND f.code       = a.family_code
            SET a.family_id = f.id
            WHERE a.family_code IS NOT NULL
              AND a.family_id IS NULL
        ");

        // 2.2. Sub families: (family_id, code)
        DB::statement("
            UPDATE winmax4_articles a
            JOIN winmax4_sub_families sf
              ON sf.family_id = a.family_id
             AND sf.code      = a.sub_family_code
            SET a.sub_family_id = sf.id
            WHERE a.sub_family_code IS NOT NULL
              AND a.sub_family_id IS NULL
              AND a.family_id IS NOT NULL
        ");

        // 2.3. Sub sub families: (sub_family_id, code)
        DB::statement("
            UPDATE winmax4_articles a
            JOIN winmax4_sub_sub_families ssf
              ON ssf.sub_family_id = a.sub_family_id
             AND ssf.code          = a.sub_sub_family_code
            SET a.sub_sub_family_id = ssf.id
            WHERE a.sub_sub_family_code IS NOT NULL
              AND a.sub_sub_family_id IS NULL
              AND a.sub_family_id IS NOT NULL
        ");

        // 3. Criar FKs (agora sim, por ID)
        Schema::table('winmax4_articles', function (Blueprint $table) {
            $table->foreign('family_id')->references('id')->on('winmax4_families')->cascadeOnDelete();
            $table->foreign('sub_family_id')->references('id')->on('winmax4_sub_families')->cascadeOnDelete();
            $table->foreign('sub_sub_family_id')->references('id')->on('winmax4_sub_sub_families')->cascadeOnDelete();
        });

        // 4. (Opcional) Remover colunas de código antigas
        Schema::table('winmax4_articles', function (Blueprint $table) {
            $table->dropColumn([
                'family_code',
                'sub_family_code',
                'sub_sub_family_code',
                'sub_sub_sub_family_code',
            ]);
        });

        // 5. Alterar FK de license para cascade on delete
        Schema::table('winmax4_articles', function (Blueprint $table) {
            $table->dropForeign([config('winmax4.license_column')]);

            $table->foreign(config('winmax4.license_column'))
                ->references('id')
                ->on(config('winmax4.licenses_table'))
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('winmax4_articles', function (Blueprint $table) {
            $table->dropForeign(['family_id']);
            $table->dropForeign(['sub_family_id']);
            $table->dropForeign(['sub_sub_family_id']);

            $table->dropIndex(['family_id']);
            $table->dropIndex(['sub_family_id']);
            $table->dropIndex(['sub_sub_family_id']);

            $table->dropColumn([
                'family_id',
                'sub_family_id',
                'sub_sub_family_id',
            ]);

            $table->foreignId('family_code')->nullable()->after('is_active');
            $table->foreignId('sub_family_code')->nullable()->after('family_code');
            $table->foreignId('sub_sub_family_code')->nullable()->after('sub_family_code');
            $table->foreignId('sub_sub_sub_family_code')->nullable()->after('sub_sub_family_code');
        });
    }
};