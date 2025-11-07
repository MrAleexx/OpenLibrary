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

class CartController extends Controller
{
    /**
     * Periodo de préstamo predeterminado en días
     */
    private const LOAN_PERIOD_DAYS = 14;

    /**
     * Estados de préstamo considerados como activos
     */
    private const ACTIVE_LOAN_STATUSES = ['active', 'overdue'];

    /**
     * Mensajes de error estandarizados
     */
    private const ERROR_BOOK_NOT_ACTIVE = 'Este libro no está disponible actualmente';
    private const ERROR_NO_COPIES = 'No hay copias disponibles de este libro';
    private const ERROR_ALREADY_ON_LOAN = 'Ya tienes un préstamo activo de este libro';
    private const ERROR_ALREADY_IN_CART = 'Este libro ya está en tu carrito';
    private const ERROR_EMAIL_NOT_VERIFIED = 'Debes verificar tu correo electrónico antes de hacer préstamos';
    private const ERROR_SERVER = 'Error al procesar la solicitud';

    /**
     * Mostrar página del carrito de préstamos
     * 
     * @return Response
     */
    public function index(): Response
    {
        $user = auth()->user();
        $activeLoansCount = $this->getActiveBookLoans();
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

            $this->logCartAction('Book added to cart', $book, count($cart));

            return response()->json([
                'success' => true,
                'count' => count($cart),
                'message' => 'Libro agregado al carrito exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error adding book to cart', [
                'user_id' => auth()->id(),
                'book_id' => $book->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Error al agregar el libro al carrito',
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Remove a book from cart
     */
    public function remove(Book $book): JsonResponse
    {
        try {
            $cart = session()->get('cart', []);
            
            // Remove book from cart
            $cart = array_values(array_filter($cart, function ($id) use ($book) {
                return $id !== $book->id;
            }));

            session()->put('cart', $cart);

            Log::info('Book removed from cart', [
                'user_id' => auth()->id(),
                'book_id' => $book->id,
                'cart_count' => count($cart)
            ]);

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
     * Process checkout and create loans
     */
    public function checkout(CheckoutRequest $request): JsonResponse
    {
        try {
            $bookIds = $request->validated()['book_ids'];
            $user = auth()->user();

            // Verify user email is verified
            if (!$user->hasVerifiedEmail()) {
                return response()->json([
                    'error' => 'Debes verificar tu correo electrónico antes de hacer préstamos',
                    'code' => 'EMAIL_NOT_VERIFIED'
                ], 422);
            }

            // Check active loans limit
            $activeLoansCount = $this->getActiveBookLoans();
            $totalAfterCheckout = $activeLoansCount + count($bookIds);
            $maxLoans = $user->max_concurrent_loans;

            if ($totalAfterCheckout > $maxLoans) {
                return response()->json([
                    'error' => 'Excedes el límite de ' . $maxLoans . ' préstamos simultáneos',
                    'code' => 'LOAN_LIMIT_EXCEEDED',
                    'current_loans' => $activeLoansCount,
                    'max_loans' => $maxLoans
                ], 422);
            }

            // Use database transaction for data consistency
            $loans = DB::transaction(function () use ($bookIds, $user) {
                $createdLoans = [];
                $loanDate = now();
                $dueDate = now()->addDays(self::LOAN_PERIOD_DAYS);

                foreach ($bookIds as $bookId) {
                    $book = Book::findOrFail($bookId);

                    // Validate book is active
                    if (!$book->is_active) {
                        throw new \Exception("El libro '{$book->title}' no está disponible");
                    }

                    // Get available copy
                    $availableCopy = $this->getAvailableCopy($book);
                    if (!$availableCopy) {
                        throw new \Exception("No hay copias disponibles de '{$book->title}'");
                    }

                    // Check user doesn't have active loan of this book
                    $hasActiveLoan = $user->bookLoans()
                        ->whereHas('physicalCopy', function ($query) use ($book) {
                            $query->where('book_id', $book->id);
                        })
                        ->where('status', 'active')
                        ->exists();

                    if ($hasActiveLoan) {
                        throw new \Exception("Ya tienes un préstamo activo de '{$book->title}'");
                    }

                    // Create loan
                    $loan = BookLoan::create([
                        'user_id' => $user->id,
                        'physical_copy_id' => $availableCopy->id,
                        'loan_date' => $loanDate,
                        'due_date' => $dueDate,
                        'status' => 'active',
                        'renewal_count' => 0,
                    ]);

                    // Update physical copy status
                    $availableCopy->update(['status' => 'loaned']);

                    // Update book availability counters
                    $book->decrement('available_physical_copies');
                    
                    // Update book statistics
                    $book->increment('total_loans');

                    $createdLoans[] = $loan->load(['physicalCopy.book.contributors', 'user']);

                    Log::info('Loan created', [
                        'loan_id' => $loan->id,
                        'user_id' => $user->id,
                        'book_id' => $book->id,
                        'physical_copy_id' => $availableCopy->id,
                        'due_date' => $dueDate
                    ]);
                }

                return $createdLoans;
            });

            // Clear cart after successful checkout
            session()->forget('cart');

            Log::info('Checkout completed successfully', [
                'user_id' => $user->id,
                'loans_count' => count($loans)
            ]);

            return response()->json([
                'success' => true,
                'loans' => $loans,
                'message' => count($loans) === 1 
                    ? 'Préstamo creado exitosamente' 
                    : count($loans) . ' préstamos creados exitosamente',
                'due_date' => $loans[0]->due_date->format('Y-m-d'),
            ]);

        } catch (\Exception $e) {
            Log::error('Checkout failed', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'error' => $e->getMessage(),
                'code' => 'CHECKOUT_FAILED'
            ], 422);
        }
    }

    /**
     * Get cart items with full book details
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
            Log::error('Error getting cart items', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Error al obtener los libros del carrito',
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Clear all items from cart
     */
    public function clear(): JsonResponse
    {
        try {
            session()->forget('cart');

            Log::info('Cart cleared', [
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Carrito vaciado exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error clearing cart', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'error' => 'Error al vaciar el carrito',
                'code' => 'SERVER_ERROR'
            ], 500);
        }
    }

    /**
     * Get count of active book loans for authenticated user
     */
    private function getActiveBookLoans(): int
    {
        return auth()->user()->bookLoans()
            ->where('status', 'active')
            ->count();
    }

    /**
     * Get first available physical copy of a book
     */
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
     * Validar que el libro puede ser agregado al carrito
     * 
     * Verifica que el libro esté activo y tenga copias disponibles
     * 
     * @param Book $book
     * @return JsonResponse|null Retorna JsonResponse si hay error, null si es válido
     */
    private function validateBookForCart(Book $book): ?JsonResponse
    {
        if (!$book->is_active) {
            return response()->json([
                'error' => self::ERROR_BOOK_NOT_ACTIVE,
                'code' => 'BOOK_NOT_ACTIVE'
            ], 422);
        }

        $availableCopy = $this->getAvailableCopy($book);
        if (!$availableCopy) {
            return response()->json([
                'error' => self::ERROR_NO_COPIES,
                'code' => 'NO_COPIES_AVAILABLE'
            ], 422);
        }

        return null;
    }

    /**
     * Validar que el usuario puede agregar el libro al carrito
     * 
     * Verifica:
     * - No tiene préstamo activo del libro
     * - El libro no está ya en el carrito
     * - No excede el límite de préstamos
     * 
     * @param Book $book
     * @return JsonResponse|null Retorna JsonResponse si hay error, null si es válido
     */
    private function validateUserCanAddToCart(Book $book): ?JsonResponse
    {
        // Verificar si ya tiene préstamo activo
        if ($this->userHasActiveLoanOf($book)) {
            return response()->json([
                'error' => self::ERROR_ALREADY_ON_LOAN,
                'code' => 'ALREADY_ON_LOAN'
            ], 422);
        }

        $cart = session()->get('cart', []);

        // Verificar si ya está en el carrito
        if (in_array($book->id, $cart)) {
            return response()->json([
                'error' => self::ERROR_ALREADY_IN_CART,
                'code' => 'ALREADY_IN_CART'
            ], 422);
        }

        // Verificar límite de carrito
        $maxLoans = auth()->user()->max_concurrent_loans;
        if (count($cart) >= $maxLoans) {
            return response()->json([
                'error' => 'No puedes agregar más de ' . $maxLoans . ' libros al carrito',
                'code' => 'CART_LIMIT_REACHED'
            ], 422);
        }

        return null;
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
            ->where('book_loans.status', 'active')
            ->exists();
    }

    /**
     * Registrar acción del carrito en los logs
     * 
     * @param string $message
     * @param Book $book
     * @param int $cartCount
     * @return void
     */
    private function logCartAction(string $message, Book $book, int $cartCount): void
    {
        Log::info($message, [
            'user_id' => auth()->id(),
            'book_id' => $book->id,
            'book_title' => $book->title,
            'cart_count' => $cartCount
        ]);
    }
}
