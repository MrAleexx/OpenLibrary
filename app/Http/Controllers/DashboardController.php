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
            'readingProgress' => [
                'read_this_month' => 0,
                'annual_goal' => 50,
                'annual_progress' => 0
            ],
            'recommendations' => []
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
            'readingProgress' => $this->getUserReadingProgress($user),
            'recommendations' => $this->getUserRecommendations($user),
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
            ->latest('downloaded_at')
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
     * Obtener progreso de lectura del usuario
     */
    private function getUserReadingProgress(User $user): array
    {
        $downloadsThisMonth = $user->downloads()
            ->whereMonth('downloaded_at', now()->month)
            ->whereYear('downloaded_at', now()->year)
            ->count();

        $loansReturnedThisMonth = $user->loans()
            ->where('status', BookLoan::STATUS_RETURNED)
            ->whereMonth('actual_return_date', now()->month)
            ->whereYear('actual_return_date', now()->year)
            ->count();

        $totalReadThisYear = $user->downloads()
            ->whereYear('downloaded_at', now()->year)
            ->count() + 
            $user->loans()
            ->where('status', BookLoan::STATUS_RETURNED)
            ->whereYear('actual_return_date', now()->year)
            ->count();

        return [
            'read_this_month' => $downloadsThisMonth + $loansReturnedThisMonth,
            'annual_goal' => 50, // Podría ser configurable en el futuro
            'annual_progress' => $totalReadThisYear
        ];
    }

    /**
     * Obtener recomendaciones para el usuario
     */
    private function getUserRecommendations(User $user)
    {
        // Obtener categorías de libros descargados o prestados recientemente
        $recentCategories = collect();
        
        $user->downloads()
            ->with('book.categories')
            ->latest('downloaded_at')
            ->take(5)
            ->get()
            ->each(function($download) use ($recentCategories) {
                $recentCategories->push(...$download->book->categories->pluck('id'));
            });

        $user->loans()
            ->with('physicalCopy.book.categories')
            ->latest('loan_date')
            ->take(5)
            ->get()
            ->each(function($loan) use ($recentCategories) {
                $recentCategories->push(...$loan->physicalCopy->book->categories->pluck('id'));
            });

        $categoryIds = $recentCategories->unique()->values();

        if ($categoryIds->isEmpty()) {
            return Book::active()
                ->featured()
                ->take(3)
                ->get();
        }

        return Book::active()
            ->whereHas('categories', function($q) use ($categoryIds) {
                $q->whereIn('categories.id', $categoryIds);
            })
            ->whereDoesntHave('downloads', function($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->inRandomOrder()
            ->take(3)
            ->get();
    }

    /**
     * Obtener estadísticas para dashboard administrativo
     * 
     * @return array Estadísticas del sistema
     */
    private function getAdminStats(): array
    {
        // Totales básicos
        $totalBooks = Book::count();
        $totalUsers = User::count();
        $totalDownloads = UserDownload::count();
        $activeLoans = BookLoan::where('status', BookLoan::STATUS_ACTIVE)->count();
        $pendingReservations = BookReservation::where('status', BookReservation::STATUS_PENDING)->count();

        // Cálculo de tendencias (comparación con mes anterior)
        $trends = [
            'books' => $this->calculateTrend(Book::class),
            'users' => $this->calculateTrend(User::class),
            'downloads' => $this->calculateTrend(UserDownload::class, 'downloaded_at'),
        ];

        // Actividad reciente (mezclada)
        $recentActivity = $this->getRecentActivity();

        // Resumen de biblioteca (Físico vs Digital)
        $libraryOverview = [
            'digital' => Book::whereIn('book_type', ['digital', 'both'])->count(),
            'physical' => Book::whereIn('book_type', ['physical', 'both'])->count(),
        ];

        // Métricas de rendimiento
        // Uso del sistema: % de usuarios activos en los últimos 30 días (que hayan descargado o pedido prestado)
        $activeUserIds = collect();
        $activeUserIds->push(...UserDownload::where('downloaded_at', '>=', now()->subDays(30))->pluck('user_id'));
        $activeUserIds->push(...BookLoan::where('loan_date', '>=', now()->subDays(30))->pluck('user_id'));
        
        $uniqueActiveUsers = $activeUserIds->unique()->count();
        $systemUsage = $totalUsers > 0 ? round(($uniqueActiveUsers / $totalUsers) * 100) : 0;

        return [
            'total_books' => $totalBooks,
            'total_users' => $totalUsers,
            'total_downloads' => $totalDownloads,
            'active_loans' => $activeLoans,
            'pending_reservations' => $pendingReservations,
            'trends' => $trends,
            'recent_activity' => $recentActivity,
            'library_overview' => $libraryOverview,
            'performance_metrics' => [
                'system_usage' => $systemUsage,
            ]
        ];
    }

    /**
     * Calcular tendencia porcentual respecto al mes anterior
     */
    private function calculateTrend($modelClass, $dateColumn = 'created_at'): int
    {
        $currentMonth = $modelClass::whereMonth($dateColumn, now()->month)
            ->whereYear($dateColumn, now()->year)
            ->count();

        $lastMonth = $modelClass::whereMonth($dateColumn, now()->subMonth()->month)
            ->whereYear($dateColumn, now()->subMonth()->year)
            ->count();

        if ($lastMonth == 0) {
            return $currentMonth > 0 ? 100 : 0;
        }

        return round((($currentMonth - $lastMonth) / $lastMonth) * 100);
    }

    /**
     * Obtener actividad reciente combinada
     */
    private function getRecentActivity(): array
    {
        $activities = collect();

        // Nuevos usuarios
        User::latest()
            ->take(5)
            ->get()
            ->each(function($user) use ($activities) {
                $activities->push([
                    'type' => 'user_registered',
                    'title' => 'Usuario registrado',
                    'description' => $user->name,
                    'time' => $user->created_at,
                    'time_ago' => $user->created_at->diffForHumans()
                ]);
            });

        // Nuevas descargas
        UserDownload::with(['user', 'book'])
            ->latest('downloaded_at')
            ->take(5)
            ->get()
            ->each(function($download) use ($activities) {
                $activities->push([
                    'type' => 'download',
                    'title' => 'Nueva descarga',
                    'description' => $download->book->title . ' por ' . $download->user->name,
                    'time' => $download->downloaded_at,
                    'time_ago' => $download->downloaded_at->diffForHumans()
                ]);
            });

        // Nuevos libros
        Book::latest()
            ->take(5)
            ->get()
            ->each(function($book) use ($activities) {
                $activities->push([
                    'type' => 'book_added',
                    'title' => 'Libro agregado',
                    'description' => $book->title,
                    'time' => $book->created_at,
                    'time_ago' => $book->created_at->diffForHumans()
                ]);
            });

        return $activities->sortByDesc('time')->take(5)->values()->all();
    }
}
