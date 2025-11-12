<?php

namespace App\Http\Controllers;

use App\Models\BookReservation;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;
use Carbon\Carbon;

/**
 * Controlador para gestionar las reservas de libros del usuario
 * 
 * Implementa la lógica completa de una biblioteca real:
 * - Límite de reservas activas por usuario (5)
 * - Expiración automática de reservas después de 7 días
 * - Validación de duplicados
 * - Estados: pending, ready, collected, expired, cancelled
 */
class UserReservationController extends Controller
{
    /**
     * Límite de reservas activas por usuario
     */
    private const MAX_ACTIVE_RESERVATIONS = 5;

    /**
     * Estados que se consideran como reservas activas
     */
    private const ACTIVE_STATUSES = ['pending', 'ready'];

    /**
     * Mensajes de error
     */
    private const ERROR_MAX_RESERVATIONS = 'Has alcanzado el límite máximo de reservas activas.';
    private const ERROR_RESERVATION_NOT_FOUND = 'La reserva no fue encontrada.';
    private const ERROR_CANNOT_CANCEL = 'No puedes cancelar esta reserva en su estado actual.';

    /**
     * Mostrar la lista de reservas del usuario autenticado
     * 
     * @return Response
     */
    public function index(): Response
    {
        $user = Auth::user();

        // Procesar reservas expiradas antes de mostrar la lista
        $this->processExpiredReservations($user->id);

        // Obtener reservas del usuario con información del libro
        $reservations = BookReservation::with([
                'book.publisher',
                'book.contributors' => function ($query) {
                    $query->orderByRaw("CASE 
                            WHEN contributor_type = 'author' THEN 1 
                            WHEN contributor_type = 'editor' THEN 2 
                            WHEN contributor_type = 'translator' THEN 3 
                            ELSE 4 
                        END");
                }
            ])
            ->where('user_id', $user->id)
            ->orderByRaw("
                CASE status
                    WHEN 'ready' THEN 1
                    WHEN 'pending' THEN 2
                    WHEN 'collected' THEN 3
                    WHEN 'expired' THEN 4
                    WHEN 'cancelled' THEN 5
                    ELSE 6
                END
            ")
            ->orderBy('reservation_date', 'desc')
            ->get();

        // Agregar URL de portada a cada libro
        $reservations->each(function ($reservation) {
            if ($reservation->book) {
                $reservation->book->cover_url = $this->getCoverUrl($reservation->book);
            }
        });

        // Calcular estadísticas
        $stats = $this->calculateStats($reservations);

        return Inertia::render('Reservations/Index', [
            'reservations' => [
                'data' => $reservations,
                'current_page' => 1,
                'last_page' => 1,
                'total' => $reservations->count(),
            ],
            'stats' => $stats,
        ]);
    }

    /**
     * Cancelar una reserva
     * 
     * Solo se pueden cancelar reservas en estado 'pending' o 'ready'
     * 
     * @param int $id ID de la reserva
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(int $id)
    {
        $user = Auth::user();

        $reservation = BookReservation::where('id', $id)
            ->where('user_id', $user->id)
            ->first();

        if (!$reservation) {
            return redirect()
                ->route('reservations.index')
                ->with('error', self::ERROR_RESERVATION_NOT_FOUND);
        }

        // Validar que la reserva se puede cancelar
        if (!$this->canCancelReservation($reservation)) {
            return redirect()
                ->route('reservations.index')
                ->with('error', self::ERROR_CANNOT_CANCEL);
        }

        try {
            DB::beginTransaction();

            // Actualizar estado de la reserva
            $reservation->update([
                'status' => 'cancelled',
                'cancellation_date' => now(),
                'notes' => $this->appendNote(
                    $reservation->notes,
                    'Cancelada por el usuario el ' . now()->format('d/m/Y H:i')
                ),
            ]);

            // Si la reserva estaba en estado 'ready', restaurar la disponibilidad
            if ($reservation->status === 'ready') {
                $this->restoreBookAvailability($reservation->book_id);
            }

            DB::commit();

            Log::info('Reserva cancelada', [
                'reservation_id' => $reservation->id,
                'user_id' => $user->id,
                'book_id' => $reservation->book_id,
            ]);

            return redirect()
                ->route('reservations.index')
                ->with('success', 'Reserva cancelada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al cancelar reserva', [
                'reservation_id' => $id,
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);

            return redirect()
                ->route('reservations.index')
                ->with('error', 'Ocurrió un error al cancelar la reserva. Por favor, intenta de nuevo.');
        }
    }

    // ===============================================
    // MÉTODOS PRIVADOS
    // ===============================================

    /**
     * Procesar reservas expiradas automáticamente
     * 
     * Las reservas en estado 'ready' expiran si:
     * - Han pasado más de 7 días desde la fecha de reserva
     * - La fecha actual es mayor al pickup_deadline
     * 
     * @param int $userId
     * @return void
     */
    private function processExpiredReservations(int $userId): void
    {
        try {
            $expiredReservations = BookReservation::where('user_id', $userId)
                ->where('status', 'ready')
                ->where('pickup_deadline', '<', now())
                ->get();

            foreach ($expiredReservations as $reservation) {
                DB::beginTransaction();

                $reservation->update([
                    'status' => 'expired',
                    'notes' => $this->appendNote(
                        $reservation->notes,
                        'Expirada automáticamente el ' . now()->format('d/m/Y H:i')
                    ),
                ]);

                // Restaurar disponibilidad del libro
                $this->restoreBookAvailability($reservation->book_id);

                DB::commit();

                Log::info('Reserva expirada automáticamente', [
                    'reservation_id' => $reservation->id,
                    'user_id' => $userId,
                    'book_id' => $reservation->book_id,
                ]);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error al procesar reservas expiradas', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Calcular estadísticas de las reservas
     * 
     * @param \Illuminate\Database\Eloquent\Collection $reservations
     * @return array
     */
    private function calculateStats($reservations): array
    {
        return [
            'total' => $reservations->count(),
            'pending' => $reservations->where('status', 'pending')->count(),
            'ready' => $reservations->where('status', 'ready')->count(),
            'collected' => $reservations->where('status', 'collected')->count(),
            'expired' => $reservations->where('status', 'expired')->count(),
            'cancelled' => $reservations->where('status', 'cancelled')->count(),
        ];
    }

    /**
     * Validar si una reserva puede ser cancelada
     * 
     * Solo se pueden cancelar reservas en estado 'pending' o 'ready'
     * 
     * @param BookReservation $reservation
     * @return bool
     */
    private function canCancelReservation(BookReservation $reservation): bool
    {
        return in_array($reservation->status, self::ACTIVE_STATUSES);
    }

    /**
     * Restaurar la disponibilidad de un libro cuando se cancela/expira una reserva
     * 
     * @param int $bookId
     * @return void
     */
    private function restoreBookAvailability(int $bookId): void
    {
        $book = Book::find($bookId);

        if ($book && $book->available_physical_copies < $book->total_physical_copies) {
            $book->increment('available_physical_copies');

            Log::info('Disponibilidad de libro restaurada', [
                'book_id' => $bookId,
                'available_copies' => $book->available_physical_copies,
            ]);
        }
    }

    /**
     * Agregar una nota al campo notes de la reserva
     * 
     * @param string|null $existingNotes
     * @param string $newNote
     * @return string
     */
    private function appendNote(?string $existingNotes, string $newNote): string
    {
        if (empty($existingNotes)) {
            return $newNote;
        }

        return $existingNotes . "\n" . $newNote;
    }

    /**
     * Obtener URL de la portada del libro
     * 
     * @param Book $book
     * @return string|null
     */
    private function getCoverUrl(Book $book): ?string
    {
        if (!empty($book->cover_image)) {
            // Si es una URL completa, retornarla directamente
            if (filter_var($book->cover_image, FILTER_VALIDATE_URL)) {
                return $book->cover_image;
            }

            // Si es una ruta relativa, construir la URL completa
            return asset('storage/' . $book->cover_image);
        }

        // Si no hay portada, usar placeholder
        return null;
    }

    /**
     * Contar reservas activas del usuario
     * 
     * @param int $userId
     * @return int
     */
    private function countActiveReservations(int $userId): int
    {
        return BookReservation::where('user_id', $userId)
            ->whereIn('status', self::ACTIVE_STATUSES)
            ->count();
    }
}
