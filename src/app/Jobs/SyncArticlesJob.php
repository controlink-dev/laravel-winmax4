<?php

namespace Controlink\LaravelWinmax4\app\Jobs;

use Controlink\LaravelWinmax4\app\Models\Winmax4Article;
use Controlink\LaravelWinmax4\app\Models\Winmax4ArticlePrices;
use Controlink\LaravelWinmax4\app\Models\Winmax4ArticlePurchaseTaxes;
use Controlink\LaravelWinmax4\app\Models\Winmax4ArticleSaleTaxes;
use Controlink\LaravelWinmax4\app\Models\Winmax4ArticleStocks;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SyncArticlesJob implements ShouldQueue
{
    use Batchable, Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $article;
    protected $license_id;

    /**
     * Create a new job instance.
     */
    public function __construct($article, $license_id = null)
    {
        $this->article = $article;
        $this->license_id = $license_id;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if(config('winmax4.use_license')){
            $article = Winmax4Article::updateOrCreate(
                [
                    'code' => $this->article->Code,
                    config('winmax4.license_column') => $this->license_id,
                ],
                [
                    'id_winmax4' => $this->article->ID,
                    'designation' => $this->article->Designation ?? null,
                    'short_description' => $this->article->ShortDescription ?? null,
                    'is_active' => $this->article->IsActive,
                    'family_code' => $this->article->FamilyCode,
                    'sub_family_code' => $this->article->SubFamilyCode ?? null,
                    'sub_sub_family_code' => $this->article->SubSubFamilyCode ?? null,
                    'sub_sub_sub_family_code' => $this->article->SubSubSubFamilyCode ?? null,
                    'stock_unit_code' => $this->article->StockUnitCode ?? null,
                    'image_url' => $this->article->ImageUrl ?? null,
                    'extras' => $this->article->Extras ?? null,
                    'holds' => $this->article->Holds ?? null,
                    'descriptives' => $this->article->Descriptives ?? null,
                ]
            );
        }else{
            $article = Winmax4Article::updateOrCreate(
                [
                    'code' => $this->article->Code,
                ],
                [
                    'id_winmax4' => $this->article->ID,
                    'designation' => $this->article->Designation ?? null,
                    'short_description' => $this->article->ShortDescription ?? null,
                    'is_active' => $this->article->IsActive,
                    'family_code' => $this->article->FamilyCode,
                    'sub_family_code' => $this->article->SubFamilyCode ?? null,
                    'sub_sub_family_code' => $this->article->SubSubFamilyCode ?? null,
                    'sub_sub_sub_family_code' => $this->article->SubSubSubFamilyCode ?? null,
                    'stock_unit_code' => $this->article->StockUnitCode ?? null,
                    'image_url' => $this->article->ImageUrl ?? null,
                    'extras' => $this->article->Extras ?? null,
                    'holds' => $this->article->Holds ?? null,
                    'descriptives' => $this->article->Descriptives ?? null,
                ]
            );
        }

        if(isset($this->article->SaleTaxes)){
            foreach ($this->article->SaleTaxes as $saleTax) {
                Winmax4ArticleSaleTaxes::updateOrCreate(
                    [
                        'article_id' => $article->id,
                        'tax_fee_code' => $saleTax->TaxFeeCode,
                    ],
                    [
                        'percentage' => $saleTax->Percentage ?? 0,
                        'fixedAmount' => $saleTax->FixedAmount ?? 0,
                    ]
                );
            }
        }

        if(isset($this->article->PurchaseTaxes)){
            foreach ($this->article->PurchaseTaxes as $purchaseTax) {
                Winmax4ArticlePurchaseTaxes::updateOrCreate(
                    [
                        'article_id' => $article->id,
                        'tax_fee_code' => $purchaseTax->TaxFeeCode,
                    ],
                    [
                        'percentage' => $purchaseTax->Percentage ?? 0,
                        'fixedAmount' => $purchaseTax->FixedAmount ?? 0,
                    ]
                );
            }
        }

        if(isset($this->article->Prices)){
            foreach ($this->article->Prices as $price) {
                Winmax4ArticlePrices::updateOrCreate(
                    [
                        'article_id' => $article->id,
                        'currency_code' => $price->CurrencyCode,
                    ],
                    [
                        'sales_price1_without_taxes' => $price->SalesPrice1WithoutTaxes ?? 0,
                        'sales_price1_with_taxes' => $price->SalesPrice1WithTaxes ?? 0,
                        'sales_price2_without_taxes' => $price->SalesPrice2WithoutTaxes ?? 0,
                        'sales_price2_with_taxes' => $price->SalesPrice2WithTaxes ?? 0,
                        'sales_price3_without_taxes' => $price->SalesPrice3WithoutTaxes ?? 0,
                        'sales_price3_with_taxes' => $price->SalesPrice3WithTaxes ?? 0,
                        'sales_price4_without_taxes' => $price->SalesPrice4WithoutTaxes ?? 0,
                        'sales_price4_with_taxes' => $price->SalesPrice4WithTaxes ?? 0,
                        'sales_price5_without_taxes' => $price->SalesPrice5WithoutTaxes ?? 0,
                        'sales_price5_with_taxes' => $price->SalesPrice5WithTaxes ?? 0,
                        'sales_price6_without_taxes' => $price->SalesPrice6WithoutTaxes ?? 0,
                        'sales_price6_with_taxes' => $price->SalesPrice6WithTaxes ?? 0,
                        'sales_price7_without_taxes' => $price->SalesPrice7WithoutTaxes ?? 0,
                        'sales_price7_with_taxes' => $price->SalesPrice7WithTaxes ?? 0,
                        'sales_price8_without_taxes' => $price->SalesPrice8WithoutTaxes ?? 0,
                        'sales_price8_with_taxes' => $price->SalesPrice8WithTaxes ?? 0,
                        'sales_price9_without_taxes' => $price->SalesPrice9WithoutTaxes ?? 0,
                        'sales_price9_with_taxes' => $price->SalesPrice9WithTaxes ?? 0,
                        'sales_price_extra_without_taxes' => $price->SalesPriceExtraWithoutTaxes ?? 0,
                        'sales_price_extra_with_taxes' => $price->SalesPriceExtraWithTaxes ?? 0,
                        'sales_price_hold_without_taxes' => $price->SalesPriceHoldWithoutTaxes ?? 0,
                        'sales_price_hold_with_taxes' => $price->SalesPriceHoldWithTaxes ?? 0,
                    ]
                );
            }
        }

        if(isset($this->article->Stocks)){
            foreach ($this->article->Stocks as $stock) {
                Winmax4ArticleStocks::updateOrCreate(
                    [
                        'article_id' => $article->id,
                        'warehouse_code' => $stock->WarehouseCode,
                    ],
                    [
                        'current' => $stock->Current ?? 0,
                    ]
                );
            }
        }
    }
}
