<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\MetalCuttingController;

Route::get('/', [WelcomeController::class, 'index'])->name('index');
Route::get('/image-cut', [MetalCuttingController::class, 'index'])->name('image-cut.index');
Route::post('/image-cut', [MetalCuttingController::class, 'process'])->name('image-cut.process');