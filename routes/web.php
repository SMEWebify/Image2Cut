<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\MetalCuttingController;

Route::get('/welcome', function () {
    return view('welcome');
});
Route::get('/', [MetalCuttingController::class, 'index'])->name('metal-cutting.index');
Route::post('/', [MetalCuttingController::class, 'process'])->name('metal-cutting.process');
