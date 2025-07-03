<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\sampleRekapController;

Route::group(
    ['prefix' => 'rekapitulasi-order'],
    function () {
        Route::controller(sampleRekapController::class)->group(function () {
            Route::get('/', 'index')->name('rekapitulasi-order');
        });
    }
);
