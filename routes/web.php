<?php
// routes/web.php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;
use App\Http\Controllers\DashboardController;

Route::get('/', function () {
    // Si el usuario está autenticado, redirigir al dashboard
    if (auth()->check()) {
        return redirect('/dashboard');
    }
    
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

// Dashboard - solo para usuarios autenticados
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth']) // Remover 'verified' temporalmente
    ->name('dashboard');

require __DIR__.'/settings.php';