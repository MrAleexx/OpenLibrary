<?php
// routes/admin.php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

// Todas las rutas de admin requieren autenticación, verificación de email Y rol de admin o librarian
Route::middleware(['auth', 'verified', 'role:admin|librarian'])->group(function () {
    // Dashboard Admin (redirige al dashboard general, pero con vista de admin)
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard');

    // Categories Management
    Route::prefix('/admin/categories')->name('admin.categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        Route::patch('/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('toggle-status');
        
        // Nuevas rutas para las funcionalidades agregadas
        Route::patch('/reorder', [CategoryController::class, 'reorder'])->name('reorder');
        Route::get('/{category}/history', [CategoryController::class, 'history'])->name('history');
    });
});
