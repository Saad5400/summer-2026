<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::resource('transactions', TransactionController::class)->only(['index', 'store', 'update', 'destroy']);
    Route::get('reports', [ReportController::class, 'index'])->name('reports');
    Route::inertia('chat', 'chat/Index')->name('chat');
    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);
});

require __DIR__.'/settings.php';
