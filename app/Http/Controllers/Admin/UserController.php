<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserDownload;
use App\Models\BookLoan;
use App\Models\BookReservation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Carbon\Carbon;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::with(['roles'])
            ->withCount(['downloads', 'loans', 'reservations']);

        // Búsqueda
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%")
                    ->orWhere('institutional_id', 'like', "%{$search}%");
            });
        }

        // Filtros
        if ($request->has('user_type') && $request->user_type) {
            $query->where('user_type', $request->user_type);
        }

        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            }
        }

        if ($request->has('membership_status') && $request->membership_status) {
            if ($request->membership_status === 'active') {
                $query->where('membership_expires_at', '>', now())
                    ->orWhereNull('membership_expires_at');
            } elseif ($request->membership_status === 'expired') {
                $query->where('membership_expires_at', '<=', now());
            }
        }

        // Ordenamiento
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSortFields = ['name', 'email', 'dni', 'created_at', 'last_login_at'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $users = $query->paginate(10)->withQueryString();

        return Inertia::render('admin/users/Index', [
            'users' => $users,
            'filters' => $request->only(['search', 'user_type', 'status', 'membership_status']),
            'sort' => ['field' => $sortField, 'direction' => $sortDirection],
            'stats' => [
                'total_users' => User::count(),
                'active_users' => User::where('is_active', true)->count(),
                'students' => User::where('user_type', 'student')->count(),
                'teachers' => User::where('user_type', 'teacher')->count(),
                'external_users' => User::where('user_type', 'external')->count(),
                'staff_users' => User::where('user_type', 'staff')->count(),
            ]
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('admin/users/Create', [
            'userTypes' => [
                ['value' => 'student', 'label' => 'Estudiante'],
                ['value' => 'teacher', 'label' => 'Docente'],
                ['value' => 'external', 'label' => 'Externo'],
                ['value' => 'staff', 'label' => 'Staff'],
            ]
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
            'staff' => 'PER'       // Personal
        ];

        return $prefixes[$userType] ?? 'USU'; // Usuario por defecto
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'dni' => 'required|string|max:8|unique:users,dni',
            'phone' => 'required|string|max:9',
            'user_type' => 'required|in:student,teacher,external,staff',
            'institutional_email' => 'nullable|email|unique:users,institutional_email',
            'membership_expires_at' => 'nullable|date|after:today',
            'max_concurrent_loans' => 'required|integer|min:1|max:10',
            'can_download' => 'boolean',
            'is_active' => 'boolean',
        ]);

        try {
            // SIEMPRE generar ID automático
            $institutionalId = $this->generateInstitutionalId($request->user_type);

            // GENERAR CONTRASEÑA TEMPORAL
            $tempPassword = Str::random(12);

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
                ['value' => 'staff', 'label' => 'Staff'],
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
            'user_type' => 'required|in:student,teacher,external,staff',
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

    /**
     * Show import form
     */
    public function import()
    {
        return Inertia::render('admin/users/Import');
    }

    /**
     * Process user import
     */
    public function processImport(Request $request)
    {
        $request->validate([
            'import_file' => 'required|file|mimes:csv,txt,xlsx|max:10240'
        ]);

        // Aquí iría la lógica de importación
        // Por ahora solo simulamos éxito

        return back()->with('success', 'Archivo recibido para procesamiento. La importación comenzará en breve.');
    }

    /**
     * Download import report
     */
    public function downloadImportReport()
    {
        // Lógica para generar y descargar reporte
        return response()->json(['message' => 'Reporte descargado']);
    }

    /**
     * Clear import session
     */
    public function clearImportSession()
    {
        // Lógica para limpiar sesión de importación
        return back()->with('success', 'Sesión de importación limpiada exitosamente.');
    }
}
