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
        Schema::create('winmax4_sync_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('model');
            $table->timestamp('last_synced_at')->nullable();

            if(config('winmax4.use_license')){
                if(config('winmax4.license_is_uuid')){
                    $table->uuid(config('winmax4.license_column'));
                }else{
                    $table->foreignId(config('winmax4.license_column'));
                }

                $table->foreign(config('winmax4.license_column'))->references('id')->on(config('winmax4.licenses_table'));
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winmax4_taxes_rates');
    }
};
