<?php

use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;

Route::get('/',[ProductController::class,'index']);
Route::post('/checkout',[ProductController::class,'checkout'])->name('checkout');