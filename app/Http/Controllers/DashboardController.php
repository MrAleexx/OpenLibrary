<?php
// app/Http/Controllers/DashboardController.php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $user = auth()->user();
        
        // DEBUG detallado
        \Log::info('=== DASHBOARD DEBUG ===', [
            'user_id' => $user->id,
            'email' => $user->email,
            'roles' => $user->getRoleNames()->toArray(),
            'is_admin' => $user->hasRole('admin'),
            'is_librarian' => $user->hasRole('librarian'),
            'is_user' => $user->hasRole('user'),
            'email_verified' => $user->hasVerifiedEmail(),
        ]);

        if (!$user->hasVerifiedEmail()) {
            \Log::info('Rendering: user/Dashboard (not verified)');
            return Inertia::render('user/Dashboard', [
                'emailVerified' => false,
                'user' => $user,
                'recentDownloads' => [], 
                'activeLoans' => [],
            ]);
        }
        
        if ($user->hasRole('admin') || $user->hasRole('librarian')) {
            \Log::info('Rendering: admin/Dashboard (admin/librarian)');
            return Inertia::render('admin/Dashboard', [
                'stats' => $this->getAdminStats(),
                'userRole' => $user->getRoleNames()->first(),
                'emailVerified' => true,
            ]);
        }

        \Log::info('Rendering: user/Dashboard (regular user)');
        return Inertia::render('user/Dashboard', [
            'recentDownloads' => $user->downloads()->with('book')->latest()->take(5)->get(),
            'activeLoans' => $user->loans()->with('physicalCopy.book')->where('status', 'active')->get(),
            'emailVerified' => true,
        ]);
    }

    private function getAdminStats(): array
    {
        return [
            'total_books' => \App\Models\Book::count(),
            'total_users' => \App\Models\User::count(),
            'total_downloads' => \App\Models\UserDownload::count(),
            'active_loans' => \App\Models\BookLoan::where('status', 'active')->count(),
            'pending_reservations' => \App\Models\BookReservation::where('status', 'pending')->count(),
        ];
    }
}
