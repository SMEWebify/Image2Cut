<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

use App\Http\Controllers\MetalCuttingController;

Route::get('/metal-cutting', [MetalCuttingController::class, 'index'])->name('metal-cutting.index');
Route::post('/metal-cutting', [MetalCuttingController::class, 'process'])->name('metal-cutting.process');
