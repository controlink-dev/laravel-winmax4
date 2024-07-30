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
        Schema::create('winmax4_articles_sale_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('article_id');
            $table->string('tax_fee_code');
            $table->integer('percentage');
            $table->decimal('fixedAmount');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winmax4_articles_sale_taxes');
    }
};
