<?php

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Illuminate\Support\Facades\Route;

Route::middleware(['web'])->prefix('winmax4')->group(function () {
    Route::get('/getWinmax4Settings', [Winmax4Controller::class, 'getWinmax4Settings'])->name('winmax4.getWinmax4Settings');
    Route::post('/generateToken', [Winmax4Controller::class, 'generateToken'])->name('winmax4.generateToken');
    Route::get('/getCurrencies', [Winmax4Controller::class, 'getCurrencies'])->name('winmax4.getCurrencies');
});