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
        Schema::create('winmax4_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_type_id')->constrained('winmax4_document_types')->onDelete('cascade');
            $table->string('document_number');
            $table->string('serie')->nullable();
            $table->bigInteger('number');
            $table->dateTime('date');
            $table->string('external_identification')->nullable();
            $table->string('currency_code');
            $table->boolean('is_deleted')->default(false);
            $table->string('user_login');
            $table->integer('terminal_code')->nullable();
            $table->integer('source_warehouse_code')->nullable();
            $table->integer('target_warehouse_code')->nullable();
            $table->foreignId('entity_id')->constrained('winmax4_entities')->onDelete('cascade');
            $table->decimal('total_without_taxes', 15, 2);
            $table->decimal('total_applied_taxes', 15, 2);
            $table->decimal('total_with_taxes', 15, 2);
            $table->decimal('total_liquidated', 15, 2)->nullable();
            $table->string('load_address')->nullable();
            $table->string('load_location')->nullable();
            $table->string('load_zip_code')->nullable();
            $table->dateTime('load_date_time')->nullable();
            $table->string('load_vehicle_license_plate')->nullable();
            $table->string('load_country_code')->nullable();
            $table->string('unload_address')->nullable();
            $table->string('unload_location')->nullable();
            $table->string('unload_zip_code')->nullable();
            $table->dateTime('unload_date_time')->nullable();
            $table->string('unload_country_code')->nullable();
            $table->string('hash_characters')->nullable();
            $table->string('ta_doc_code_id')->nullable();
            $table->string('atcud')->nullable();
            $table->integer('table_number')->nullable();
            $table->integer('table_split_number')->nullable();
            $table->string('sales_person_code')->nullable();
            $table->string('remarks')->nullable();
            $table->string('url')->nullable();

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

        Schema::create('winmax4_document_details', function (Blueprint $table){
            $table->id();
            $table->foreignId('document_id')->constrained('winmax4_documents')->onDelete('cascade');
            $table->foreignId('article_id')->constrained('winmax4_articles')->onDelete('cascade');
            $table->decimal('unitary_price_without_taxes', 15, 2);
            $table->decimal('unitary_price_with_taxes', 15, 2);
            $table->integer('discount_percentage_1')->default(0);
            $table->integer('discount_percentage_2')->default(0);
            $table->double('quantity', 15, 2);
            $table->decimal('total_without_taxes', 15, 2);
            $table->decimal('total_with_taxes', 15, 2);
            $table->string('remarks')->nullable();
            $table->foreignId('tax_id')->nullable()->constrained('winmax4_taxes')->onDelete('cascade');
            $table->foreignId('tax_rate_id')->nullable()->constrained('winmax4_taxes_rates')->onDelete('cascade');
            $table->timestamps();
        });

        Schema::create('winmax4_document_taxes', function (Blueprint $table){
            $table->id();
            $table->foreignId('document_id')->constrained('winmax4_documents')->onDelete('cascade');
            $table->string('tax_fee_code')->nullable();
            $table->integer('percentage')->nullable();
            $table->decimal('fixedAmount')->nullable();
            $table->decimal('total_affected')->nullable();
            $table->decimal('total')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winmax4_document_taxes');
        Schema::dropIfExists('winmax4_documents');
        Schema::dropIfExists('winmax4_document_details');
    }
};
