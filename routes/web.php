<?php

use Illuminate\Support\Facades\Route;

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
    Route::inertia('transactions', 'transactions/Index')->name('transactions');
    Route::inertia('reports', 'reports/Index')->name('reports');
    Route::inertia('chat', 'chat/Index')->name('chat');
    Route::inertia('categories', 'categories/Index')->name('categories');
});

require __DIR__.'/settings.php';
