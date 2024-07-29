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
        Schema::create('winmax4_articles', function (Blueprint $table) {
            $table->id();
            $table->string('code');
            $table->string('designation');
            $table->string('short_description');
            $table->boolean('is_active');
            $table->foreignId('family_code');
            $table->foreignId('sub_family_code');
            $table->foreignId('sub_sub_family_code');
            $table->foreignId('sub_sub_sub_family_code');
            $table->foreignId('stock_unit_code');
            $table->longText('image_url');
            $table->longText('extras');
            $table->longText('holds');
            $table->integer('descriptives');

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
        Schema::dropIfExists('winmax4_articles');
    }
};
