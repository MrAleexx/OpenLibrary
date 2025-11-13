<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\PhysicalCopy;
use App\Http\Requests\CheckoutRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Controlador del Carrito de Préstamos
 * 
 * Responsabilidades:
 * - Gestionar carrito de libros en sesión
 * - Procesar checkout creando préstamos con estado pending_pickup
 * - Validar disponibilidad y límites del usuario
 * 
 * Flujo de checkout:
 * 1. Validar email verificado
 * 2. Validar límite de préstamos
 * 3. Crear préstamos en estado 'pending_pickup'
 * 4. Reservar copias físicas
 * 5. Actualizar contadores
 * 
 * @author Sistema de Biblioteca
 * @version 2.0
 */
class CartController extends Controller
{
    /**
     * Estados de préstamo considerados como activos para límites
     */
    private const ACTIVE_LOAN_STATUSES = [
        BookLoan::STATUS_PENDING_PICKUP,
        BookLoan::STATUS_READY_FOR_PICKUP,
        BookLoan::STATUS_ACTIVE,
        BookLoan::STATUS_OVERDUE
    ];

    /**
     * Mensajes de error estandarizados
     */
    private const ERROR_BOOK_NOT_ACTIVE = 'Este libro no está disponible actualmente';
    private const ERROR_NO_COPIES = 'No hay copias disponibles de este libro';
    private const ERROR_ALREADY_ON_LOAN = 'Ya tienes un préstamo activo de este libro';
    private const ERROR_ALREADY_IN_CART = 'Este libro ya está en tu carrito';
    private const ERROR_EMAIL_NOT_VERIFIED = 'Debes verificar tu correo electrónico antes de hacer préstamos';
    private const ERROR_LOAN_LIMIT = 'Excedes el límite de préstamos simultáneos';

    // ===============================================
    // MÉTODOS PÚBLICOS
    // ===============================================

    /**
     * Mostrar página del carrito de préstamos
     * 
     * @return Response
     */
    public function index(): Response
    {
        $user = auth()->user();
        $activeLoansCount = $this->getActiveLoansCount();
        $maxLoans = $user->max_concurrent_loans;
        $remainingLoans = $maxLoans - $activeLoansCount;

        return Inertia::render('Cart/Index', [
            'remainingLoans' => $remainingLoans,
            'maxLoans' => $maxLoans,
            'activeLoansCount' => $activeLoansCount,
        ]);
    }

    /**
     * Agregar un libro al carrito (basado en sesión)
     * 
     * @param Book $book Libro a agregar
     * @param Request $request
     * @return JsonResponse
     */
    public function add(Book $book, Request $request): JsonResponse
    {
        try {
            // Validar que el libro esté activo
            $validation = $this->validateBookForCart($book);
            if ($validation !== null) {
                return $validation;
            }

            // Validar límites y estado del usuario
            $userValidation = $this->validateUserCanAddToCart($book);
            if ($userValidation !== null) {
                return $userValidation;
            }

            // Agregar libro al carrito
            $cart = session()->get('cart', []);
            $cart[] = $book->id;
            session()->put('cart', $cart);

            $this->logCartAction('Libro agregado al carrito', $book, count($cart));

            return response()->json([
                'success' => true,
                'count' => count($cart),
                'message' => 'Libro agregado al carrito exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->handleGenericError($e, 'agregar el libro al carrito', $book->id);
        }
    }

    /**
     * Remover libro del carrito
     * 
     * @param Book $book
     * @return JsonResponse
     */
    public function remove(Book $book): JsonResponse
    {
        try {
            $cart = session()->get('cart', []);
            
            // Remover libro del carrito
            $cart = array_values(array_filter($cart, function ($id) use ($book) {
                return $id !== $book->id;
            }));

            session()->put('cart', $cart);

            $this->logCartAction('Libro removido del carrito', $book, count($cart));

            return response()->json([
                'success' => true,
                'count' => count($cart),
                'message' => 'Libro removido del carrito'
            ]);

        } catch (\Exception $e) {
            Log::error('Error removing book from cart', [
                'user_id' => auth()->id(),
                'book_id' => $book->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Error al remover el libro del carrito',
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Procesar checkout y crear préstamos
     * 
     * Flujo nuevo:
     * 1. Validaciones de usuario y límites
     * 2. Crear préstamos en estado PENDING_PICKUP
     * 3. Reservar copias físicas (status = 'reserved')
     * 4. Decrementar contador de disponibilidad
     * 5. Limpiar carrito
     * 
     * IMPORTANTE: Los préstamos inician como 'pending_pickup'
     * El bibliotecario debe marcarlos como 'ready_for_pickup' 
     * y luego 'active' cuando el usuario recoja el libro.
     * 
     * @param CheckoutRequest $request
     * @return JsonResponse
     */
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        try {
            DB::beginTransaction();

            $bookIds = $request->validated()['book_ids'];
            $user = auth()->user();

            // Validar email verificado
            if (!$user->hasVerifiedEmail()) {
                return $this->errorResponse(
                    self::ERROR_EMAIL_NOT_VERIFIED,
                    'EMAIL_NOT_VERIFIED',
                    422
                );
            }

            // Validar límite de préstamos
            $validation = $this->validateLoanLimits($user, count($bookIds));
            if ($validation !== null) {
                return $validation;
            }

            // Crear préstamos
            $loans = $this->createPendingLoans($bookIds, $user);

            // Limpiar carrito
            session()->forget('cart');

            DB::commit();

            $this->logCheckoutSuccess($user, count($loans));

            return response()->json([
                'success' => true,
                'loans' => $loans,
                'message' => $this->buildCheckoutMessage(count($loans)),
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return $this->handleCheckoutError($e);
        }
    }

    /**
     * Obtener libros del carrito con detalles completos
     * 
     * @return JsonResponse
     */
    public function getItems(): JsonResponse
    {
        try {
            $cart = session()->get('cart', []);
            
            if (empty($cart)) {
                return response()->json([
                    'books' => [],
                    'count' => 0
                ]);
            }

            $books = Book::with(['contributors', 'publisher', 'categories'])
                ->whereIn('id', $cart)
                ->where('is_active', true)
                ->get();

            return response()->json([
                'books' => $books,
                'count' => count($cart)
            ]);

        } catch (\Exception $e) {
            return $this->handleGenericError($e, 'obtener los libros del carrito');
        }
    }

    /**
     * Limpiar todos los libros del carrito
     * 
     * @return JsonResponse
     */
    public function clear(): JsonResponse
    {
        try {
            session()->forget('cart');

            Log::info('Carrito vaciado', [
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Carrito vaciado exitosamente'
            ]);

        } catch (\Exception $e) {
            return $this->handleGenericError($e, 'vaciar el carrito');
        }
    }

    // ===============================================
    // MÉTODOS PRIVADOS - LÓGICA DE NEGOCIO
    // ===============================================

    /**
     * Crear préstamos en estado pending_pickup
     * 
     * @param array $bookIds IDs de libros
     * @param \App\Models\User $user Usuario
     * @return array Préstamos creados
     * @throws \Exception Si hay algún problema
     */
    private function createPendingLoans(array $bookIds, $user): array
    {
        $createdLoans = [];

        foreach ($bookIds as $bookId) {
            $book = Book::findOrFail($bookId);

            $this->validateBookForLoan($book, $user);

            $availableCopy = $this->findAndReservePhysicalCopy($book);

            $loan = $this->createLoan($user, $availableCopy);

            $this->updateBookCounters($book);

            $createdLoans[] = $loan->load(['physicalCopy.book.contributors', 'user']);

            $this->logLoanCreation($loan, $book, $availableCopy);
        }

        return $createdLoans;
    }

    /**
     * Crear registro de préstamo en pending_pickup
     * 
     * @param \App\Models\User $user
     * @param PhysicalCopy $physicalCopy
     * @return BookLoan
     */
    private function createLoan($user, PhysicalCopy $physicalCopy): BookLoan
    {
        return BookLoan::create([
            'user_id' => $user->id,
            'physical_copy_id' => $physicalCopy->id,
            'loan_date' => null, // Se asignará cuando se active
            'due_date' => null,  // Se asignará cuando se active
            'status' => BookLoan::STATUS_PENDING_PICKUP,
            'renewal_count' => 0,
            'notes' => 'Préstamo creado desde carrito. Pendiente de preparación por bibliotecario.',
        ]);
    }

    /**
     * Buscar y reservar copia física disponible
     * 
     * @param Book $book
     * @return PhysicalCopy
     * @throws \Exception Si no hay copias disponibles
     */
    private function findAndReservePhysicalCopy(Book $book): PhysicalCopy
    {
        $availableCopy = $this->getAvailableCopy($book);

        if (!$availableCopy) {
            throw new \Exception("No hay copias disponibles de '{$book->title}'");
        }

        // Reservar la copia (no se presta hasta que esté ready)
        $availableCopy->update(['status' => 'reserved']);

        return $availableCopy;
    }

    /**
     * Actualizar contadores del libro
     * 
     * @param Book $book
     * @return void
     */
    private function updateBookCounters(Book $book): void
    {
        $book->decrement('available_physical_copies');
        $book->increment('total_loans');
    }

    // ===============================================
    // MÉTODOS PRIVADOS - VALIDACIONES
    // ===============================================

    /**
     * Validar límite de préstamos del usuario
     * 
     * @param \App\Models\User $user
     * @param int $newLoansCount
     * @return JsonResponse|null
     */
    private function validateLoanLimits($user, int $newLoansCount): ?JsonResponse
    {
        $activeLoansCount = $this->getActiveLoansCount();
        $totalAfterCheckout = $activeLoansCount + $newLoansCount;
        $maxLoans = $user->max_concurrent_loans;

        if ($totalAfterCheckout > $maxLoans) {
            return response()->json([
                'error' => self::ERROR_LOAN_LIMIT . " ({$maxLoans})",
                'code' => 'LOAN_LIMIT_EXCEEDED',
                'current_loans' => $activeLoansCount,
                'max_loans' => $maxLoans,
                'requested' => $newLoansCount
            ], 422);
        }

        return null;
    }

    /**
     * Validar libro para préstamo
     * 
     * @param Book $book
     * @param \App\Models\User $user
     * @return void
     * @throws \Exception Si el libro no es válido
     */
    private function validateBookForLoan(Book $book, $user): void
    {
        if (!$book->is_active) {
            throw new \Exception("El libro '{$book->title}' no está disponible");
        }

        if ($this->userHasActiveLoanOf($book)) {
            throw new \Exception("Ya tienes un préstamo activo de '{$book->title}'");
        }
    }

    // ===============================================
    // MÉTODOS PRIVADOS - QUERIES
    // ===============================================

    /**
     * Contar préstamos activos del usuario
     * 
     * Incluye todos los estados que cuentan como "activos"
     * para el límite de préstamos simultáneos
     * 
     * @return int
     */
    private function getActiveLoansCount(): int
    {
        return auth()->user()->bookLoans()
            ->whereIn('status', self::ACTIVE_LOAN_STATUSES)
            ->count();
    }
    /**
     * Obtener primera copia física disponible de un libro
     * 
     * @param Book $book
     * @return PhysicalCopy|null
     */
    private function getAvailableCopy(Book $book): ?PhysicalCopy
    {
        return $book->physicalCopies()
            ->where('status', 'available')
            ->first();
    }

    /**
     * Verificar si el usuario tiene un préstamo activo del libro
     * 
     * @param Book $book
     * @return bool
     */
    private function userHasActiveLoanOf(Book $book): bool
    {
        return auth()->user()->bookLoans()
            ->whereHas('physicalCopy', function ($query) use ($book) {
                $query->where('book_id', $book->id);
            })
            ->whereIn('status', self::ACTIVE_LOAN_STATUSES)
            ->exists();
    }

    /**
     * Validar si el libro puede ser agregado al carrito
     * 
     * @param Book $book
     * @return JsonResponse|null Null si es válido, JsonResponse con error si no
     */
    private function validateBookForCart(Book $book): ?JsonResponse
    {
        // Validar que el libro esté activo
        if (!$book->is_active) {
            return $this->errorResponse(
                'Este libro no está disponible para préstamo',
                'BOOK_NOT_ACTIVE',
                422
            );
        }

        // Validar que tenga copias disponibles
        $availableCopy = $this->getAvailableCopy($book);
        if (!$availableCopy) {
            return $this->errorResponse(
                'No hay copias disponibles de este libro',
                'NO_COPIES_AVAILABLE',
                422
            );
        }

        return null;
    }

    /**
     * Validar si el usuario puede agregar el libro al carrito
     * 
     * @param Book $book
     * @return JsonResponse|null Null si es válido, JsonResponse con error si no
     */
    private function validateUserCanAddToCart(Book $book): ?JsonResponse
    {
        $user = auth()->user();
        $cart = session()->get('cart', []);

        // Verificar si el libro ya está en el carrito
        if (in_array($book->id, $cart)) {
            return $this->errorResponse(
                'Este libro ya está en tu carrito',
                'BOOK_ALREADY_IN_CART',
                422
            );
        }

        // Validar que el usuario no tenga un préstamo activo del mismo libro
        if ($this->userHasActiveLoanOf($book)) {
            return $this->errorResponse(
                'Ya tienes un préstamo activo de este libro',
                'ACTIVE_LOAN_EXISTS',
                422
            );
        }

        // Validar límite de préstamos (actuales + carrito)
        $activeLoansCount = $this->getActiveLoansCount();
        $totalPotentialLoans = $activeLoansCount + count($cart) + 1;

        if ($totalPotentialLoans > $user->max_concurrent_loans) {
            return $this->errorResponse(
                'Alcanzaste el límite de préstamos simultáneos (' . $user->max_concurrent_loans . ')',
                'LOAN_LIMIT_REACHED',
                422
            );
        }

        return null;
    }

    /**
     * Registrar acción en el carrito
     * 
     * @param string $action
     * @param Book $book
     * @param int $cartCount
     * @return void
     */
    private function logCartAction(string $action, Book $book, int $cartCount): void
    {
        Log::info($action, [
            'user_id' => auth()->id(),
            'book_id' => $book->id,
            'book_title' => $book->title,
            'cart_count' => $cartCount
        ]);
    }

    /**
     * Manejar error genérico en operación de carrito
     * 
     * @param \Exception $exception
     * @param string $operation
     * @param int|null $bookId
     * @return JsonResponse
     */
    private function handleGenericError(\Exception $exception, string $operation, ?int $bookId = null): JsonResponse
    {
        Log::error("Error en operación de carrito: {$operation}", [
            'user_id' => auth()->id(),
            'book_id' => $bookId,
            'error' => $exception->getMessage()
        ]);

        return $this->errorResponse(
            "Error al {$operation}",
            'SERVER_ERROR',
            500
        );
    }

    // ===============================================
    // MÉTODOS PRIVADOS - HELPERS
    // ===============================================

    /**
     * Construir mensaje de checkout exitoso
     * 
     * @param int $count Cantidad de préstamos creados
     * @return string
     */
    private function buildCheckoutMessage(int $count): string
    {
        if ($count === 1) {
            return 'Préstamo creado exitosamente. El bibliotecario preparará tu libro.';
        }

        return "{$count} préstamos creados exitosamente. El bibliotecario preparará tus libros.";
    }

    /**
     * Crear respuesta de error estandarizada
     * 
     * @param string $message
     * @param string $code
     * @param int $status
     * @return JsonResponse
     */
    private function errorResponse(string $message, string $code, int $status): JsonResponse
    {
        return response()->json([
            'error' => $message,
            'code' => $code
        ], $status);
    }

    /**
     * Manejar error en checkout
     * 
     * @param \Exception $exception
     * @return JsonResponse
     */
    private function handleCheckoutError(\Exception $exception): JsonResponse
    {
        Log::error('Checkout failed', [
            'user_id' => auth()->id(),
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);

        return $this->errorResponse(
            $exception->getMessage(),
            'CHECKOUT_FAILED',
            422
        );
    }

    // ===============================================
    // MÉTODOS PRIVADOS - LOGGING
    // ===============================================

    /**
     * Registrar creación de préstamo
     * 
     * @param BookLoan $loan
     * @param Book $book
     * @param PhysicalCopy $copy
     * @return void
     */
    private function logLoanCreation(BookLoan $loan, Book $book, PhysicalCopy $copy): void
    {
        Log::info('Préstamo creado en estado pending_pickup', [
            'loan_id' => $loan->id,
            'user_id' => $loan->user_id,
            'book_id' => $book->id,
            'book_title' => $book->title,
            'physical_copy_id' => $copy->id,
            'status' => BookLoan::STATUS_PENDING_PICKUP,
        ]);
    }

    /**
     * Registrar checkout exitoso
     * 
     * @param \App\Models\User $user
     * @param int $loansCount
     * @return void
     */
    private function logCheckoutSuccess($user, int $loansCount): void
    {
        Log::info('Checkout completado exitosamente', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'loans_created' => $loansCount,
            'timestamp' => now()->toIso8601String(),
        ]);
    }
}
