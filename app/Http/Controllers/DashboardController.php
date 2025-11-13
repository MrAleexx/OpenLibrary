<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\UserDownload;
use App\Models\BookLoan;
use App\Models\BookReservation;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador del Dashboard principal
 * 
 * Gestiona la renderización del dashboard según el rol del usuario:
 * - Email no verificado: Dashboard limitado
 * - Admin/Librarian: Dashboard administrativo con estadísticas
 * - Usuario regular: Dashboard con descargas recientes y préstamos activos
 */
class DashboardController extends Controller
{
    /**
     * Límite de descargas recientes a mostrar
     */
    private const RECENT_DOWNLOADS_LIMIT = 5;

    /**
     * Renderizar dashboard según rol del usuario autenticado
     * 
     * @return Response Vista Inertia apropiada
     */
    public function index(): Response
    {
        $user = auth()->user();
        
        if (!$user->hasVerifiedEmail()) {
            return $this->renderUnverifiedDashboard($user);
        }
        
        if ($user->hasRole('admin') || $user->hasRole('librarian')) {
            return $this->renderAdminDashboard($user);
        }

        return $this->renderUserDashboard($user);
    }

    /**
     * Dashboard para usuarios sin email verificado
     * 
     * @param User $user Usuario autenticado
     * @return Response
     */
    private function renderUnverifiedDashboard(User $user): Response
    {
        return Inertia::render('user/Dashboard', [
            'emailVerified' => false,
            'user' => $user,
            'recentDownloads' => [], 
            'activeLoans' => [],
        ]);
    }

    /**
     * Dashboard administrativo para admin/librarian
     * 
     * @param User $user Usuario autenticado
     * @return Response
     */
    private function renderAdminDashboard(User $user): Response
    {
        return Inertia::render('admin/Dashboard', [
            'stats' => $this->getAdminStats(),
            'userRole' => $user->getRoleNames()->first(),
            'emailVerified' => true,
        ]);
    }

    /**
     * Dashboard para usuarios regulares
     * 
     * @param User $user Usuario autenticado
     * @return Response
     */
    private function renderUserDashboard(User $user): Response
    {
        return Inertia::render('user/Dashboard', [
            'recentDownloads' => $this->getRecentDownloads($user),
            'activeLoans' => $this->getActiveLoans($user),
            'emailVerified' => true,
        ]);
    }

    /**
     * Obtener descargas recientes del usuario
     * 
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getRecentDownloads(User $user)
    {
        return $user->downloads()
            ->with('book')
            ->latest()
            ->take(self::RECENT_DOWNLOADS_LIMIT)
            ->get();
    }

    /**
     * Obtener préstamos activos del usuario
     * 
     * @param User $user
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getActiveLoans(User $user)
    {
        return $user->loans()
            ->with('physicalCopy.book')
            ->where('status', BookLoan::STATUS_ACTIVE)
            ->get();
    }

    /**
     * Obtener estadísticas para dashboard administrativo
     * 
     * @return array Estadísticas del sistema
     */
    private function getAdminStats(): array
    {
        return [
            'total_books' => Book::count(),
            'total_users' => User::count(),
            'total_downloads' => UserDownload::count(),
            'active_loans' => BookLoan::where('status', BookLoan::STATUS_ACTIVE)->count(),
            'pending_reservations' => BookReservation::where('status', BookReservation::STATUS_PENDING)->count(),
        ];
    }
}
