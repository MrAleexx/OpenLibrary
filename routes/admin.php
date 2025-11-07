<?php
// routes/admin.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserImportController;

// ========================================
// Grupo principal: rutas protegidas del panel admin
// ========================================
Route::middleware(['auth', 'verified', 'role:admin|librarian'])->group(function () {
    
    // ===========================
    // DASHBOARD
    // ===========================
    Route::get('/admin/dashboard', [DashboardController::class, 'index'])
        ->name('admin.dashboard');

    // ===========================
    // CATEGORÍAS
    // ===========================
    Route::prefix('/admin/categories')->name('admin.categories.')->group(function () {
        Route::get('/', [CategoryController::class, 'index'])->name('index');
        Route::get('/create', [CategoryController::class, 'create'])->name('create');
        Route::post('/', [CategoryController::class, 'store'])->name('store');
        Route::get('/{category}/edit', [CategoryController::class, 'edit'])->name('edit');
        Route::put('/{category}', [CategoryController::class, 'update'])->name('update');
        Route::delete('/{category}', [CategoryController::class, 'destroy'])->name('destroy');
        Route::patch('/{category}/toggle-status', [CategoryController::class, 'toggleStatus'])->name('toggle-status');

        // Funcionalidades adicionales
        Route::patch('/reorder', [CategoryController::class, 'reorder'])->name('reorder');
        Route::get('/{category}/history', [CategoryController::class, 'history'])->name('history');
    });

    // ===========================
    // LIBROS
    // ===========================
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

    // ===========================
    // IMPORTACIÓN DE USUARIOS
    // ===========================
    Route::prefix('/admin/users/import')->name('admin.users.import.')->group(function () {
        Route::get('/', [UserImportController::class, 'import'])->name('index'); // Vista principal de importación
        Route::get('/template', [UserImportController::class, 'downloadTemplate'])->name('template');
        Route::post('/', [UserImportController::class, 'processImport'])->name('process');
        Route::get('/report', [UserImportController::class, 'downloadImportReport'])->name('report');
        Route::delete('/session', [UserImportController::class, 'clearImportSession'])->name('clear-session');
    });

    // ===========================
    // USUARIOS
    // ===========================
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
    });
});
