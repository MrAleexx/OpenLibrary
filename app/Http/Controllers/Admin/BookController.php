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
        return Inertia::render('admin/books/Create', [
            'categories' => Category::where('is_active', true)->get(),
            'publishers' => Publisher::where('is_active', true)->get(),
            'languages' => Language::where('is_active', true)->get(),
            'book_types' => [
                ['value' => 'digital', 'label' => 'Digital'],
                ['value' => 'physical', 'label' => 'Físico'],
                ['value' => 'both', 'label' => 'Ambos'],
            ],
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'publisher_id' => 'nullable|exists:publishers,id',
            'isbn' => 'required|string|max:20|unique:books,isbn',
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
        ]);

        try {
            DB::beginTransaction();

            // Crear el libro
            $book = Book::create([
                'title' => $request->title,
                'publisher_id' => $request->publisher_id,
                'isbn' => $request->isbn,
                'language_code' => $request->language_code,
                'pages' => $request->pages,
                'publication_year' => $request->publication_year,
                'book_type' => $request->book_type,
                'featured' => $request->featured ?? false,
                'is_active' => $request->is_active ?? true,
                'downloadable' => $request->downloadable ?? true,
                'published_at' => $request->publication_year ? $request->publication_year . '-01-01' : null,
            ]);

            // Crear detalles del libro
            BookDetail::create([
                'book_id' => $book->id,
                'description' => $request->description,
                'edition' => $request->edition ?? '1ra',
                'keywords' => $request->keywords,
            ]);

            // Asignar categorías
            $book->categories()->attach($request->categories);

            // Crear contribuidores
            foreach ($request->contributors as $contributorData) {
                BookContributor::create([
                    'book_id' => $book->id,
                    'contributor_type' => $contributorData['contributor_type'],
                    'full_name' => $contributorData['full_name'],
                    'sequence_number' => $contributorData['sequence_number'] ?? 1,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.books.index')
                ->with('success', 'Libro creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al crear el libro: ' . $e->getMessage());
        }
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

        return Inertia::render('admin/books/Edit', [
            'book' => $book,
            'categories' => Category::where('is_active', true)->get(),
            'publishers' => Publisher::where('is_active', true)->get(),
            'languages' => Language::where('is_active', true)->get(),
            'book_types' => [
                ['value' => 'digital', 'label' => 'Digital'],
                ['value' => 'physical', 'label' => 'Físico'],
                ['value' => 'both', 'label' => 'Ambos'],
            ],
        ]);
    }

    public function update(Request $request, Book $book)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'publisher_id' => 'nullable|exists:publishers,id',
            'isbn' => 'required|string|max:20|unique:books,isbn,' . $book->id,
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
        ]);

        try {
            DB::beginTransaction();

            // Actualizar el libro
            $book->update([
                'title' => $request->title,
                'publisher_id' => $request->publisher_id,
                'isbn' => $request->isbn,
                'language_code' => $request->language_code,
                'pages' => $request->pages,
                'publication_year' => $request->publication_year,
                'book_type' => $request->book_type,
                'featured' => $request->featured ?? false,
                'is_active' => $request->is_active ?? true,
                'downloadable' => $request->downloadable ?? true,
                'published_at' => $request->publication_year ? $request->publication_year . '-01-01' : null,
            ]);

            // Actualizar o crear detalles del libro
            if ($book->details) {
                $book->details->update([
                    'description' => $request->description,
                    'edition' => $request->edition ?? '1ra',
                    'keywords' => $request->keywords,
                ]);
            } else {
                BookDetail::create([
                    'book_id' => $book->id,
                    'description' => $request->description,
                    'edition' => $request->edition ?? '1ra',
                    'keywords' => $request->keywords,
                ]);
            }

            // Sincronizar categorías
            $book->categories()->sync($request->categories);

            // Sincronizar contribuidores
            $book->contributors()->delete();
            foreach ($request->contributors as $contributorData) {
                BookContributor::create([
                    'book_id' => $book->id,
                    'contributor_type' => $contributorData['contributor_type'],
                    'full_name' => $contributorData['full_name'],
                    'sequence_number' => $contributorData['sequence_number'] ?? 1,
                ]);
            }

            DB::commit();

            return redirect()->route('admin.books.index')
                ->with('success', 'Libro actualizado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al actualizar el libro: ' . $e->getMessage());
        }
    }

    public function destroy(Book $book)
    {
        try {
            DB::beginTransaction();

            // Eliminar relaciones
            $book->categories()->detach();
            $book->contributors()->delete();
            $book->physicalCopies()->delete();

            if ($book->details) {
                $book->details()->delete();
            }

            // Eliminar el libro
            $book->delete();

            DB::commit();

            return redirect()->route('admin.books.index')
                ->with('success', 'Libro eliminado exitosamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al eliminar el libro: ' . $e->getMessage());
        }
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
}
