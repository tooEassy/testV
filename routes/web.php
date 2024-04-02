<?php

use Illuminate\Support\Facades\Route;

Route::get('/update', [App\Http\Controllers\MainController::class, 'update'])->name('update');
Route::get('/state', [App\Http\Controllers\MainController::class, 'state'])->name('state');
Route::get('/get-names', [App\Http\Controllers\MainController::class, 'getNames'])->name('getNames');
