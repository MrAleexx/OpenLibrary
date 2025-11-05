<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Category;
use App\Models\Publisher;
use App\Models\Language;
use App\Models\BookDetail;
use App\Models\BookContributor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $query = Book::with(['publisher', 'categories', 'details'])
            ->withCount(['downloads', 'physicalCopies', 'loans']);

        // Búsqueda
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('isbn', 'like', "%{$search}%")
                    ->orWhereHas('contributors', function ($q) use ($search) {
                        $q->where('full_name', 'like', "%{$search}%");
                    });
            });
        }

        // Filtros
        if ($request->has('category') && $request->category) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category);
            });
        }

        if ($request->has('book_type') && $request->book_type) {
            $query->where('book_type', $request->book_type);
        }

        if ($request->has('status') && $request->status) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'featured') {
                $query->where('featured', true);
            }
        }

        // Ordenamiento
        $sortField = $request->get('sort_field', 'created_at');
        $sortDirection = $request->get('sort_direction', 'desc');

        $allowedSortFields = ['title', 'created_at', 'total_downloads', 'total_views', 'publication_year'];
        if (in_array($sortField, $allowedSortFields)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $books = $query->paginate(12)->withQueryString();

        return Inertia::render('admin/books/Index', [
            'books' => $books,
            'filters' => $request->only(['search', 'category', 'book_type', 'status']),
            'sort' => ['field' => $sortField, 'direction' => $sortDirection],
            'categories' => Category::where('is_active', true)->get(),
            'stats' => [
                'total_books' => Book::count(),
                'active_books' => Book::where('is_active', true)->count(),
                'digital_books' => Book::where('book_type', 'digital')->count(),
                'physical_books' => Book::where('book_type', 'physical')->count(),
                'both_types' => Book::where('book_type', 'both')->count(),
                'total_physical_copies' => \App\Models\PhysicalCopy::count(),
                'available_copies' => \App\Models\PhysicalCopy::where('status', 'available')->count(),
            ]
        ]);
    }

    public function create()
    {
        // Obtener solo categorías "hoja" (sin subcategorías) con su ruta completa
        $selectableCategories = Category::where('is_active', true)
            ->with('parent') // Cargar parent para construir la ruta
            ->get()
            ->filter(function ($category) {
                return $category->isLeaf(); // Solo categorías sin hijos
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

        return Inertia::render('admin/books/Create', [
            'categories' => $selectableCategories,
            'publishers' => Publisher::where('is_active', true)->get(),
            'languages' => Language::where('is_active', true)->get(),
            'book_types' => [
                ['value' => 'digital', 'label' => 'Digital'],
                ['value' => 'physical', 'label' => 'Físico'],
                ['value' => 'both', 'label' => 'Ambos'],
            ],
        ]);
    }

    public function show(Book $book)
    {
        $book->load([
            'publisher',
            'categories',
            'details',
            'contributors',
            'physicalCopies',
            'downloads' => function ($query) {
                $query->limit(10)->latest();
            }
        ]);

        return Inertia::render('admin/books/Show', [
            'book' => $book,
            'stats' => [
                'total_downloads' => $book->downloads()->count(),
                'total_loans' => $book->loans()->count(),
                'total_views' => $book->total_views,
            ]
        ]);
    }

    public function edit(Book $book)
    {
        $book->load(['categories', 'contributors', 'details']);

        // Obtener solo categorías "hoja" (sin subcategorías) con su ruta completa
        $selectableCategories = Category::where('is_active', true)
            ->with('parent')
            ->get()
            ->filter(function ($category) {
                return $category->isLeaf(); // Solo categorías sin hijos
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

        return Inertia::render('admin/books/Edit', [
            'book' => $book,
            'categories' => $selectableCategories,
            'publishers' => Publisher::where('is_active', true)->get(),
            'languages' => Language::where('is_active', true)->get(),
            'book_types' => [
                ['value' => 'digital', 'label' => 'Digital'],
                ['value' => 'physical', 'label' => 'Físico'],
                ['value' => 'both', 'label' => 'Ambos'],
            ],
        ]);
    }

    public function toggleFeatured(Book $book)
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
}
