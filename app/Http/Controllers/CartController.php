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
     * Maximum number of books a user can have on loan simultaneously
     */
    private const MAX_LOANS = 5;

    /**
     * Default loan period in days
     */
    private const LOAN_PERIOD_DAYS = 14;

    /**
     * Show the cart page
     */
    public function index(): Response
    {
        $user = auth()->user();
        
        // Get active loans count
        $activeLoansCount = $this->getActiveBookLoans();
        $remainingLoans = self::MAX_LOANS - $activeLoansCount;

        return Inertia::render('Cart/Index', [
            'remainingLoans' => $remainingLoans,
            'maxLoans' => self::MAX_LOANS,
            'activeLoansCount' => $activeLoansCount,
        ]);
    }

    /**
     * Add a book to cart (session-based)
     */
    public function add(Book $book, Request $request): JsonResponse
    {
        try {
            // Validate book is active and available
            if (!$book->is_active) {
                return response()->json([
                    'error' => 'Este libro no está disponible actualmente',
                    'code' => 'BOOK_NOT_ACTIVE'
                ], 422);
            }

            // Check if book has available physical copies
            $availableCopy = $this->getAvailableCopy($book);
            if (!$availableCopy) {
                return response()->json([
                    'error' => 'No hay copias disponibles de este libro',
                    'code' => 'NO_COPIES_AVAILABLE'
                ], 422);
            }

            // Check if user already has this book on active loan
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

            // Get current cart from session
            $cart = session()->get('cart', []);

            // Check if book is already in cart
            if (in_array($book->id, $cart)) {
                return response()->json([
                    'error' => 'Este libro ya está en tu carrito',
                    'code' => 'ALREADY_IN_CART'
                ], 422);
            }

            // Check cart size limit
            if (count($cart) >= self::MAX_LOANS) {
                return response()->json([
                    'error' => 'No puedes agregar más de ' . self::MAX_LOANS . ' libros al carrito',
                    'code' => 'CART_LIMIT_REACHED'
                ], 422);
            }

            // Add book to cart
            $cart[] = $book->id;
            session()->put('cart', $cart);

            Log::info('Book added to cart', [
                'user_id' => auth()->id(),
                'book_id' => $book->id,
                'book_title' => $book->title,
                'cart_count' => count($cart)
            ]);

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

            if ($totalAfterCheckout > self::MAX_LOANS) {
                return response()->json([
                    'error' => 'Excedes el límite de ' . self::MAX_LOANS . ' préstamos simultáneos',
                    'code' => 'LOAN_LIMIT_EXCEEDED',
                    'current_loans' => $activeLoansCount,
                    'max_loans' => self::MAX_LOANS
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
                    $availableCopy->update(['status' => 'on_loan']);

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
    private function getAvailableCopy(Book $book): ?PhysicalCopy
    {
        return $book->physicalCopies()
            ->where('status', 'available')
            ->first();
    }
}
