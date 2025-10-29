<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\DashboardController;

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

// Incluir rutas del admin
require __DIR__.'/admin.php';

require __DIR__.'/settings.php';