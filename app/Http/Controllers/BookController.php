<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class BookController extends Controller
{
    /**
     * Mostrar catálogo de libros paginado
     * 
     * @param Request $request
     * @return Response
     */
    public function index(Request $request): Response
    {
        // Obtener libros activos con relaciones
        $books = Book::with(['publisher', 'categories'])
            ->where('is_active', true)
            ->orderBy('featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Obtener categorías activas para el sidebar/filtros
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Books/Index', [
            'books' => $books,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category'])
        ]);
    }

    /**
     * Mostrar detalle completo de un libro
     * 
     * @param Book $book
     * @return Response
     */
    public function show(Book $book): Response
    {
        // Verificar que el libro esté activo
        if (!$book->is_active) {
            abort(404);
        }

        // Cargar todas las relaciones necesarias
        $book->load([
            'publisher',
            'categories',
            'contributors' => function ($query) {
                $query->orderBy('sequence_number');
            },
            'details',
            'contents' => function ($query) {
                $query->orderBy('page_number');
            },
            'physicalCopies' => function ($query) {
                $query->where('condition', '!=', 'withdrawn');
            }
        ]);

        // Contar copias físicas disponibles
        $availableCount = $book->physicalCopies()
            ->where('status', 'available')
            ->count();

        // Verificar si el usuario tiene una reserva pendiente para este libro
        $hasReservation = false;
        if (Auth::check()) {
            $hasReservation = $book->reservations()
                ->where('user_id', Auth::id())
                ->whereIn('status', ['pending', 'ready'])
                ->exists();
        }

        return Inertia::render('Books/Show', [
            'book' => $book,
            'availableCopies' => $availableCount,
            'userHasReservation' => $hasReservation
        ]);
    }

    /**
     * Buscar y filtrar libros
     * 
     * @param Request $request
     * @return Response
     */
    public function search(Request $request): Response
    {
        $search = $request->input('search');
        $categorySlug = $request->input('category');

        // Query base de libros activos
        $query = Book::with(['publisher', 'categories'])
            ->where('is_active', true);

        // Filtrar por búsqueda de texto
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'LIKE', "%{$search}%")
                    ->orWhere('isbn', 'LIKE', "%{$search}%")
                    ->orWhereHas('contributors', function ($q) use ($search) {
                        $q->where('full_name', 'LIKE', "%{$search}%");
                    });
            });
        }

        // Filtrar por categoría
        if ($categorySlug) {
            $query->whereHas('categories', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Paginar resultados
        $books = $query->orderBy('featured', 'desc')
            ->orderBy('title')
            ->paginate(15)
            ->withQueryString();

        // Obtener categorías activas para filtros
        $categories = Category::where('is_active', true)
            ->orderBy('name')
            ->get();

        return Inertia::render('Books/Search', [
            'books' => $books,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category'])
        ]);
    }
}
