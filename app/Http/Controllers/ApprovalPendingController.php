<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

class ApprovalPendingController extends Controller
{
    /**
     * Show the approval pending screen.
     */
    public function show()
    {
        // Si el usuario ya está activo, redirigir al dashboard
        if (Auth::user()->is_active) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('auth/ApprovalPending');
    }
}
