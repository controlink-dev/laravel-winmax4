<?php

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4ArticlesController;
use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4CompaniesController;
use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4CurrenciesController;
use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4DocumentTypesController;
use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4EntitiesController;
use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4FamiliesController;
use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4TaxesController;
use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4WarehousesController;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->prefix('winmax4')->group(function () {

    Route::get('/getWinmax4Settings', [Winmax4Controller::class, 'getWinmax4Settings'])->name('winmax4.getWinmax4Settings');
    Route::post('/generateToken', [Winmax4Controller::class, 'generateToken'])->name('winmax4.generateToken');
    Route::get('/getWinmax4SyncStatus/{model}', [Winmax4Controller::class, 'getWinmax4SyncStatus'])->name('winmax4.getWinmax4SyncStatus');

    Route::prefix('companies')->group(function () {
        Route::get('/get', [Winmax4CompaniesController::class, 'getCompanies'])->name('winmax4.getCompanies');
    });

    Route::prefix('currencies')->group(function () {
        Route::get('/get', [Winmax4CurrenciesController::class, 'getCurrencies'])->name('winmax4.getCurrencies');
    });

    Route::prefix('documentTypes')->group(function () {
        Route::get('/get', [Winmax4DocumentTypesController::class, 'getDocumentTypes'])->name('winmax4.getDocumentTypes');

    });

    Route::prefix('families')->group(function () {
        Route::get('/get', [Winmax4FamiliesController::class, 'getFamilies'])->name('winmax4.getFamilies');
        Route::get('/getSubFamilies/{family_code}', [Winmax4FamiliesController::class, 'getSubFamilies'])->name('winmax4.getSubFamilies');
        Route::get('/getSubSubFamilies/{sub_family_code}', [Winmax4FamiliesController::class, 'getSubSubFamilies'])->name('winmax4.getSubSubFamilies');
    });

    Route::prefix('taxes')->group(function () {
        Route::get('/get', [Winmax4TaxesController::class, 'getTaxes'])->name('winmax4.taxes.query');
    });

    Route::prefix('articles')->group(function () {
        Route::get('/query', [Winmax4ArticlesController::class, 'getArticles'])->name('winmax4.articles.query');
    });

    Route::prefix('entities')->group(function () {
        Route::get('/query', [Winmax4EntitiesController::class, 'getEntities'])->name('winmax4.entities.query');
        Route::post('/store', [Winmax4EntitiesController::class, 'postEntities'])->name('winmax4.entities.store');
        Route::post('/update/{id}', [Winmax4EntitiesController::class, 'putEntities'])->name('winmax4.entities.update');
        Route::post('/delete/{id}', [Winmax4EntitiesController::class, 'deleteEntities'])->name('winmax4.entities.delete');
    });

    Route::prefix('warehouses')->group(function () {
        Route::get('/get', [Winmax4WarehousesController::class, 'getWarehouses'])->name('winmax4.getWarehouses');
    });
});