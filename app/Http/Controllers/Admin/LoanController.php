<?php

namespace App\Http\Controllers\Admin;

use App\Models\BookLoan;
use App\Models\Book;
use App\Models\BookReservation;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

/**
 * Controlador para gestión administrativa de préstamos
 *
 * Responsabilidades:
 * - Listar y filtrar préstamos del sistema
 * - Gestionar transiciones de estado del flujo de préstamo
 * - Coordinar activación automática de reservas FIFO
 *
 * Flujo de estados:
 * pending_pickup -> ready_for_pickup -> active -> returned
 *
 * @author Sistema de Biblioteca
 * @version 1.0
 */
class LoanController extends Controller
{
    /**
     * Estados válidos para filtrado
     */
    private const VALID_STATUSES = [
        'pending_pickup',
        'ready_for_pickup',
        'active',
        'overdue',
        'returned',
        'cancelled'
    ];

    /**
     * Días por defecto para préstamos
     */
    private const DEFAULT_LOAN_DAYS = 14;

    /**
     * Días para activar reserva tras devolución
     */
    private const RESERVATION_PICKUP_DAYS = 7;

    /**
     * Registros por página
     */
    private const PER_PAGE = 20;

    // ===============================================
    // MÉTODOS PÚBLICOS
    // ===============================================

    /**
     * Mostrar panel de administración de préstamos
     *
     * @param Request $request Petición HTTP con filtros opcionales
     * @return Response Vista Inertia con datos paginados
     */
    public function index(Request $request): Response
    {
        $filters = $this->extractFilters($request);

        $loans = $this->buildLoansQuery($filters)
            ->orderByRaw($this->getStatusPriorityOrder())
            ->orderBy('loan_date', 'desc')
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $this->enrichLoansWithCoverUrls($loans);

        return Inertia::render('admin/loans/Index', [
            'loans' => $loans,
            'stats' => $this->calculateStats(),
            'filters' => $filters,
        ]);
    }

    /**
     * Marcar préstamo como listo para recoger
     *
     * Transición: pending_pickup -> ready_for_pickup
     *
     * @param int $id ID del préstamo
     * @return RedirectResponse
     */
    public function markAsReadyForPickup(int $id): RedirectResponse
    {
        try {
            $loan = $this->findLoanOrFail($id);

            $this->validateStatusTransition(
                $loan,
                'pending_pickup',
                'Solo se pueden marcar como listos los préstamos pendientes.'
            );

            $loan->update(['status' => 'ready_for_pickup']);

            $this->logAction('Préstamo marcado como listo para recoger', $loan);

            // TODO: Enviar notificación al usuario

            return $this->redirectWithSuccess('Préstamo marcado como listo para recoger.');
        } catch (\Exception $e) {
            return $this->handleError($e, $id, 'marcar préstamo como listo');
        }
    }

    /**
     * Confirmar entrega física del libro al usuario
     *
     * Transición: ready_for_pickup -> active
     *
     * @param int $id ID del préstamo
     * @return RedirectResponse
     */
    public function confirmHandover(int $id): RedirectResponse
    {
        try {
            $loan = $this->findLoanOrFail($id);

            $this->validateStatusTransition(
                $loan,
                'ready_for_pickup',
                'Solo se pueden confirmar préstamos listos para recoger.'
            );

            $dueDate = now()->addDays(self::DEFAULT_LOAN_DAYS);

            $loan->update([
                'status' => 'active',
                'loan_date' => now(),
                'due_date' => $dueDate,
            ]);

            $this->logAction('Préstamo activado tras entrega', $loan);

            return $this->redirectWithSuccess(
                "Préstamo confirmado. El usuario tiene hasta {$dueDate->format('d/m/Y')} para devolver."
            );
        } catch (\Exception $e) {
            return $this->handleError($e, $id, 'confirmar entrega');
        }
    }

    /**
     * Marcar préstamo como devuelto
     *
     * Transición: active|overdue -> returned
     *
     * Responsabilidades:
     * 1. Actualizar estado del préstamo
     * 2. Restaurar disponibilidad de la copia física
     * 3. Incrementar contador de copias disponibles
     * 4. Activar siguiente reserva FIFO si existe
     *
     * @param int $id ID del préstamo
     * @return RedirectResponse
     */
    public function markAsReturned(int $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $loan = $this->findLoanOrFail($id);

            $this->validateStatusTransition(
                $loan,
                ['active', 'overdue'],
                'Este préstamo no puede ser marcado como devuelto en su estado actual.'
            );

            // Actualizar préstamo
            $loan->update([
                'status' => 'returned',
                'actual_return_date' => now(),
            ]);

            // Restaurar disponibilidad
            $book = $loan->physicalCopy->book;
            $this->restoreBookAvailability($loan, $book);

            // Activar siguiente reserva FIFO
            $activatedReservation = $this->activateNextPendingReservation($book);

            DB::commit();

            $this->logAction('Préstamo marcado como devuelto', $loan, [
                'book_id' => $book->id,
                'reservation_activated' => $activatedReservation !== null,
            ]);

            return $this->redirectWithSuccess(
                $this->buildReturnMessage($activatedReservation)
            );
        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleError($e, $id, 'procesar la devolución');
        }
    }

    // ===============================================
    // MÉTODOS PRIVADOS - QUERIES
    // ===============================================

    /**
     * Construir query base de préstamos con filtros
     *
     * @param array $filters Filtros extraídos del request
     * @return Builder Query builder configurado
     */
    private function buildLoansQuery(array $filters): Builder
    {
        $query = BookLoan::with($this->getEagerLoadRelations());

        if ($this->hasValidStatus($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $this->applySearchFilter($query, $filters['search']);
        }

        if (!empty($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        return $query;
    }

    /**
     * Obtener relaciones para eager loading
     *
     * @return array Configuración de relaciones
     */
    private function getEagerLoadRelations(): array
    {
        return [
            'user' => fn($query) => $query->select('id', 'name', 'last_name', 'email', 'dni'),
            'physicalCopy' => fn($query) => $query
                ->select('id', 'book_id', 'copy_number', 'status')
                ->with(['book' => fn($q) => $q->select('id', 'title', 'cover_image')]),
        ];
    }

    /**
     * Aplicar filtro de búsqueda
     *
     * @param Builder $query Query builder
     * @param string $search Término de búsqueda
     * @return void
     */
    private function applySearchFilter(Builder $query, string $search): void
    {
        $query->where(function ($q) use ($search) {
            $q->whereHas('user', function ($userQuery) use ($search) {
                $userQuery->where('name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('dni', 'like', "%{$search}%");
            })
                ->orWhereHas('physicalCopy.book', function ($bookQuery) use ($search) {
                    $bookQuery->where('title', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Obtener orden de prioridad por estado
     *
     * @return string SQL para ordenamiento
     */
    private function getStatusPriorityOrder(): string
    {
        return "CASE status
            WHEN 'overdue' THEN 1
            WHEN 'active' THEN 2
            WHEN 'ready_for_pickup' THEN 3
            WHEN 'pending_pickup' THEN 4
            WHEN 'returned' THEN 5
            ELSE 6
        END";
    }

    // ===============================================
    // MÉTODOS PRIVADOS - LÓGICA DE NEGOCIO
    // ===============================================

    /**
     * Restaurar disponibilidad del libro tras devolución
     *
     * @param BookLoan $loan Préstamo devuelto
     * @param Book $book Libro asociado
     * @return void
     */
    private function restoreBookAvailability(BookLoan $loan, Book $book): void
    {
        $loan->physicalCopy->update(['status' => 'available']);
        $book->increment('available_physical_copies');
    }

    /**
     * Activar siguiente reserva pendiente (FIFO) - Notificar usuario
     *
     * Cuando se devuelve un libro, el sistema automáticamente:
     * 1. Busca la reserva más antigua en estado 'pending'
     * 2. La marca como 'ready' (libro apartado)
     * 3. Establece fecha límite para recoger
     * 4. Notifica al usuario (TODO: email)
     * 5. Decrementa copias disponibles (apartada para ese usuario)
     *
     * El usuario tiene X días para llegar a recoger el libro.
     * Cuando llegue, el admin hace clic en "Registrar Entrega".
     *
     * @param Book $book Libro del préstamo devuelto
     * @return BookReservation|null Reserva activada o null
     */
    private function activateNextPendingReservation(Book $book): ?BookReservation
    {
        $pendingReservation = BookReservation::where('book_id', $book->id)
            ->where('status', 'pending')
            ->orderBy('reservation_date', 'asc')
            ->first();

        if (!$pendingReservation) {
            return null;
        }

        $pendingReservation->update([
            'status' => BookReservation::STATUS_READY,
            'pickup_deadline' => now()->addDays(self::RESERVATION_PICKUP_DAYS),
            'notes' => $this->appendActivationNote($pendingReservation->notes),
        ]);

        // Apartar copia para este usuario específico
        $book->decrement('available_physical_copies');

        $this->logReservationActivation($pendingReservation);

        // TODO: Enviar email notificando que su libro está listo

        return $pendingReservation;
    }

    /**
     * Construir mensaje de devolución
     *
     * @param BookReservation|null $reservation Reserva activada
     * @return string Mensaje para el usuario
     */
    private function buildReturnMessage(?BookReservation $reservation): string
    {
        $message = 'Préstamo marcado como devuelto exitosamente.';

        if ($reservation) {
            $userName = $reservation->user->name . ' ' . $reservation->user->last_name;
            $message .= " El libro ha sido apartado para {$userName}, quien fue notificado para recogerlo.";
        }

        return $message;
    }

    // ===============================================
    // MÉTODOS PRIVADOS - VALIDACIONES
    // ===============================================

    /**
     * Validar transición de estado
     *
     * @param BookLoan $loan Préstamo a validar
     * @param string|array $expectedStatus Estado(s) esperado(s)
     * @param string $errorMessage Mensaje de error
     * @return void
     * @throws \RuntimeException Si el estado no es válido
     */
    private function validateStatusTransition(
        BookLoan $loan,
        string|array $expectedStatus,
        string $errorMessage
    ): void {
        $expected = is_array($expectedStatus) ? $expectedStatus : [$expectedStatus];

        if (!in_array($loan->status, $expected)) {
            throw new \RuntimeException($errorMessage);
        }
    }

    /**
     * Verificar si el estado es válido
     *
     * @param string|null $status Estado a verificar
     * @return bool
     */
    private function hasValidStatus(?string $status): bool
    {
        return $status && in_array($status, self::VALID_STATUSES);
    }

    // ===============================================
    // MÉTODOS PRIVADOS - HELPERS
    // ===============================================

    /**
     * Encontrar préstamo o fallar
     *
     * @param int $id ID del préstamo
     * @return BookLoan
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    private function findLoanOrFail(int $id): BookLoan
    {
        return BookLoan::with(['physicalCopy.book', 'user'])->findOrFail($id);
    }

    /**
     * Extraer filtros del request
     *
     * @param Request $request
     * @return array Filtros normalizados
     */
    private function extractFilters(Request $request): array
    {
        return [
            'status' => $request->input('status'),
            'search' => $request->input('search'),
            'user_id' => $request->input('user_id'),
        ];
    }

    /**
     * Enriquecer préstamos con URLs de portadas
     *
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $loans
     * @return void
     */
    private function enrichLoansWithCoverUrls($loans): void
    {
        $loans->getCollection()->transform(function ($loan) {
            if ($loan->physicalCopy?->book) {
                $loan->physicalCopy->book->cover_url = $this->getCoverUrl(
                    $loan->physicalCopy->book
                );
            }
            return $loan;
        });
    }

    /**
     * Obtener URL de portada
     *
     * @param Book $book Libro
     * @return string|null URL completa o null
     */
    private function getCoverUrl(Book $book): ?string
    {
        if (empty($book->cover_image)) {
            return null;
        }

        if (filter_var($book->cover_image, FILTER_VALIDATE_URL)) {
            return $book->cover_image;
        }

        return asset('storage/' . $book->cover_image);
    }

    /**
     * Agregar nota de activación automática
     *
     * @param string|null $existingNotes Notas existentes
     * @return string Notas actualizadas
     */
    private function appendActivationNote(?string $existingNotes): string
    {
        $newNote = sprintf(
            'Activada automáticamente el %s tras devolución de préstamo',
            now()->format('d/m/Y H:i')
        );

        return empty($existingNotes)
            ? $newNote
            : "{$existingNotes}\n{$newNote}";
    }

    /**
     * Calcular estadísticas de préstamos
     *
     * @return array Estadísticas agregadas
     */
    private function calculateStats(): array
    {
        return [
            'total' => BookLoan::count(),
            'pending_pickup' => BookLoan::where('status', 'pending_pickup')->count(),
            'ready_for_pickup' => BookLoan::where('status', 'ready_for_pickup')->count(),
            'active' => BookLoan::where('status', 'active')->count(),
            'overdue' => BookLoan::where('status', 'overdue')->count(),
            'returned' => BookLoan::where('status', 'returned')->count(),
            'overdue_soon' => BookLoan::where('status', 'active')
                ->where('due_date', '<=', now()->addDays(3))
                ->where('due_date', '>', now())
                ->count(),
        ];
    }

    /**
     * Redirigir con mensaje de éxito
     *
     * @param string $message Mensaje de éxito
     * @return RedirectResponse
     */
    private function redirectWithSuccess(string $message): RedirectResponse
    {
        return redirect()
            ->route('admin.loans.index')
            ->with('success', $message);
    }

    /**
     * Manejar error y redirigir
     *
     * @param \Exception $exception Excepción capturada
     * @param int $loanId ID del préstamo
     * @param string $action Acción que falló
     * @return RedirectResponse
     */
    private function handleError(
        \Exception $exception,
        int $loanId,
        string $action
    ): RedirectResponse {
        Log::error("Error al {$action}", [
            'loan_id' => $loanId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        return redirect()
            ->route('admin.loans.index')
            ->with('error', "Ocurrió un error al {$action}.");
    }

    // ===============================================
    // MÉTODOS PRIVADOS - LOGGING
    // ===============================================

    /**
     * Registrar acción en logs
     *
     * @param string $message Mensaje descriptivo
     * @param BookLoan $loan Préstamo afectado
     * @param array $extraData Datos adicionales
     * @return void
     */
    private function logAction(
        string $message,
        BookLoan $loan,
        array $extraData = []
    ): void {
        Log::info($message, array_merge([
            'loan_id' => $loan->id,
            'user_id' => $loan->user_id,
            'admin_id' => auth::id(),
        ], $extraData));
    }

    /**
     * Registrar activación de reserva
     *
     * @param BookReservation $reservation Reserva activada
     * @return void
     */
    private function logReservationActivation(BookReservation $reservation): void
    {
        Log::info('Reserva activada automáticamente tras devolución', [
            'reservation_id' => $reservation->id,
            'book_id' => $reservation->book_id,
            'user_id' => $reservation->user_id,
            'admin_id' => auth::id(),
        ]);
    }

    // ===============================================
    // GESTIÓN DE ESTADOS DEL FLUJO DE PRÉSTAMO
    // ===============================================

    /**
     * Marcar préstamo como listo para recoger
     *
     * @param BookLoan $loan
     * @param Request $request
     * @return RedirectResponse
     */
    public function markAsReady(BookLoan $loan, Request $request): RedirectResponse
    {
        if (!$loan->isPendingPickup()) {
            return back()->with('error', 'Solo se pueden marcar como listos los préstamos pendientes');
        }

        $request->validate([
            'notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $loan->markAsReadyForPickup($request->notes);

            $this->logAction('Préstamo marcado como listo para recoger', $loan, [
                'book_id' => $loan->physicalCopy->book_id,
                'notes' => $request->notes
            ]);

            DB::commit();

            return back()->with('success', 'Préstamo marcado como listo para recoger');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al marcar préstamo como listo', [
                'loan_id' => $loan->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error al marcar préstamo como listo');
        }
    }

    /**
     * Activar préstamo (entregar libro al usuario)
     *
     * @param BookLoan $loan
     * @param Request $request
     * @return RedirectResponse
     */
    public function activateLoan(BookLoan $loan, Request $request): RedirectResponse
    {
        if (!$loan->canBeActivated()) {
            return back()->with('error', 'Solo se pueden activar préstamos listos para recoger');
        }

        $request->validate([
            'loan_days' => 'nullable|integer|min:1|max:60',
            'notes' => 'nullable|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $loanDays = $request->loan_days ?? self::DEFAULT_LOAN_DAYS;

            $loan->activate($loanDays, $request->notes);

            $this->logAction('Préstamo activado (libro entregado)', $loan, [
                'book_id' => $loan->physicalCopy->book_id,
                'loan_days' => $loanDays,
                'due_date' => $loan->fresh()->due_date
            ]);

            DB::commit();

            return back()->with('success', 'Préstamo activado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al activar préstamo', [
                'loan_id' => $loan->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error al activar préstamo');
        }
    }

    /**
     * Cancelar/rechazar préstamo
     *
     * @param BookLoan $loan
     * @param Request $request
     * @return RedirectResponse
     */
    public function cancelLoan(BookLoan $loan, Request $request): RedirectResponse
    {
        if (!$loan->canBeCancelled()) {
            return back()->with('error', 'Solo se pueden cancelar préstamos pendientes o listos para recoger');
        }

        $request->validate([
            'reason' => 'required|string|max:500'
        ]);

        DB::beginTransaction();
        try {
            $bookId = $loan->physicalCopy->book_id;

            $loan->cancel($request->reason);

            $this->logAction('Préstamo cancelado por administrador', $loan, [
                'book_id' => $bookId,
                'reason' => $request->reason
            ]);

            DB::commit();

            return back()->with('success', 'Préstamo cancelado exitosamente');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al cancelar préstamo', [
                'loan_id' => $loan->id,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Error al cancelar préstamo');
        }
    }
}
