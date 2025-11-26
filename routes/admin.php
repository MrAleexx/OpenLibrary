<?php
// routes/admin.php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\BookController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\UserImportController;
use App\Http\Controllers\Admin\LoanController;
use App\Http\Controllers\Admin\ReservationController;

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
        Route::get('/', [UserImportController::class, 'import'])->name('index'); 
        Route::get('/template', [UserImportController::class, 'downloadTemplate'])->name('template');
        Route::post('/', [UserImportController::class, 'processImport'])->name('process');
        Route::get('/report', [UserImportController::class, 'downloadImportReport'])->name('report');
        Route::get('/passwords', [UserImportController::class, 'showTempPasswords'])->name('passwords');
        Route::get('/passwords-report', [UserImportController::class, 'downloadPasswordReport'])->name('passwords.report');
        Route::delete('/session', [UserImportController::class, 'clearImportSession'])->name('clear-session');
    });

    // ===========================
    // USUARIOS
    // ===========================
    Route::prefix('/admin/users')->name('admin.users.')->group(function () {
        // RUTAS PARA CONTRASEÑAS TEMPORALES
        Route::get('/temp-passwords', [UserImportController::class, 'showTempPasswords'])->name('temp-passwords');
        Route::get('/temp-passwords/report', [UserImportController::class, 'downloadPasswordReport'])->name('temp-passwords.report');

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

    // ===========================
    // PRÉSTAMOS (LOANS)
    // ===========================
    Route::prefix('/admin/loans')->name('admin.loans.')->group(function () {
        Route::get('/', [LoanController::class, 'index'])->name('index');
        
        // Gestión de estados del flujo
        Route::post('/{loan}/mark-ready', [LoanController::class, 'markAsReady'])->name('mark-ready');
        Route::post('/{loan}/activate', [LoanController::class, 'activateLoan'])->name('activate');
        Route::post('/{loan}/cancel', [LoanController::class, 'cancelLoan'])->name('cancel');
        Route::post('/{loan}/mark-returned', [LoanController::class, 'markAsReturned'])->name('mark-returned');
        Route::post('/{loan}/verify-return', [LoanController::class, 'verifyReturn'])->name('verify-return');
        Route::post('/{loan}/reject-return', [LoanController::class, 'rejectReturn'])->name('reject-return');
    });

    // ===========================
    // RESERVAS (RESERVATIONS)
    // ===========================
    Route::prefix('/admin/reservations')->name('admin.reservations.')->group(function () {
        Route::get('/', [ReservationController::class, 'index'])->name('index');
        Route::post('/{reservation}/mark-ready', [ReservationController::class, 'markAsReady'])->name('mark-ready');
        Route::post('/{reservation}/convert-to-loan', [ReservationController::class, 'convertToLoan'])->name('convert-to-loan');
        Route::post('/{reservation}/cancel', [ReservationController::class, 'cancel'])->name('cancel');
    });
    // ===========================
    // RECLAMOS
    // ===========================
    Route::prefix('/admin/claims')->name('admin.claims.')->group(function () {
        Route::get('/', [\App\Http\Controllers\ClaimController::class, 'index'])->name('index');
        Route::get('/{claim}', [\App\Http\Controllers\ClaimController::class, 'show'])->name('show');
    });
});
