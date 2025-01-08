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
        Schema::create('winmax4_entities', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('code')->nullable()->unique();
            $table->bigInteger('entity_type')->nullable();
            $table->string('tax_payer_id')->nullable();
            $table->string('address')->nullable();
            $table->string('zip_code')->nullable();
            $table->string('location')->nullable(); // Adicionado para 'locality'
            $table->boolean('is_active')->default(true); // Valor padrão de 1
            $table->string('phone')->nullable();
            $table->string('fax')->nullable();
            $table->string('mobile_phone')->nullable();
            $table->string('email')->nullable();
            $table->string('country_code', 2)->default('PT'); // Valor padrão 'PT'

            if(config('winmax4.use_license')){
                if(config('winmax4.license_is_uuid')){
                    $table->uuid(config('winmax4.license_column'));
                }else{
                    $table->foreignId(config('winmax4.license_column'));
                }

                $table->foreign(config('winmax4.license_column'))->references('id')->on(config('winmax4.licenses_table'));
            }

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winmax4_entities');
    }
};
