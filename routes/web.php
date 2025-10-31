<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookController;

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

// Incluir rutas del admin
require __DIR__.'/admin.php';

require __DIR__.'/settings.php';