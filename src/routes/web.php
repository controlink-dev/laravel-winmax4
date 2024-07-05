<?php

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->prefix('winmax4')->group(function () {
    Route::get('/getWinmax4Settings', [Winmax4Controller::class, 'getWinmax4Settings'])->name('winmax4.getWinmax4Settings');
    Route::post('/generateToken', [Winmax4Controller::class, 'generateToken'])->name('winmax4.generateToken');

    Route::get('/getCurrencies', [Winmax4Controller::class, 'getCurrencies'])->name('winmax4.getCurrencies');
    Route::get('/getDocumentTypes', [Winmax4Controller::class, 'getDocumentTypes'])->name('winmax4.getDocumentTypes');

    Route::get('/getFamilies', [Winmax4Controller::class, 'getFamilies'])->name('winmax4.getFamilies');
    Route::get('/getSubFamilies/{family_id}', [Winmax4Controller::class, 'getSubFamilies'])->name('winmax4.getSubFamilies');
    Route::get('/getSubSubFamilies/{sub_family_id}', [Winmax4Controller::class, 'getSubSubFamilies'])->name('winmax4.getSubSubFamilies');

    Route::get('/getTaxes', [Winmax4Controller::class, 'getTaxes'])->name('winmax4.getTaxes');
    Route::get('/getArticles', [Winmax4Controller::class, 'getArticles'])->name('winmax4.getArticles');
    Route::get('/getEntities', [Winmax4Controller::class, 'getEntities'])->name('winmax4.getEntities');
});