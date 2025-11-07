<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookReservation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class ReservationController extends Controller
{
    /**
     * Display user's reservations
     */
    public function index(): Response
    {
        $reservations = auth()->user()->reservations()
            ->with(['book.contributors', 'book.publisher'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return Inertia::render('Reservations/Index', [
            'reservations' => $reservations,
        ]);
    }

    /**
     * Create a new reservation
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'book_id' => 'required|exists:books,id',
            ]);

            $book = Book::findOrFail($validated['book_id']);

            // Validación 1: No se pueden reservar libros digitales
            if ($book->book_type === 'digital') {
                return response()->json([
                    'error' => 'No puedes reservar libros digitales. Los libros digitales están disponibles para descarga inmediata.',
                    'code' => 'DIGITAL_BOOK'
                ], 422);
            }

            // Validación 2: Verificar si hay copias disponibles
            $availableCopies = $book->physicalCopies()
                ->where('status', 'available')
                ->count();

            if ($availableCopies > 0) {
                return response()->json([
                    'error' => 'Este libro tiene copias disponibles. Por favor, usa "Agregar al carrito" en lugar de reservar.',
                    'code' => 'COPIES_AVAILABLE',
                    'available_copies' => $availableCopies
                ], 422);
            }

            // Validación 3: Verificar si el usuario ya tiene una reserva activa
            $existingReservation = auth()->user()->reservations()
                ->where('book_id', $book->id)
                ->whereIn('status', ['pending', 'ready'])
                ->first();

            if ($existingReservation) {
                return response()->json([
                    'error' => 'Ya tienes una reserva activa para este libro',
                    'code' => 'ALREADY_RESERVED',
                    'reservation' => $existingReservation
                ], 422);
            }

            // Validación 4: Verificar si el usuario tiene un préstamo activo del libro
            $hasActiveLoan = auth()->user()->bookLoans()
                ->whereHas('physicalCopy', function ($query) use ($book) {
                    $query->where('book_id', $book->id);
                })
                ->where('status', 'active')
                ->exists();

            if ($hasActiveLoan) {
                return response()->json([
                    'error' => 'Ya tienes un préstamo activo de este libro',
                    'code' => 'ALREADY_ON_LOAN'
                ], 422);
            }

            // Validación 5: Límite de reservas activas (máximo 5)
            $activeReservationsCount = auth()->user()->reservations()
                ->whereIn('status', ['pending', 'ready'])
                ->count();

            if ($activeReservationsCount >= 5) {
                return response()->json([
                    'error' => 'Has alcanzado el límite máximo de 5 reservas activas',
                    'code' => 'RESERVATION_LIMIT'
                ], 422);
            }

            // Crear la reserva
            $reservation = BookReservation::create([
                'book_id' => $book->id,
                'user_id' => auth()->id(),
                'reservation_date' => now(),
                'pickup_deadline' => now()->addDays(7), // 7 días para recoger
                'status' => 'pending',
            ]);

            // Cargar relaciones para la respuesta
            $reservation->load(['book.contributors', 'book.publisher']);

            Log::info('Reservation created', [
                'reservation_id' => $reservation->id,
                'user_id' => auth()->id(),
                'book_id' => $book->id,
                'book_title' => $book->title
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reserva creada exitosamente. Te notificaremos cuando el libro esté disponible.',
                'reservation' => $reservation,
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'error' => 'Datos de reserva inválidos',
                'code' => 'VALIDATION_ERROR',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error('Error creating reservation', [
                'user_id' => auth()->id(),
                'book_id' => $request->book_id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => 'Error al crear la reserva. Por favor, inténtalo nuevamente.',
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Cancel a reservation
     */
    public function destroy(BookReservation $reservation): JsonResponse
    {
        try {
            // Validar que la reserva pertenece al usuario autenticado
            if ($reservation->user_id !== auth()->id()) {
                return response()->json([
                    'error' => 'No estás autorizado para cancelar esta reserva',
                    'code' => 'UNAUTHORIZED'
                ], 403);
            }

            // Validar que la reserva se puede cancelar
            if ($reservation->status === 'collected') {
                return response()->json([
                    'error' => 'No puedes cancelar una reserva que ya fue recolectada',
                    'code' => 'ALREADY_COLLECTED'
                ], 422);
            }

            if ($reservation->status === 'expired') {
                return response()->json([
                    'error' => 'No puedes cancelar una reserva que ya expiró',
                    'code' => 'ALREADY_EXPIRED'
                ], 422);
            }

            if ($reservation->status === 'cancelled') {
                return response()->json([
                    'error' => 'Esta reserva ya está cancelada',
                    'code' => 'ALREADY_CANCELLED'
                ], 422);
            }

            // Cancelar la reserva
            $reservation->update([
                'status' => 'cancelled',
                'notes' => 'Cancelada por el usuario el ' . now()->format('Y-m-d H:i:s')
            ]);

            Log::info('Reservation cancelled', [
                'reservation_id' => $reservation->id,
                'user_id' => auth()->id(),
                'book_id' => $reservation->book_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Reserva cancelada exitosamente',
            ]);

        } catch (\Exception $e) {
            Log::error('Error cancelling reservation', [
                'reservation_id' => $reservation->id,
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Error al cancelar la reserva',
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Get reservation status count for user
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = [
                'pending' => auth()->user()->reservations()->where('status', 'pending')->count(),
                'ready' => auth()->user()->reservations()->where('status', 'ready')->count(),
                'collected' => auth()->user()->reservations()->where('status', 'collected')->count(),
                'expired' => auth()->user()->reservations()->where('status', 'expired')->count(),
                'cancelled' => auth()->user()->reservations()->where('status', 'cancelled')->count(),
                'total_active' => auth()->user()->reservations()->whereIn('status', ['pending', 'ready'])->count(),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting reservation stats', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Error al obtener estadísticas',
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }
}
