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
     * Constantes para configuración del catálogo
     */
    private const BOOKS_PER_PAGE = 15;
    private const AVAILABILITY_AVAILABLE = 'available';
    private const AVAILABILITY_DOWNLOADABLE = 'downloadable';
    
    /**
     * Mostrar catálogo de libros paginado con filtros
     * 
     * Este método renderiza el catálogo público de libros permitiendo:
     * - Búsqueda por título, ISBN o nombre de autor
     * - Filtrado por categoría
     * - Filtrado por tipo de libro (físico, digital, ambos)
     * - Filtrado por disponibilidad (copias disponibles o descargables)
     * 
     * @param Request $request - Petición HTTP con parámetros de filtrado
     * @return Response - Vista Inertia con libros paginados y filtros
     */
    public function index(Request $request): Response
    {
        // Construir query base con eager loading para optimizar consultas
        $query = Book::with(['publisher', 'categories', 'contributors'])
            ->where('is_active', true);

        // Aplicar filtros según parámetros recibidos
        $this->applySearchFilter($query, $request->input('search'));
        $this->applyCategoryFilter($query, $request->input('category'));
        $this->applyTypeFilter($query, $request->input('type'));
        $this->applyAvailabilityFilter($query, $request->input('availability'));

        // Ordenar: primero los destacados, luego los más recientes
        $books = $query->orderBy('featured', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(self::BOOKS_PER_PAGE)
            ->withQueryString(); // Mantener parámetros en paginación

        // Obtener categorías activas para el componente de filtros
        $categories = Category::where('is_active', true)
            ->withCount('books')
            ->orderBy('name')
            ->get();

        return Inertia::render('Books/Index', [
            'books' => $books,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'type', 'availability'])
        ]);
    }

    /**
     * Aplicar filtro de búsqueda por texto
     * 
     * Busca coincidencias en: título del libro, ISBN, y nombre completo de autores
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
            $q->where('title', 'LIKE', "%{$search}%")
                ->orWhere('isbn', 'LIKE', "%{$search}%")
                ->orWhereHas('contributors', function ($q) use ($search) {
                    $q->where('full_name', 'LIKE', "%{$search}%");
                });
        });
    }

    /**
     * Aplicar filtro por categoría
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int|null $categoryId
     * @return void
     */
    private function applyCategoryFilter($query, ?int $categoryId): void
    {
        if (empty($categoryId)) {
            return;
        }

        $query->whereHas('categories', function ($q) use ($categoryId) {
            $q->where('categories.id', $categoryId);
        });
    }

    /**
     * Aplicar filtro por tipo de libro
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $type - Valores válidos: 'physical', 'digital', 'both'
     * @return void
     */
    private function applyTypeFilter($query, ?string $type): void
    {
        if (empty($type)) {
            return;
        }

        $query->where('book_type', $type);
    }

    /**
     * Aplicar filtro por disponibilidad
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $availability - Valores válidos: 'available', 'downloadable'
     * @return void
     */
    private function applyAvailabilityFilter($query, ?string $availability): void
    {
        if (empty($availability)) {
            return;
        }

        if ($availability === self::AVAILABILITY_AVAILABLE) {
            // Libros físicos con al menos una copia disponible
            $query->where('available_physical_copies', '>', 0);
        } elseif ($availability === self::AVAILABILITY_DOWNLOADABLE) {
            // Libros digitales que permiten descarga
            $query->where('downloadable', true);
        }
    }

    /**
     * Mostrar página de detalle de un libro
     * 
     * Renderiza la vista detallada de un libro mostrando:
     * - Información básica (título, autor, editorial, año)
     * - Categorías asociadas
     * - Tabla de contenidos (si aplica)
     * - Disponibilidad de copias físicas
     * - Estado de reservación del usuario actual
     * 
     * @param Book $book - Modelo Book obtenido por route model binding
     * @return Response - Vista Inertia con datos del libro
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException Si el libro está inactivo
     */
    public function show(Book $book): Response
    {
        // Verificar que el libro esté activo (soft delete pattern)
        if (!$book->is_active) {
            abort(404, 'Libro no disponible');
        }

        // Eager loading de relaciones con constraints específicos para optimizar consultas
        $book->load([
            'publisher',
            'language',
            'categories',
            'contributors' => function ($query) {
                // Ordenar por secuencia para mostrar autores en orden correcto
                $query->orderBy('sequence_number');
            },
            'details',
            'contents' => function ($query) {
                // Ordenar tabla de contenidos por orden establecido
                $query->orderBy('sort_order');
            },
            'physicalCopies' => function ($query) {
                // Excluir copias retiradas de circulación
                $query->where('condition', '!=', 'withdrawn');
            }
        ]);

        // Calcular copias físicas disponibles para préstamo
        $availableCount = $this->getAvailableCopiesCount($book);

        // Verificar si el usuario actual ya tiene una reserva activa
        $hasReservation = $this->userHasActiveReservation($book);

        return Inertia::render('Books/Show', [
            'book' => $book,
            'availableCopies' => $availableCount,
            'userHasReservation' => $hasReservation
        ]);
    }

    /**
     * Obtener conteo de copias físicas disponibles
     * 
     * @param Book $book
     * @return int
     */
    private function getAvailableCopiesCount(Book $book): int
    {
        return $book->physicalCopies()
            ->where('status', 'available')
            ->count();
    }

    /**
     * Verificar si el usuario tiene una reserva activa para este libro
     * 
     * @param Book $book
     * @return bool
     */
    private function userHasActiveReservation(Book $book): bool
    {
        if (!Auth::check()) {
            return false;
        }

        return $book->reservations()
            ->where('user_id', Auth::id())
            ->whereIn('status', ['pending', 'ready'])
            ->exists();
    }

    /**
     * Búsqueda avanzada de libros (requiere autenticación)
     * 
     * Similar a index() pero con búsqueda por slug de categoría
     * y ordenamiento alfabético. Requiere que el usuario esté autenticado.
     * 
     * @param Request $request
     * @return Response
     */
    public function search(Request $request): Response
    {
        $search = $request->input('search');
        $categorySlug = $request->input('category');

        // Query base de libros activos con relaciones optimizadas
        $query = Book::with(['publisher', 'categories'])
            ->where('is_active', true);

        // Aplicar búsqueda por texto si se proporciona
        if (!empty($search)) {
            $this->applySearchFilter($query, $search);
        }

        // Filtrar por slug de categoría (diferente a index que usa ID)
        if (!empty($categorySlug)) {
            $query->whereHas('categories', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // Ordenar: destacados primero, luego alfabéticamente
        $books = $query->orderBy('featured', 'desc')
            ->orderBy('title')
            ->paginate(self::BOOKS_PER_PAGE)
            ->withQueryString();

        // Obtener categorías para el sidebar de filtros
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
