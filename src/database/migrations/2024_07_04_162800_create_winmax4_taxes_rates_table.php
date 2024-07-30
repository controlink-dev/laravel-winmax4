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
        Schema::create('winmax4_taxes_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(\Controlink\LaravelWinmax4\app\Models\Winmax4Tax::class, 'tax_id')->constrained()->cascadeOnDelete();
            $table->integer('fixedAmount');
            $table->integer('percentage');

            $table->timestamps();
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
