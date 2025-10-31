<?php
// routes/admin.php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\UserController;
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

    Route::prefix('/admin/books')->name('admin.books.')->group(function () {
        Route::get('/', [BookController::class, 'index'])->name('index');
        Route::get('/create', [BookController::class, 'create'])->name('create');
        Route::post('/', [BookController::class, 'store'])->name('store');
        Route::get('/{book}', [BookController::class, 'show'])->name('show');
        Route::get('/{book}/edit', [BookController::class, 'edit'])->name('edit');
        Route::put('/{book}', [BookController::class, 'update'])->name('update');
        Route::delete('/{book}', [BookController::class, 'destroy'])->name('destroy');
        Route::patch('/{book}/toggle-featured', [BookController::class, 'toggleFeatured'])->name('toggle-featured');
        Route::patch('/{book}/toggle-active', [BookController::class, 'toggleActive'])->name('toggle-active');
    });

    Route::prefix('/admin/users')->name('admin.users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::get('/create', [UserController::class, 'create'])->name('create');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::get('/{user}', [UserController::class, 'show'])->name('show');
        Route::get('/{user}/edit', [UserController::class, 'edit'])->name('edit');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
        Route::patch('/{user}/toggle-active', [UserController::class, 'toggleActive'])->name('toggle-active');
        Route::patch('/{user}/reset-password', [UserController::class, 'resetPassword'])->name('reset-password');
        Route::get('/{user}/download-history', [UserController::class, 'downloadHistory'])->name('download-history');
        Route::get('/{user}/loan-history', [UserController::class, 'loanHistory'])->name('loan-history');
        Route::get('/import', [UserController::class, 'import'])->name('import');
        Route::post('/import', [UserController::class, 'processImport'])->name('import.process');
        Route::get('/import/report', [UserController::class, 'downloadImportReport'])->name('import.report');
        Route::delete('/import/session', [UserController::class, 'clearImportSession'])->name('import.clear-session');
    });
});
