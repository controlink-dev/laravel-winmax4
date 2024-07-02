<?php

use Controlink\LaravelWinmax4\app\Http\Controllers\Winmax4Controller;
use Illuminate\Support\Facades\Route;

Route::prefix('winmax4')->group(function () {

    Route::post('/authenticate', [Winmax4Controller::class, 'authenticate'])->name('winmax4.authenticate');
});