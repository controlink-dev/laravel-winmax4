<?php

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Illuminate\Support\Facades\Route;

Route::prefix('winmax4')->group(function () {

    Route::post('/generateToken', [Winmax4Controller::class, 'generateToken'])->name('winmax4.generateToken');
});