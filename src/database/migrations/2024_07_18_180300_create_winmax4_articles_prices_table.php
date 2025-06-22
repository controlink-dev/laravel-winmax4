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
        Schema::create('winmax4_articles_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Controlink\LaravelWinmax4\app\Models\Winmax4Article::class, 'article_id')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('currency_code', 10);
            $table->decimal('sales_price1_without_taxes', 10, 2);
            $table->decimal('sales_price1_with_taxes', 10, 2);
            $table->decimal('sales_price2_without_taxes', 10, 2);
            $table->decimal('sales_price2_with_taxes', 10, 2);
            $table->decimal('sales_price3_without_taxes', 10, 2);
            $table->decimal('sales_price3_with_taxes', 10, 2);
            $table->decimal('sales_price4_without_taxes', 10, 2);
            $table->decimal('sales_price4_with_taxes', 10, 2);
            $table->decimal('sales_price5_without_taxes', 10, 2);
            $table->decimal('sales_price5_with_taxes', 10, 2);
            $table->decimal('sales_price6_without_taxes', 10, 2);
            $table->decimal('sales_price6_with_taxes', 10, 2);
            $table->decimal('sales_price7_without_taxes', 10, 2);
            $table->decimal('sales_price7_with_taxes', 10, 2);
            $table->decimal('sales_price8_without_taxes', 10, 2);
            $table->decimal('sales_price8_with_taxes', 10, 2);
            $table->decimal('sales_price9_without_taxes', 10, 2);
            $table->decimal('sales_price9_with_taxes', 10, 2);
            $table->decimal('sales_price_extra_without_taxes', 10, 2);
            $table->decimal('sales_price_extra_with_taxes', 10, 2);
            $table->decimal('sales_price_hold_without_taxes', 10, 2);
            $table->decimal('sales_price_hold_with_taxes', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('winmax4_articles_prices');
    }
};
