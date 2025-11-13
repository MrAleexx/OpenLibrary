<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDownload;
use App\Models\BookLoan;
use App\Models\BookReservation;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

/**
 * Controlador de administración de usuarios
 * 
 * Gestiona CRUD completo de usuarios del sistema incluyendo:
 * - Listado con filtros y búsqueda
 * - Creación con generación automática de ID institucional
 * - Edición de datos y permisos
 * - Activación/desactivación de cuentas
 * - Visualización de actividad (préstamos, descargas, reservas)
 */
class UserController extends Controller
{
    // ===============================================
    // CONSTANTES
    // ===============================================

    /**
     * Configuración de paginación
     */
    private const USERS_PER_PAGE = 10;
    private const RECENT_ACTIVITY_LIMIT = 10;

    /**
     * Límites de préstamos concurrentes
     */
    private const MIN_CONCURRENT_LOANS = 1;
    private const MAX_CONCURRENT_LOANS = 10;
    private const DEFAULT_CONCURRENT_LOANS = 3;

    /**
     * Configuración de contraseña temporal
     */
    private const TEMP_PASSWORD_LENGTH = 12;

    /**
     * Campos permitidos para ordenamiento
     */
    private const ALLOWED_SORT_FIELDS = [
        'name',
        'email',
        'dni',
        'created_at',
        'last_login_at'
    ];

    /**
     * Ordenamiento por defecto
     */
    private const DEFAULT_SORT_FIELD = 'created_at';
    private const DEFAULT_SORT_DIRECTION = 'desc';

    /**
     * Estados de filtro
     */
    private const STATUS_ACTIVE = 'active';
    private const STATUS_INACTIVE = 'inactive';
    private const MEMBERSHIP_ACTIVE = 'active';
    private const MEMBERSHIP_EXPIRED = 'expired';

    /**
     * Tipos de usuario disponibles
     */
    private const USER_TYPE_STUDENT = 'student';
    private const USER_TYPE_TEACHER = 'teacher';
    private const USER_TYPE_EXTERNAL = 'external';
    private const USER_TYPE_LIBRARIAN = 'librarian';
    private const USER_TYPE_ADMIN = 'admin';

    // ===============================================
    // MÉTODOS PÚBLICOS
    // ===============================================

    /**
     * Listar usuarios con filtros y paginación
     * 
     * @param Request $request Parámetros de filtrado y ordenamiento
     * @return Response Vista Inertia con usuarios paginados
     */
    public function index(Request $request): Response
    {
        $query = $this->buildUsersQuery();

        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);

        $users = $query->paginate(self::USERS_PER_PAGE)->withQueryString();

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => $this->extractFilters($request),
            'sort' => $this->extractSortParams($request),
            'stats' => $this->calculateStats(),
        ]);
    }

    /**
     * Mostrar formulario de creación de usuario
     * 
     * @return Response Vista Inertia con datos para formulario
     */
    public function create(): Response
    {
        return Inertia::render('admin/users/Create', [
            'userTypes' => $this->getUserTypes(),
        ]);
    }

    /**
     * Generar ID institucional automático basado en el tipo de usuario
     */
    private function generateInstitutionalId(string $userType): string
    {
        $prefix = $this->getPrefixForUserType($userType);
        $year = date('y'); // Año en 2 dígitos

        // Buscar el último número para este tipo de usuario y año
        $lastUser = User::where('institutional_id', 'like', "{$prefix}{$year}%")
            ->orderBy('institutional_id', 'desc')
            ->first();

        if ($lastUser && preg_match('/' . $prefix . $year . '(\d+)/', $lastUser->institutional_id, $matches)) {
            $lastNumber = intval($matches[1]);
            $nextNumber = $lastNumber + 1;
        } else {
            $nextNumber = 1;
        }

        // Formatear con ceros a la izquierda (001, 002, etc.)
        $sequential = str_pad($nextNumber, 3, '0', STR_PAD_LEFT);

        return "{$prefix}{$year}{$sequential}";
    }

    /**
     * Obtener prefijo según el tipo de usuario
     */
    private function getPrefixForUserType(string $userType): string
    {
        $prefixes = [
            'admin' => 'ADM',      // Administrador
            'librarian' => 'BIB',  // Bibliotecario
            'student' => 'EST',    // Estudiante
            'teacher' => 'DOC',    // Docente
            'external' => 'EXT',   // Externo
        ];

        return $prefixes[$userType] ?? 'USU'; // Usuario por defecto
    }

    /**
     * Store a newly created resource.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'dni' => 'required|string|max:8|unique:users,dni',
            'phone' => 'required|string|max:9',
            'user_type' => 'required|in:student,teacher,external,librarian,admin',
            'institutional_email' => 'nullable|email|unique:users,institutional_email',
            'membership_expires_at' => 'nullable|date|after:today',
            'max_concurrent_loans' => 'required|integer|min:' . self::MIN_CONCURRENT_LOANS . '|max:' . self::MAX_CONCURRENT_LOANS,
            'can_download' => 'boolean',
            'is_active' => 'boolean',
        ]);

        try {
            // SIEMPRE generar ID automático
            $institutionalId = $this->generateInstitutionalId($request->user_type);

            // GENERAR CONTRASEÑA TEMPORAL
            $tempPassword = Str::random(self::TEMP_PASSWORD_LENGTH);

            $user = User::create([
                'name' => $request->name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'dni' => $request->dni,
                'phone' => $request->phone,
                'user_type' => $request->user_type,
                'institutional_email' => $request->institutional_email,
                'institutional_id' => $institutionalId,
                'membership_expires_at' => $request->membership_expires_at,
                'max_concurrent_loans' => $request->max_concurrent_loans,
                'can_download' => $request->can_download ?? true,
                'is_active' => $request->is_active ?? true,
                'password' => Hash::make($tempPassword),
                'is_temp_password' => true,
                'temp_password_expires_at' => now()->addDays(7),
                'created_by' => Auth::id(),
            ]);

            // REDIRIGIR A CONTRASEÑAS TEMPORALES CON LA CONTRASEÑA
            return redirect()->route('admin.users.temp-passwords')
                ->with('temp_password', $tempPassword)
                ->with('user_created', [
                    'name' => $user->name,
                    'email' => $user->email,
                    'institutional_id' => $user->institutional_id
                ])
                ->with('success', "Usuario {$user->name} creado exitosamente");
        } catch (\Exception $e) {
            return back()->with('error', 'Error al crear el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        $user->load(['roles', 'creator']);

        // Cargar estadísticas y datos relacionados
        $recentDownloads = $user->downloads()
            ->with('book')
            ->orderBy('downloaded_at', 'desc')
            ->limit(10)
            ->get();

        $activeLoans = $user->loans()
            ->with(['physicalCopy.book'])
            ->whereIn('status', ['active', 'overdue'])
            ->orderBy('due_date', 'asc')
            ->get();

        $activeReservations = $user->reservations()
            ->with('book')
            ->whereIn('status', ['pending', 'ready_for_pickup'])
            ->orderBy('reservation_date', 'desc')
            ->get();

        $loanHistory = $user->loans()
            ->with(['physicalCopy.book'])
            ->orderBy('loan_date', 'desc')
            ->limit(20)
            ->get();

        return Inertia::render('admin/users/Show', [
            'user' => $user,
            'recentDownloads' => $recentDownloads,
            'activeLoans' => $activeLoans,
            'activeReservations' => $activeReservations,
            'loanHistory' => $loanHistory,
            'stats' => [
                'total_downloads' => $user->downloads()->count(),
                'total_loans' => $user->loans()->count(),
                'total_reservations' => $user->reservations()->count(),
                'downloads_today' => $user->downloads()
                    ->whereDate('downloaded_at', today())
                    ->count(),
            ]
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $user->load(['roles']);

        return Inertia::render('admin/users/Edit', [
            'user' => $user,
            'userTypes' => [
                ['value' => 'student', 'label' => 'Estudiante'],
                ['value' => 'teacher', 'label' => 'Docente'],
                ['value' => 'external', 'label' => 'Externo'],
                ['value' => 'librarian', 'label' => 'Bibliotecario'],
                ['value' => 'admin', 'label' => 'Administrador'],
            ]
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'dni' => 'required|string|max:8|unique:users,dni,' . $user->id,
            'phone' => 'required|string|max:9',
            'user_type' => 'required|in:student,teacher,external,librarian,admin',
            'institutional_email' => 'nullable|email|unique:users,institutional_email,' . $user->id,
            'institutional_id' => 'nullable|string|max:255',
            'membership_expires_at' => 'nullable|date|after:today',
            'max_concurrent_loans' => 'required|integer|min:1|max:10',
            'can_download' => 'boolean',
            'is_active' => 'boolean',
        ]);

        try {
            $user->update($request->all());

            return redirect()->route('admin.users.show', $user)
                ->with('success', 'Usuario actualizado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        try {
            // Verificar si el usuario tiene préstamos activos
            if ($user->loans()->whereIn('status', ['active', 'overdue'])->exists()) {
                return back()->with('error', 'No se puede eliminar un usuario con préstamos activos.');
            }

            // Eliminar relaciones
            $user->downloads()->delete();
            $user->reservations()->delete();

            // Eliminar préstamos (historial)
            $user->loans()->delete();

            // Eliminar usuario
            $user->delete();

            return redirect()->route('admin.users.index')
                ->with('success', 'Usuario eliminado exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar el usuario: ' . $e->getMessage());
        }
    }

    /**
     * Toggle active status
     */
    public function toggleActive(User $user)
    {
        $user->update(['is_active' => !$user->is_active]);

        $status = $user->is_active ? 'activado' : 'desactivado';
        return back()->with('success', "Usuario {$status} exitosamente.");
    }

    /**
     * Reset user password
     */
    public function resetPassword(User $user)
    {
        try {
            $tempPassword = Str::random(12);

            $user->update([
                'password' => Hash::make($tempPassword),
                'is_temp_password' => true,
                'temp_password_expires_at' => now()->addDays(7),
            ]);

            // REDIRIGIR A CONTRASEÑAS TEMPORALES
            return redirect()->route('admin.users.temp-passwords')
                ->with('temp_password', $tempPassword)
                ->with('user_reset', [
                    'name' => $user->name,
                    'email' => $user->email,
                    'institutional_id' => $user->institutional_id
                ])
                ->with('success', 'Contraseña reseteada exitosamente.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error al resetear la contraseña: ' . $e->getMessage());
        }
    }

    /**
     * Show user download history
     */
    public function downloadHistory(User $user)
    {
        $downloads = $user->downloads()
            ->with('book')
            ->orderBy('downloaded_at', 'desc')
            ->paginate(20);

        return Inertia::render('admin/users/DownloadHistory', [
            'user' => $user,
            'downloads' => $downloads
        ]);
    }

    /**
     * Show user loan history
     */
    public function loanHistory(User $user)
    {
        $loans = $user->loans()
            ->with(['physicalCopy.book'])
            ->orderBy('loan_date', 'desc')
            ->paginate(20);

        return Inertia::render('admin/users/LoanHistory', [
            'user' => $user,
            'loans' => $loans
        ]);
    }

    // ===============================================
    // MÉTODOS PRIVADOS - QUERIES
    // ===============================================

    /**
     * Construir query base para usuarios con relaciones
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildUsersQuery()
    {
        return User::with(['roles'])
            ->withCount(['downloads', 'loans', 'reservations']);
    }

    // ===============================================
    // MÉTODOS PRIVADOS - FILTROS
    // ===============================================

    /**
     * Aplicar filtros a la query de usuarios
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return void
     */
    private function applyFilters($query, Request $request): void
    {
        $this->applySearchFilter($query, $request->input('search'));
        $this->applyUserTypeFilter($query, $request->input('user_type'));
        $this->applyStatusFilter($query, $request->input('status'));
        $this->applyMembershipStatusFilter($query, $request->input('membership_status'));
    }

    /**
     * Aplicar filtro de búsqueda
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $search
     * @return void
     */
    private function applySearchFilter($query, ?string $search): void
    {
        if (empty($search)) {
            return;
        }

        $query->where(function ($q) use ($search) {
            $q->where('name', 'like', "%{$search}%")
                ->orWhere('last_name', 'like', "%{$search}%")
                ->orWhere('email', 'like', "%{$search}%")
                ->orWhere('dni', 'like', "%{$search}%")
                ->orWhere('institutional_id', 'like', "%{$search}%");
        });
    }

    /**
     * Aplicar filtro de tipo de usuario
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $userType
     * @return void
     */
    private function applyUserTypeFilter($query, ?string $userType): void
    {
        if (empty($userType)) {
            return;
        }

        $query->where('user_type', $userType);
    }

    /**
     * Aplicar filtro de estado
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $status
     * @return void
     */
    private function applyStatusFilter($query, ?string $status): void
    {
        if (empty($status)) {
            return;
        }

        if ($status === self::STATUS_ACTIVE) {
            $query->where('is_active', true);
        } elseif ($status === self::STATUS_INACTIVE) {
            $query->where('is_active', false);
        }
    }

    /**
     * Aplicar filtro de estado de membresía
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $membershipStatus
     * @return void
     */
    private function applyMembershipStatusFilter($query, ?string $membershipStatus): void
    {
        if (empty($membershipStatus)) {
            return;
        }

        if ($membershipStatus === self::MEMBERSHIP_ACTIVE) {
            $query->where('membership_expires_at', '>', now())
                ->orWhereNull('membership_expires_at');
        } elseif ($membershipStatus === self::MEMBERSHIP_EXPIRED) {
            $query->where('membership_expires_at', '<=', now());
        }
    }

    /**
     * Aplicar ordenamiento a la query
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return void
     */
    private function applySorting($query, Request $request): void
    {
        $sortField = $request->get('sort_field', self::DEFAULT_SORT_FIELD);
        $sortDirection = $request->get('sort_direction', self::DEFAULT_SORT_DIRECTION);

        if (in_array($sortField, self::ALLOWED_SORT_FIELDS)) {
            $query->orderBy($sortField, $sortDirection);
        }
    }

    // ===============================================
    // MÉTODOS PRIVADOS - HELPERS
    // ===============================================

    /**
     * Extraer filtros del request
     * 
     * @param Request $request
     * @return array
     */
    private function extractFilters(Request $request): array
    {
        return $request->only(['search', 'user_type', 'status', 'membership_status']);
    }

    /**
     * Extraer parámetros de ordenamiento
     * 
     * @param Request $request
     * @return array
     */
    private function extractSortParams(Request $request): array
    {
        return [
            'field' => $request->get('sort_field', self::DEFAULT_SORT_FIELD),
            'direction' => $request->get('sort_direction', self::DEFAULT_SORT_DIRECTION),
        ];
    }

    /**
     * Calcular estadísticas de usuarios
     * 
     * @return array
     */
    private function calculateStats(): array
    {
        return [
            'total_users' => User::count(),
            'active_users' => User::where('is_active', true)->count(),
            'students' => User::where('user_type', self::USER_TYPE_STUDENT)->count(),
            'teachers' => User::where('user_type', self::USER_TYPE_TEACHER)->count(),
            'external_users' => User::where('user_type', self::USER_TYPE_EXTERNAL)->count(),
            'librarian_users' => User::where('user_type', self::USER_TYPE_LIBRARIAN)->count(),
            'admin_users' => User::where('user_type', self::USER_TYPE_ADMIN)->count(),
        ];
    }

    /**
     * Obtener tipos de usuario disponibles
     * 
     * @return array
     */
    private function getUserTypes(): array
    {
        return [
            ['value' => self::USER_TYPE_STUDENT, 'label' => 'Estudiante'],
            ['value' => self::USER_TYPE_TEACHER, 'label' => 'Docente'],
            ['value' => self::USER_TYPE_EXTERNAL, 'label' => 'Externo'],
            ['value' => self::USER_TYPE_LIBRARIAN, 'label' => 'Bibliotecario'],
            ['value' => self::USER_TYPE_ADMIN, 'label' => 'Administrador'],
        ];
    }
}
