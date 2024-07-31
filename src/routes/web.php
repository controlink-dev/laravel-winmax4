<?php

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4ArticlesController;
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

    Route::get('/getCurrencies', [Winmax4CurrenciesController::class, 'getCurrencies'])->name('winmax4.getCurrencies');

    Route::get('/getDocumentTypes', [Winmax4DocumentTypesController::class, 'getDocumentTypes'])->name('winmax4.getDocumentTypes');

    Route::get('/getFamilies', [Winmax4FamiliesController::class, 'getFamilies'])->name('winmax4.getFamilies');
    Route::get('/getSubFamilies/{family_code}', [Winmax4FamiliesController::class, 'getSubFamilies'])->name('winmax4.getSubFamilies');
    Route::get('/getSubSubFamilies/{sub_family_code}', [Winmax4FamiliesController::class, 'getSubSubFamilies'])->name('winmax4.getSubSubFamilies');

    Route::get('/getTaxes', [Winmax4TaxesController::class, 'getTaxes'])->name('winmax4.getTaxes');

    Route::get('/getArticles', [Winmax4ArticlesController::class, 'getArticles'])->name('winmax4.getArticles');
    Route::post('/postArticles', [Winmax4ArticlesController::class, 'postArticles'])->name('winmax4.postArticles');
    Route::post('/putArticles/{id}', [Winmax4ArticlesController::class, 'putArticles'])->name('winmax4.putArticles');
    Route::delete('/deleteArticles/{id}', [Winmax4ArticlesController::class, 'deleteArticles'])->name('winmax4.deleteArticles');

    Route::get('/getEntities', [Winmax4EntitiesController::class, 'getEntities'])->name('winmax4.getEntities');
    Route::post('/postEntities', [Winmax4EntitiesController::class, 'postEntities'])->name('winmax4.postEntities');
    Route::post('/putEntities/{id}', [Winmax4EntitiesController::class, 'putEntities'])->name('winmax4.putEntities');
    Route::delete('/deleteEntities/{id}', [Winmax4EntitiesController::class, 'deleteEntities'])->name('winmax4.deleteEntities');

    Route::get('/getWarehouses', [Winmax4WarehousesController::class, 'getWarehouses'])->name('winmax4.getWarehouses');

});