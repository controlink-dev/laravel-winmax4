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
        Schema::create('winmax4_terminals', function (Blueprint $table) {
            $table->id();
            $table->integer('code');
            $table->string('designation');
            $table->boolean('is_active');
            $table->integer('service_zone_code')->nullable();
            $table->integer('price_type')->nullable();
            $table->boolean('ask_entity_info')->default(false);
            $table->integer('pos_default_table_number')->nullable();
            $table->boolean('pos_printer_is_local')->default(false);

            if(config('winmax4.use_license')){
                if(config('winmax4.license_is_uuid')){
                    $table->uuid(config('winmax4.license_column'));
                }else{
                    $table->foreignId(config('winmax4.license_column'));
                }

                $table->foreign(config('winmax4.license_column'))
                    ->references('id')
                    ->on(config('winmax4.licenses_table'))
                    ->cascadeOnDelete();
            }

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winmax4_terminals');
    }
};
