<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\Language;
use App\Models\PhysicalCopy;
use App\Models\BookDetail;
use App\Models\BookContributor;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

/**
 * Controlador de administración de libros
 * 
 * Gestiona CRUD completo de libros del sistema incluyendo:
 * - Listado con filtros y búsqueda
 * - Creación con categorías, autores y detalles
 * - Edición de datos y copias físicas
 * - Eliminación (soft delete)
 * - Gestión de imágenes de portada
 */
class BookController extends Controller
{
    // ===============================================
    // CONSTANTES
    // ===============================================

    /**
     * Configuración de paginación
     */
    private const BOOKS_PER_PAGE = 12;
    private const RECENT_DOWNLOADS_LIMIT = 10;

    /**
     * Campos permitidos para ordenamiento
     */
    private const ALLOWED_SORT_FIELDS = [
        'title',
        'created_at',
        'total_downloads',
        'total_views',
        'publication_year'
    ];

    /**
     * Ordenamiento por defecto
     */
    private const DEFAULT_SORT_FIELD = 'created_at';
    private const DEFAULT_SORT_DIRECTION = 'desc';

    /**
     * Estados de filtro de status
     */
    private const STATUS_ACTIVE = 'active';
    private const STATUS_INACTIVE = 'inactive';
    private const STATUS_FEATURED = 'featured';

    /**
     * Tipos de libro disponibles
     */
    private const BOOK_TYPE_DIGITAL = 'digital';
    private const BOOK_TYPE_PHYSICAL = 'physical';
    private const BOOK_TYPE_BOTH = 'both';

    // ===============================================
    // MÉTODOS PÚBLICOS
    // ===============================================

    /**
     * Listar libros con filtros y paginación
     * 
     * @param Request $request Parámetros de filtrado y ordenamiento
     * @return Response Vista Inertia con libros paginados
     */
    public function index(Request $request): Response
    {
        $query = $this->buildBooksQuery();

        $this->applyFilters($query, $request);
        $this->applySorting($query, $request);

        $books = $query->paginate(self::BOOKS_PER_PAGE)->withQueryString();

        return Inertia::render('admin/books/Index', [
            'books' => $books,
            'filters' => $this->extractFilters($request),
            'sort' => $this->extractSortParams($request),
            'categories' => Category::where('is_active', true)->get(),
            'stats' => $this->calculateStats(),
        ]);
    }

    /**
     * Mostrar formulario de creación de libro
     * 
     * @return Response Vista Inertia con datos para formulario
     */
    public function create(): Response
    {
        return Inertia::render('admin/books/Create', [
            'categories' => $this->getSelectableCategories(),
            'publishers' => Publisher::where('is_active', true)->get(),
            'languages' => Language::where('is_active', true)->get(),
            'book_types' => $this->getBookTypes(),
        ]);
    }

    /**
     * Mostrar detalles de un libro
     * 
     * @param Book $book Libro a mostrar
     * @return Response Vista Inertia con detalles del libro
     */
    public function show(Book $book): Response
    {
        $book->load([
            'publisher',
            'categories',
            'details',
            'contributors',
            'physicalCopies',
            'downloads' => function ($query) {
                $query->limit(self::RECENT_DOWNLOADS_LIMIT)->latest();
            }
        ]);

        return Inertia::render('admin/books/Show', [
            'book' => $book,
            'stats' => $this->getBookStats($book),
        ]);
    }

    /**
     * Mostrar formulario de edición de libro
     * 
     * @param Book $book Libro a editar
     * @return Response Vista Inertia con datos para edición
     */
    public function edit(Book $book): Response
    {
        $book->load(['categories', 'contributors', 'details']);

        return Inertia::render('admin/books/Edit', [
            'book' => $book,
            'categories' => $this->getSelectableCategories(),
            'publishers' => Publisher::where('is_active', true)->get(),
            'languages' => Language::where('is_active', true)->get(),
            'book_types' => $this->getBookTypes(),
        ]);
    }

    /**
     * Alternar estado destacado de un libro
     * 
     * @param Book $book Libro a modificar
     * @return RedirectResponse
     */
    public function toggleFeatured(Book $book): RedirectResponse
    {
        $book->update(['featured' => !$book->featured]);

        return back()->with('success', 'Estado destacado actualizado.');
    }

    public function toggleActive(Book $book)
    {
        $book->update(['is_active' => !$book->is_active]);

        return back()->with('success', 'Estado activo actualizado.');
    }

    /**
     * Obtener la ruta completa de una categoría (padre > hijo > nieto)
     */
    private function getCategoryFullPath(Category $category): string
    {
        $path = [];
        $current = $category;

        while ($current) {
            array_unshift($path, $current->name);
            $current = $current->parent;
        }

        return implode(' > ', $path);
    }

    /**
     * Obtener el breadcrumb de una categoría para mostrar en tooltips
     */
    private function getCategoryBreadcrumb(Category $category): string
    {
        $breadcrumb = [];
        $current = $category;

        while ($current) {
            array_unshift($breadcrumb, $current->name);
            $current = $current->parent;
        }

        return implode(' → ', $breadcrumb);
    }

    /**
     * Validación común para crear y actualizar libros
     */
    private function getValidationRules($bookId = null): array
    {
        $isbnRule = $bookId
            ? 'required|string|max:20|unique:books,isbn,' . $bookId
            : 'required|string|max:20|unique:books,isbn';

        return [
            'title' => 'required|string|max:255',
            'publisher_id' => 'nullable|exists:publishers,id',
            'isbn' => $isbnRule,
            'language_code' => 'required|string|max:5',
            'pages' => 'required|integer|min:1',
            'publication_year' => 'nullable|integer|min:1800|max:' . (date('Y') + 1),
            'book_type' => 'required|in:digital,physical,both',
            'featured' => 'boolean',
            'is_active' => 'boolean',
            'downloadable' => 'boolean',
            'description' => 'nullable|string',
            'edition' => 'nullable|string|max:255',
            'keywords' => 'nullable|string',
            'categories' => 'required|array|min:1',
            'categories.*' => 'exists:categories,id',
            'contributors' => 'required|array|min:1',
            'contributors.*.full_name' => 'required|string|max:255',
            'contributors.*.contributor_type' => 'required|in:author,editor,translator,illustrator,prologuist',
            'contributors.*.sequence_number' => 'integer|min:1',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'pdf_file' => 'nullable|file|mimes:pdf|max:10240',
        ];
    }

    /**
     * Procesar archivos subidos
     */
    private function processUploadedFiles(Request $request, Book $book = null): array
    {
        $coverImagePath = $book?->cover_image;
        $pdfFilePath = $book?->pdf_file;

        // Procesar cover image
        if ($request->hasFile('cover_image')) {
            // Eliminar cover anterior si existe
            if ($coverImagePath && Storage::disk('public')->exists($coverImagePath)) {
                Storage::disk('public')->delete($coverImagePath);
            }
            $coverImagePath = $request->file('cover_image')->store('covers', 'public');
        }

        // Procesar PDF file
        if ($request->hasFile('pdf_file')) {
            // Eliminar PDF anterior si existe
            if ($pdfFilePath && Storage::disk('public')->exists($pdfFilePath)) {
                Storage::disk('public')->delete($pdfFilePath);
            }
            $pdfFilePath = $request->file('pdf_file')->store('books', 'public');
        }

        return [
            'cover_image' => $coverImagePath,
            'pdf_file' => $pdfFilePath,
        ];
    }

    /**
     * Crear datos básicos del libro
     */
    private function getBookData(array $validated, array $filePaths, bool $isUpdate = false): array
    {
        $baseData = [
            'title' => $validated['title'],
            'publisher_id' => $validated['publisher_id'],
            'isbn' => $validated['isbn'],
            'language_code' => $validated['language_code'],
            'pages' => $validated['pages'],
            'publication_year' => $validated['publication_year'],
            'book_type' => $validated['book_type'],
            'featured' => $validated['featured'] ?? false,
            'is_active' => $validated['is_active'] ?? true,
            'downloadable' => $validated['downloadable'] ?? true,
            'cover_image' => $filePaths['cover_image'],
            'pdf_file' => $filePaths['pdf_file'],
            'published_at' => $validated['publication_year'] ? $validated['publication_year'] . '-01-01' : null,
        ];

        // Solo agregar campos de conteo para creación
        if (!$isUpdate) {
            $baseData = array_merge($baseData, [
                'physical_copies_count' => 0,
                'available_copies_count' => 0,
                'total_downloads' => 0,
                'total_views' => 0,
            ]);
        }

        return $baseData;
    }

    /**
     * Manejar detalles del libro
     */
    private function handleBookDetails(Book $book, array $validated): void
    {
        if ($book->details) {
            $book->details->update([
                'description' => $validated['description'] ?? null,
                'edition' => $validated['edition'] ?? '1ra',
                'keywords' => $validated['keywords'] ?? null,
            ]);
        } else {
            BookDetail::create([
                'book_id' => $book->id,
                'description' => $validated['description'] ?? null,
                'edition' => $validated['edition'] ?? '1ra',
                'file_format' => 'PDF',
                'keywords' => $validated['keywords'] ?? null,
            ]);
        }
    }

    /**
     * Manejar categorías del libro
     */
    private function handleBookCategories(Book $book, array $categories): void
    {
        $book->categories()->sync($categories);
    }

    /**
     * Manejar contribuidores del libro
     */
    private function handleBookContributors(Book $book, array $contributors): void
    {
        // Eliminar contribuidores existentes y crear nuevos
        $book->contributors()->delete();

        foreach ($contributors as $contributorData) {
            BookContributor::create([
                'book_id' => $book->id,
                'contributor_type' => $contributorData['contributor_type'],
                'full_name' => $contributorData['full_name'],
                'sequence_number' => $contributorData['sequence_number'] ?? 1,
            ]);
        }
    }

    /**
     * Limpiar archivos en caso de error
     */
    private function cleanupFiles(array $filePaths): void
    {
        foreach ($filePaths as $filePath) {
            if ($filePath && Storage::disk('public')->exists($filePath)) {
                Storage::disk('public')->delete($filePath);
            }
        }
    }

    /**
     * Ejecutar operación de libro con transacción
     */
    private function executeBookOperation(callable $operation, string $successMessage)
    {
        try {
            DB::beginTransaction();

            $result = $operation();

            DB::commit();

            // SIEMPRE retornar una respuesta de Inertia o redirect
            if ($result instanceof \Illuminate\Http\RedirectResponse) {
                return $result;
            }

            // Si no es una redirect response, redirigir al índice
            return redirect()->route('admin.books.index')
                ->with('success', $successMessage);

        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Error en operación de libro: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());

            return back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->getValidationRules());

        return $this->executeBookOperation(
            function () use ($validated, $request) {
                // Procesar archivos
                $filePaths = $this->processUploadedFiles($request);

                // Crear libro
                $book = Book::create($this->getBookData($validated, $filePaths));

                // Manejar relaciones
                $this->handleBookDetails($book, $validated);
                $this->handleBookCategories($book, $validated['categories']);
                $this->handleBookContributors($book, $validated['contributors']);

                // NO retornar el libro, dejar que executeBookOperation maneje la respuesta
                return null;
            },
            'Libro creado exitosamente.'
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate($this->getValidationRules($book->id));

        return $this->executeBookOperation(
            function () use ($validated, $request, $book) {
                // Procesar archivos (manteniendo los existentes si no se suben nuevos)
                $filePaths = $this->processUploadedFiles($request, $book);

                // Actualizar libro
                $book->update($this->getBookData($validated, $filePaths, true));

                // Manejar relaciones
                $this->handleBookDetails($book, $validated);
                $this->handleBookCategories($book, $validated['categories']);
                $this->handleBookContributors($book, $validated['contributors']);

                // NO retornar el libro, dejar que executeBookOperation maneje la respuesta
                return null;
            },
            'Libro actualizado exitosamente.'
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book)
    {
        return $this->executeBookOperation(
            function () use ($book) {
                // Eliminar archivos físicos
                $this->cleanupFiles([
                    $book->cover_image,
                    $book->pdf_file
                ]);

                // Eliminar relaciones
                $book->categories()->detach();
                $book->contributors()->delete();
                $book->physicalCopies()->delete();

                if ($book->details) {
                    $book->details()->delete();
                }

                // Eliminar el libro
                $book->delete();

                // NO retornar el libro, dejar que executeBookOperation maneje la respuesta
                return null;
            },
            'Libro eliminado exitosamente.'
        );
    }

    // ===============================================
    // MÉTODOS PRIVADOS - QUERIES
    // ===============================================

    /**
     * Construir query base para libros con relaciones
     * 
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function buildBooksQuery()
    {
        return Book::with(['publisher', 'categories', 'details'])
            ->withCount(['downloads', 'physicalCopies', 'loans']);
    }

    /**
     * Obtener categorías seleccionables (solo hojas)
     * 
     * @return \Illuminate\Support\Collection
     */
    private function getSelectableCategories()
    {
        return Category::where('is_active', true)
            ->with('parent')
            ->get()
            ->filter(function ($category) {
                return $category->isLeaf();
            })
            ->map(function ($category) {
                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'full_path' => $this->getCategoryFullPath($category),
                    'breadcrumb' => $this->getCategoryBreadcrumb($category)
                ];
            })
            ->sortBy('full_path')
            ->values();
    }

    // ===============================================
    // MÉTODOS PRIVADOS - FILTROS
    // ===============================================

    /**
     * Aplicar filtros a la query de libros
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return void
     */
    private function applyFilters($query, Request $request): void
    {
        $this->applySearchFilter($query, $request->input('search'));
        $this->applyCategoryFilter($query, $request->input('category'));
        $this->applyBookTypeFilter($query, $request->input('book_type'));
        $this->applyStatusFilter($query, $request->input('status'));
    }

    /**
     * Aplicar filtro de búsqueda
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
            $q->where('title', 'like', "%{$search}%")
                ->orWhere('isbn', 'like', "%{$search}%")
                ->orWhereHas('contributors', function ($q) use ($search) {
                    $q->where('full_name', 'like', "%{$search}%");
                });
        });
    }

    /**
     * Aplicar filtro de categoría
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
     * Aplicar filtro de tipo de libro
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $bookType
     * @return void
     */
    private function applyBookTypeFilter($query, ?string $bookType): void
    {
        if (empty($bookType)) {
            return;
        }

        $query->where('book_type', $bookType);
    }

    /**
     * Aplicar filtro de estado
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|null $status
     * @return void
     */
    private function applyStatusFilter($query, ?string $status): void
    {
        if (empty($status)) {
            return;
        }

        switch ($status) {
            case self::STATUS_ACTIVE:
                $query->where('is_active', true);
                break;
            case self::STATUS_INACTIVE:
                $query->where('is_active', false);
                break;
            case self::STATUS_FEATURED:
                $query->where('featured', true);
                break;
        }
    }

    /**
     * Aplicar ordenamiento a la query
     * 
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param Request $request
     * @return void
     */
    private function applySorting($query, Request $request): void
    {
        $sortField = $request->get('sort_field', self::DEFAULT_SORT_FIELD);
        $sortDirection = $request->get('sort_direction', self::DEFAULT_SORT_DIRECTION);

        if (in_array($sortField, self::ALLOWED_SORT_FIELDS)) {
            $query->orderBy($sortField, $sortDirection);
        }
    }

    // ===============================================
    // MÉTODOS PRIVADOS - HELPERS
    // ===============================================

    /**
     * Extraer filtros del request
     * 
     * @param Request $request
     * @return array
     */
    private function extractFilters(Request $request): array
    {
        return $request->only(['search', 'category', 'book_type', 'status']);
    }

    /**
     * Extraer parámetros de ordenamiento
     * 
     * @param Request $request
     * @return array
     */
    private function extractSortParams(Request $request): array
    {
        return [
            'field' => $request->get('sort_field', self::DEFAULT_SORT_FIELD),
            'direction' => $request->get('sort_direction', self::DEFAULT_SORT_DIRECTION),
        ];
    }

    /**
     * Calcular estadísticas del catálogo
     * 
     * @return array
     */
    private function calculateStats(): array
    {
        return [
            'total_books' => Book::count(),
            'active_books' => Book::where('is_active', true)->count(),
            'digital_books' => Book::where('book_type', self::BOOK_TYPE_DIGITAL)->count(),
            'physical_books' => Book::where('book_type', self::BOOK_TYPE_PHYSICAL)->count(),
            'both_types' => Book::where('book_type', self::BOOK_TYPE_BOTH)->count(),
            'total_physical_copies' => PhysicalCopy::count(),
            'available_copies' => PhysicalCopy::where('status', 'available')->count(),
        ];
    }

    /**
     * Obtener estadísticas de un libro específico
     * 
     * @param Book $book
     * @return array
     */
    private function getBookStats(Book $book): array
    {
        return [
            'total_downloads' => $book->downloads()->count(),
            'total_loans' => $book->loans()->count(),
            'total_views' => $book->total_views,
        ];
    }

    /**
     * Obtener tipos de libro disponibles
     * 
     * @return array
     */
    private function getBookTypes(): array
    {
        return [
            ['value' => self::BOOK_TYPE_DIGITAL, 'label' => 'Digital'],
            ['value' => self::BOOK_TYPE_PHYSICAL, 'label' => 'Físico'],
            ['value' => self::BOOK_TYPE_BOTH, 'label' => 'Ambos'],
        ];
    }
}
