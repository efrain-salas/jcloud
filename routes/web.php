<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::middleware(['auth'])->group(function () {
    Route::get('/', [\App\Http\Controllers\AppController::class, 'home'])->name('home');
    Route::get('explorer', [\App\Http\Controllers\AppController::class, 'explorer'])->name('explorer');
    Route::post('upload', [\App\Http\Controllers\AppController::class, 'upload'])->name('upload');
});

require __DIR__.'/auth.php';
