<?php

namespace App\Http\Controllers\Admin;

use App\Models\BookReservation;
use App\Models\BookLoan;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Builder;

/**
 * Controlador para gestión administrativa de reservas
 * 
 * Responsabilidades:
 * - Listar y filtrar reservas del sistema con cola FIFO
 * - Activar reservas manualmente (pending → ready)
 * - Convertir reservas a préstamos activos
 * - Gestionar cancelaciones y activación automática
 * 
 * Flujo de estados:
 * pending → ready → collected
 *        ↘ expired / cancelled
 * 
 * @author Sistema de Biblioteca
 * @version 2.0
 */
class ReservationController extends Controller
{
    /**
     * Estados válidos para filtrado
     */
    private const VALID_STATUSES = [
        'pending',
        'ready',
        'collected',
        'expired',
        'cancelled'
    ];

    /**
     * Días para recoger reserva activada
     */
    private const PICKUP_DEADLINE_DAYS = 7;

    /**
     * Días por defecto de préstamo al convertir
     */
    private const DEFAULT_LOAN_DAYS = 14;

    /**
     * Registros por página
     */
    private const PER_PAGE = 20;

    /**
     * Límite de libros populares
     */
    private const POPULAR_BOOKS_LIMIT = 10;

    /**
     * Días para alerta de expiración
     */
    private const EXPIRING_SOON_DAYS = 2;

    // ===============================================
    // MÉTODOS PÚBLICOS
    // ===============================================

    /**
     * Mostrar panel de administración de reservas
     * 
     * Incluye:
     * - Lista paginada con filtros
     * - Cálculo de posición en cola FIFO
     * - Estadísticas agregadas
     * - Libros con más reservas pendientes
     * 
     * @param Request $request Petición HTTP con filtros opcionales
     * @return Response Vista Inertia con datos
     */
    public function index(Request $request): Response
    {
        $filters = $this->extractFilters($request);
        
        $reservations = $this->buildReservationsQuery($filters)
            ->orderByRaw($this->getStatusPriorityOrder())
            ->orderBy('reservation_date', 'asc') // FIFO
            ->paginate(self::PER_PAGE)
            ->withQueryString();

        $this->enrichReservationsWithMetadata($reservations);

        return Inertia::render('admin/reservations/Index', [
            'reservations' => $reservations,
            'stats' => $this->calculateStats(),
            'booksWithPendingReservations' => $this->getBooksWithPendingReservations(),
            'filters' => $filters,
        ]);
    }


    /**
     * Marcar reserva como lista para recoger (MANUAL - Solo casos excepcionales)
     * 
     * Transición: pending → ready
     * 
     * ⚠️ ADVERTENCIA: Este método solo debe usarse en casos excepcionales
     * El sistema normalmente activa reservas automáticamente vía FIFO cuando
     * se devuelve un libro (ver LoanController::activateNextPendingReservation)
     * 
     * Casos de uso válidos:
     * - Error del sistema que no activó automáticamente
     * - Copia física encontrada que no estaba registrada
     * - Override administrativo justificado
     * 
     * Proceso:
     * 1. Validar estado pending
     * 2. Verificar disponibilidad de copias
     * 3. Actualizar reserva con deadline
     * 4. Decrementar copias disponibles (apartada)
     * 5. Registrar acción en logs
     * 
     * @param int $id ID de la reserva
     * @return RedirectResponse
     */
    public function markAsReady(int $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $reservation = $this->findReservationOrFail($id);

            $this->validateStatusTransition(
                $reservation,
                'pending',
                'Solo se pueden activar manualmente reservas en estado pendiente.'
            );

            $this->validateBookAvailability($reservation->book);

            $reservation->update([
                'status' => 'ready',
                'pickup_deadline' => now()->addDays(self::PICKUP_DEADLINE_DAYS),
                'notes' => $this->appendActivationNote($reservation->notes, 'manual_override'),
            ]);

            $reservation->book->decrement('available_physical_copies');

            DB::commit();

            $this->logAction('⚠️ Reserva activada MANUALMENTE (override FIFO)', $reservation, [
                'activation_type' => 'manual_override',
                'warning' => 'Activación manual - verificar justificación',
            ]);

            // TODO: Enviar email al usuario

            return $this->redirectWithSuccess(
                "⚠️ Reserva activada manualmente. El libro está apartado hasta {$reservation->pickup_deadline->format('d/m/Y')}. Verificar que hay copia disponible."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleError($e, $id, 'activar manualmente la reserva');
        }
    }

    /**
     * Registrar entrega física del libro al usuario
     * 
     * Transición: ready → collected (+ crear BookLoan en estado ready_for_pickup)
     * 
     * 📝 FLUJO CORRECTO:
     * 1. Libro fue devuelto → Sistema activa automáticamente reserva FIFO → Estado 'ready'
     * 2. Usuario recibe notificación de que su libro está listo (email/sistema)
     * 3. Usuario llega físicamente a biblioteca para recoger
     * 4. Admin verifica identidad y hace clic en "Registrar Entrega"
     * 5. Se crea préstamo en estado 'ready_for_pickup' (aún no activo)
     * 6. Usuario recibe libro físico en mano
     * 7. Admin activa préstamo → Estado 'active' con fechas de vencimiento
     * 
     * Este método registra el paso 4-5: El usuario ESTÁ PRESENTE y se registra la entrega
     * 
     * Proceso técnico:
     * 1. Validar estado ready (libro apartado, usuario notificado)
     * 2. Buscar copia física disponible
     * 3. Crear BookLoan con estado ready_for_pickup (NO active)
     * 4. Marcar reserva como collected
     * 5. Registrar acción en logs
     * 
     * @param int $id ID de la reserva
     * @return RedirectResponse
     */
    public function convertToLoan(int $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $reservation = $this->findReservationOrFail($id);

            $this->validateStatusTransition(
                $reservation,
                'ready',
                'Solo se pueden registrar entregas de reservas en estado listo (usuario ya notificado).'
            );

            $availableCopy = $this->findAvailablePhysicalCopy($reservation->book);

            $loan = $this->createLoanFromReservation($reservation, $availableCopy);

            $this->markReservationAsCollected($reservation);

            // NO cambiar estado de physical_copy aquí - se hará al activar el préstamo
            // $availableCopy->update(['status' => 'loaned']);

            // NO incrementar total_loans aquí - se hará al activar el préstamo
            // $reservation->book->increment('total_loans');

            DB::commit();

            $userName = $reservation->user->name . ' ' . $reservation->user->last_name;

            $this->logAction('📚 Entrega física registrada - Préstamo creado', $reservation, [
                'loan_id' => $loan->id,
                'status' => 'ready_for_pickup',
                'user_name' => $userName,
            ]);

            return $this->redirectWithSuccess(
                "✅ Entrega registrada exitosamente. {$userName} ha recibido el libro. Ahora debe activar el préstamo en la sección de Préstamos."
            );

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleError($e, $id, 'registrar la entrega del libro');
        }
    }

    /**
     * Cancelar reserva (acción administrativa)
     * 
     * Transiciones permitidas: pending → cancelled, ready → cancelled
     * 
     * 📝 CASOS DE USO:
     * - Usuario solicita cancelación
     * - Usuario no llegó a recoger (expiró el plazo)
     * - Libro perdido/dañado y no hay copias
     * - Override administrativo justificado
     * 
     * Proceso:
     * 1. Validar estado (pending o ready)
     * 2. Actualizar reserva como cancelled
     * 3. Si era ready: restaurar disponibilidad + activar siguiente FIFO
     * 4. Si era pending: simplemente cancelar (no afecta inventario)
     * 5. Registrar acción en logs
     * 
     * @param int $id ID de la reserva
     * @return RedirectResponse
     */
    public function cancel(int $id): RedirectResponse
    {
        try {
            DB::beginTransaction();

            $reservation = $this->findReservationOrFail($id);

            $this->validateStatusTransition(
                $reservation,
                ['pending', 'ready'],
                'Solo se pueden cancelar reservas pendientes o listas.'
            );

            $wasReady = $reservation->status === 'ready';
            $userName = $reservation->user->name . ' ' . $reservation->user->last_name;

            $reservation->update([
                'status' => 'cancelled',
                'cancellation_date' => now(),
                'notes' => $this->appendCancellationNote($reservation->notes),
            ]);

            $message = "❌ Reserva de {$userName} cancelada.";

            if ($wasReady) {
                $this->restoreBookAvailabilityAndActivateNext($reservation);
                $message .= " El libro apartado ha sido liberado. Si había siguiente en cola, fue notificado automáticamente.";
            } else {
                $message .= " La reserva estaba en cola de espera y no afectó el inventario.";
            }

            DB::commit();

            $this->logAction('Reserva cancelada por admin', $reservation, [
                'was_ready' => $wasReady,
                'user_name' => $userName,
            ]);

            return $this->redirectWithSuccess($message);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleError($e, $id, 'cancelar la reserva');
        }
    }


    // ===============================================
    // MÉTODOS PRIVADOS - QUERIES
    // ===============================================

    /**
     * Construir query base de reservas con filtros
     * 
     * @param array $filters Filtros extraídos del request
     * @return Builder Query builder configurado
     */
    private function buildReservationsQuery(array $filters): Builder
    {
        $query = BookReservation::with($this->getEagerLoadRelations());

        if ($this->hasValidStatus($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['search'])) {
            $this->applySearchFilter($query, $filters['search']);
        }

        if (!empty($filters['book_id'])) {
            $this->applyBookFilter($query, $filters['book_id']);
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
            'book' => fn($query) => $query->select('id', 'title', 'cover_image'),
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
            })
            ->orWhereHas('book', function ($bookQuery) use ($search) {
                $bookQuery->where('title', 'like', "%{$search}%");
            });
        });
    }

    /**
     * Aplicar filtro de libro
     * 
     * @param Builder $query Query builder
     * @param int $bookId ID del libro
     * @return void
     */
    private function applyBookFilter(Builder $query, int $bookId): void
    {
        $query->where(function ($q) use ($bookId) {
            $q->where('book_id', $bookId)
              ->orWhereHas('physicalCopy', function ($sq) use ($bookId) {
                  $sq->where('book_id', $bookId);
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
            WHEN 'ready' THEN 1
            WHEN 'pending' THEN 2
            WHEN 'collected' THEN 3
            WHEN 'expired' THEN 4
            WHEN 'cancelled' THEN 5
            ELSE 6
        END";
    }

    // ===============================================
    // MÉTODOS PRIVADOS - LÓGICA DE NEGOCIO
    // ===============================================

    /**
     * Crear préstamo a partir de una reserva
     * 
     * @param BookReservation $reservation Reserva lista
     * @param mixed $physicalCopy Copia física disponible
     * @return BookLoan Préstamo creado
     */
    private function createLoanFromReservation(
        BookReservation $reservation,
        $physicalCopy
    ): BookLoan {
        return BookLoan::create([
            'user_id' => $reservation->user_id,
            'physical_copy_id' => $physicalCopy->id,
            'reservation_id' => $reservation->id,
            'loan_date' => null, // Se establecerá al activar
            'due_date' => null,  // Se establecerá al activar
            'status' => BookLoan::STATUS_READY_FOR_PICKUP,
            'renewal_count' => 0,
            'notes' => 'Convertido desde reserva. Esperando recogida.',
        ]);
    }

    /**
     * Marcar reserva como recolectada
     * 
     * @param BookReservation $reservation Reserva a actualizar
     * @return void
     */
    private function markReservationAsCollected(BookReservation $reservation): void
    {
        $reservation->update([
            'status' => 'collected',
            'pickup_date' => now(),
        ]);
    }

    /**
     * Restaurar disponibilidad y activar siguiente reserva FIFO
     * 
     * @param BookReservation $reservation Reserva cancelada
     * @return void
     */
    private function restoreBookAvailabilityAndActivateNext(
        BookReservation $reservation
    ): void {
        $reservation->book->increment('available_physical_copies');
        $this->activateNextReservation($reservation->book_id);
    }

    /**
     * Activar siguiente reserva pending (FIFO)
     * 
     * @param int $bookId ID del libro
     * @return BookReservation|null Reserva activada o null
     */
    private function activateNextReservation(int $bookId): ?BookReservation
    {
        $nextReservation = BookReservation::where('book_id', $bookId)
            ->where('status', 'pending')
            ->orderBy('reservation_date', 'asc')
            ->first();

        if (!$nextReservation) {
            return null;
        }

        $nextReservation->update([
            'status' => 'ready',
            'pickup_deadline' => now()->addDays(self::PICKUP_DEADLINE_DAYS),
            'notes' => $this->appendActivationNote($nextReservation->notes, 'auto'),
        ]);

        $nextReservation->book->decrement('available_physical_copies');

        $this->logReservationActivation($nextReservation, 'auto');

        // TODO: Enviar email al usuario

        return $nextReservation;
    }

    // ===============================================
    // MÉTODOS PRIVADOS - VALIDACIONES
    // ===============================================

    /**
     * Validar transición de estado
     * 
     * @param BookReservation $reservation Reserva a validar
     * @param string|array $expectedStatus Estado(s) esperado(s)
     * @param string $errorMessage Mensaje de error
     * @return void
     * @throws \RuntimeException Si el estado no es válido
     */
    private function validateStatusTransition(
        BookReservation $reservation,
        string|array $expectedStatus,
        string $errorMessage
    ): void {
        $expected = is_array($expectedStatus) ? $expectedStatus : [$expectedStatus];

        if (!in_array($reservation->status, $expected)) {
            throw new \RuntimeException($errorMessage);
        }
    }

    /**
     * Validar disponibilidad de copias del libro
     * 
     * @param Book $book Libro a verificar
     * @return void
     * @throws \RuntimeException Si no hay copias disponibles
     */
    private function validateBookAvailability(Book $book): void
    {
        if ($book->available_physical_copies < 1) {
            throw new \RuntimeException('No hay copias disponibles de este libro.');
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
     * Encontrar reserva o fallar
     * 
     * @param int $id ID de la reserva
     * @return BookReservation
     * @throws \Illuminate\Database\Eloquent\ModelNotFoundException
     */
    private function findReservationOrFail(int $id): BookReservation
    {
        return BookReservation::with(['book', 'user'])->findOrFail($id);
    }

    /**
     * Encontrar copia física disponible
     * 
     * @param Book $book Libro del que buscar copia
     * @return mixed Copia física disponible
     * @throws \RuntimeException Si no hay copias disponibles
     */
    private function findAvailablePhysicalCopy(Book $book)
    {
        $copy = $book->physicalCopies()
            ->where('status', 'available')
            ->first();

        if (!$copy) {
            throw new \RuntimeException('No hay copias físicas disponibles.');
        }

        return $copy;
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
            'book_id' => $request->input('book_id'),
        ];
    }

    /**
     * Enriquecer reservas con metadata adicional
     * 
     * @param \Illuminate\Contracts\Pagination\LengthAwarePaginator $reservations
     * @return void
     */
    private function enrichReservationsWithMetadata($reservations): void
    {
        $reservations->getCollection()->transform(function ($reservation) {
            $this->addCoverUrlToReservation($reservation);
            $this->addQueuePositionIfPending($reservation);
            
            // Agregar campo expiration_date para el frontend (alias de pickup_deadline)
            $reservation->expiration_date = $reservation->pickup_deadline;
            
            return $reservation;
        });
    }

    /**
     * Agregar URL de portada a la reserva
     * 
     * @param BookReservation $reservation
     * @return void
     */
    private function addCoverUrlToReservation(BookReservation $reservation): void
    {
        $book = $this->getBookFromReservation($reservation);
        
        if (!$book) {
            return;
        }

        $coverUrl = $this->getCoverUrl($book);

        // Asignar al libro correcto según origen
        if ($reservation->physicalCopy?->book) {
            $reservation->physicalCopy->book->cover_url = $coverUrl;
        } elseif ($reservation->book) {
            $reservation->book->cover_url = $coverUrl;
        }
    }

    /**
     * Agregar posición en cola si está pending
     * 
     * @param BookReservation $reservation
     * @return void
     */
    private function addQueuePositionIfPending(BookReservation $reservation): void
    {
        if ($reservation->status !== 'pending') {
            return;
        }

        $bookId = $this->getBookIdFromReservation($reservation);
        
        if (!$bookId) {
            return;
        }

        $reservation->queue_position = $this->calculateQueuePosition(
            $bookId,
            $reservation->reservation_date
        );
        
        $reservation->total_in_queue = $this->calculateTotalInQueue($bookId);
    }

    /**
     * Obtener libro desde reserva (dual-source)
     * 
     * @param BookReservation $reservation
     * @return Book|null
     */
    private function getBookFromReservation(BookReservation $reservation): ?Book
    {
        return $reservation->physicalCopy?->book ?? $reservation->book;
    }

    /**
     * Obtener ID de libro desde reserva (dual-source)
     * 
     * @param BookReservation $reservation
     * @return int|null
     */
    private function getBookIdFromReservation(BookReservation $reservation): ?int
    {
        return $reservation->physicalCopy?->book_id ?? $reservation->book_id;
    }

    /**
     * Calcular posición en cola FIFO
     * 
     * @param int $bookId ID del libro
     * @param \Carbon\Carbon $reservationDate Fecha de reserva
     * @return int Posición en cola (1-indexed)
     */
    private function calculateQueuePosition(int $bookId, $reservationDate): int
    {
        return BookReservation::where(function ($q) use ($bookId) {
                $q->where('book_id', $bookId)
                  ->orWhereHas('physicalCopy', function ($sq) use ($bookId) {
                      $sq->where('book_id', $bookId);
                  });
            })
            ->where('status', 'pending')
            ->where('reservation_date', '<', $reservationDate)
            ->count() + 1;
    }

    /**
     * Calcular total de reservas pending para un libro
     * 
     * @param int $bookId ID del libro
     * @return int Total de reservas pending
     */
    private function calculateTotalInQueue(int $bookId): int
    {
        return BookReservation::where(function ($q) use ($bookId) {
                $q->where('book_id', $bookId)
                  ->orWhereHas('physicalCopy', function ($sq) use ($bookId) {
                      $sq->where('book_id', $bookId);
                  });
            })
            ->where('status', 'pending')
            ->count();
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
     * Agregar nota de activación
     * 
     * @param string|null $existingNotes Notas existentes
     * @param string $type Tipo de activación: 'manual' o 'auto'
     * @return string Notas actualizadas
     */
    private function appendActivationNote(?string $existingNotes, string $type): string
    {
        $action = $type === 'manual' 
            ? 'Activada manualmente por bibliotecario' 
            : 'Activada automáticamente tras cancelación';

        $newNote = sprintf(
            '%s el %s',
            $action,
            now()->format('d/m/Y H:i')
        );

        return $this->appendNote($existingNotes, $newNote);
    }

    /**
     * Agregar nota de cancelación
     * 
     * @param string|null $existingNotes Notas existentes
     * @return string Notas actualizadas
     */
    private function appendCancellationNote(?string $existingNotes): string
    {
        $newNote = sprintf(
            'Cancelada por bibliotecario el %s',
            now()->format('d/m/Y H:i')
        );

        return $this->appendNote($existingNotes, $newNote);
    }

    /**
     * Agregar nota genérica
     * 
     * @param string|null $existingNotes Notas existentes
     * @param string $newNote Nueva nota a agregar
     * @return string Notas actualizadas
     */
    private function appendNote(?string $existingNotes, string $newNote): string
    {
        return empty($existingNotes) 
            ? $newNote 
            : "{$existingNotes}\n{$newNote}";
    }

    /**
     * Calcular estadísticas de reservas
     * 
     * @return array Estadísticas agregadas
     */
    private function calculateStats(): array
    {
        return [
            'total' => BookReservation::count(),
            'pending' => BookReservation::where('status', 'pending')->count(),
            'ready' => BookReservation::where('status', 'ready')->count(),
            'collected' => BookReservation::where('status', 'collected')->count(),
            'expired' => BookReservation::where('status', 'expired')->count(),
            'cancelled' => BookReservation::where('status', 'cancelled')->count(),
            'expiring_soon' => BookReservation::where('status', 'ready')
                ->where('pickup_deadline', '<=', now()->addDays(self::EXPIRING_SOON_DAYS))
                ->where('pickup_deadline', '>', now())
                ->count(),
        ];
    }

    /**
     * Obtener libros con más reservas pendientes
     * 
     * @return \Illuminate\Support\Collection Top libros ordenados por reservas
     */
    private function getBooksWithPendingReservations()
    {
        return Book::whereHas('reservations', function ($query) {
                $query->where('status', 'pending');
            })
            ->withCount(['reservations' => function ($query) {
                $query->where('status', 'pending');
            }])
            ->with(['publisher'])
            ->orderBy('reservations_count', 'desc')
            ->limit(self::POPULAR_BOOKS_LIMIT)
            ->get()
            ->map(function ($book) {
                $book->cover_url = $this->getCoverUrl($book);
                return $book;
            });
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
            ->route('admin.reservations.index')
            ->with('success', $message);
    }

    /**
     * Manejar error y redirigir
     * 
     * @param \Exception $exception Excepción capturada
     * @param int $reservationId ID de la reserva
     * @param string $action Acción que falló
     * @return RedirectResponse
     */
    private function handleError(
        \Exception $exception,
        int $reservationId,
        string $action
    ): RedirectResponse {
        Log::error("Error al {$action}", [
            'reservation_id' => $reservationId,
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString(),
        ]);

        return redirect()
            ->route('admin.reservations.index')
            ->with('error', "Ocurrió un error al {$action}.");
    }

    // ===============================================
    // MÉTODOS PRIVADOS - LOGGING
    // ===============================================

    /**
     * Registrar acción en logs
     * 
     * @param string $message Mensaje descriptivo
     * @param BookReservation $reservation Reserva afectada
     * @param array $extraData Datos adicionales
     * @return void
     */
    private function logAction(
        string $message,
        BookReservation $reservation,
        array $extraData = []
    ): void {
        Log::info($message, array_merge([
            'reservation_id' => $reservation->id,
            'book_id' => $reservation->book_id,
            'user_id' => $reservation->user_id,
            'admin_id' => auth()->id(),
        ], $extraData));
    }

    /**
     * Registrar activación de reserva
     * 
     * @param BookReservation $reservation Reserva activada
     * @param string $type Tipo de activación: 'manual' o 'auto'
     * @return void
     */
    private function logReservationActivation(
        BookReservation $reservation,
        string $type
    ): void {
        Log::info('Reserva activada', [
            'reservation_id' => $reservation->id,
            'book_id' => $reservation->book_id,
            'user_id' => $reservation->user_id,
            'activation_type' => $type,
            'admin_id' => $type === 'manual' ? auth()->id() : null,
        ]);
    }
}
