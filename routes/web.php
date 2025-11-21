<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\UserReservationController;
use App\Http\Controllers\DownloadController;

// Ruta principal que redirige según autenticación
Route::get('/', function () {
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// NUEVA RUTA: Home que siempre muestra el welcome
Route::get('/welcome', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
        'isAuthenticated' => auth()->check(),
    ]);
})->name('welcome.page');

// Rutas de Páginas Legales
Route::get('/terms', function () {
    return Inertia::render('legal/Terms', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('terms');

Route::get('/privacy', function () {
    return Inertia::render('legal/Privacy', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('privacy');

Route::get('/cookies', function () {
    return Inertia::render('legal/Cookies', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('cookies');

Route::get('/usage-policies', function () {
    return Inertia::render('legal/UsagePolicies', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('usage-policies');

// Dashboard - solo para usuarios autenticados
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

// ============================================
// RUTAS DE CATÁLOGO DE LIBROS (PÚBLICAS)
// ============================================

// Catálogo de libros - acceso público
Route::get('/books', [BookController::class, 'index'])
    ->name('books.index');

// Detalle de un libro - acceso público
Route::get('/books/{book}', [BookController::class, 'show'])
    ->name('books.show');

// Búsqueda de libros - requiere autenticación
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/search', [BookController::class, 'search'])
        ->name('books.search');
});

// ============================================
// RUTAS DE CARRITO DE PRÉSTAMOS
// ============================================

Route::middleware(['auth', 'verified'])->group(function () {
    // Ver carrito
    Route::get('/cart', [CartController::class, 'index'])
        ->name('cart.index');
    
    // Agregar libro al carrito
    Route::post('/cart/add/{book}', [CartController::class, 'add'])
        ->name('cart.add');
    
    // Remover libro del carrito
    Route::delete('/cart/remove/{book}', [CartController::class, 'remove'])
        ->name('cart.remove');
    
    // Obtener items del carrito
    Route::get('/cart/items', [CartController::class, 'getItems'])
        ->name('cart.items');
    
    // Proceso de checkout
    Route::post('/cart/checkout', [CartController::class, 'checkout'])
        ->name('cart.checkout');
    
    // Limpiar carrito
    Route::delete('/cart/clear', [CartController::class, 'clear'])
        ->name('cart.clear');
    
    // ============================================
    // RUTAS DE PRÉSTAMOS
    // ============================================
    
    // Ver mis préstamos
    Route::get('/loans', [\App\Http\Controllers\LoanController::class, 'index'])
        ->name('loans.index');
    
    // Solicitar renovación de préstamo (opcional)
    Route::post('/loans/{loan}/renew', [\App\Http\Controllers\LoanController::class, 'requestRenewal'])
        ->name('loans.renew');
    
    // ============================================
    // RUTAS DE RESERVAS DE LIBROS
    // ============================================
    
    // Ver mis reservas (Panel del usuario)
    Route::get('/reservations', [UserReservationController::class, 'index'])
        ->name('reservations.index');
    
    // Crear nueva reserva
    Route::post('/reservations', [ReservationController::class, 'store'])
        ->name('reservations.store');
    
    // Cancelar reserva
    Route::delete('/reservations/{reservation}', [UserReservationController::class, 'destroy'])
        ->name('reservations.destroy');
    
    // ============================================
    // RUTAS DE DESCARGAS DE LIBROS
    // ============================================
    
    // Registrar y descargar PDF
    Route::post('/downloads', [DownloadController::class, 'download'])
        ->name('downloads.register');
    
    // Stream directo de PDF (alternativo)
    Route::get('/downloads/{book}/stream', [DownloadController::class, 'stream'])
        ->name('downloads.stream');
});

// ============================================

// Incluir rutas del admin
require __DIR__.'/admin.php';

require __DIR__.'/settings.php';
