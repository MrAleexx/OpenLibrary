<?php
// app/Http/Controllers/Admin/CategoryController.php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;


class CategoryController extends Controller
{

    /**
     * Display a listing of the categories.
     */
    public function index(Request $request): Response
    {
        $viewType = $request->get('view', 'table');

        if ($viewType === 'tree') {
            // Para vista de árbol, cargar TODAS las categorías con relaciones anidadas
            $categories = Category::with(['children' => function ($query) {
                $query->ordered()->withCount(['books', 'children']);
            }])
            ->with(['parent'])
            ->withCount(['books', 'children'])
            ->ordered()
            ->get();
        } else {
            // Para vista de tabla, carga plana
            $categories = Category::with(['parent'])
                ->withCount(['books', 'children'])
                ->ordered()
                ->get();
        }

        return Inertia::render('admin/categories/Index', [
            'categories' => $categories,
            'viewType' => $viewType,
        ]);
    }

    /*
    Reorder categories via drag and drop
      */
    // En CategoryController.php - Mejorar el método reorder
    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'draggedId' => 'required|exists:categories,id',
            'targetId' => 'required|exists:categories,id',
            'position' => 'required|in:before,after,inside',
            'newParentId' => 'nullable|exists:categories,id'
        ]);

        $draggedCategory = Category::find($request->draggedId);
        $targetCategory = Category::find($request->targetId);
        $newParentId = $request->position === 'inside' ? $request->targetId : $request->newParentId;

        DB::transaction(function () use ($draggedCategory, $targetCategory, $request, $newParentId) {
            // 1. Actualizar parent_id si es necesario
            if ($draggedCategory->parent_id != $newParentId) {
                $draggedCategory->update(['parent_id' => $newParentId]);
            }

            // 2. Recalcular sort_order basado en la posición
            $this->recalculateSortOrder($draggedCategory, $targetCategory, $request->position);
        });

        return redirect()->back()->with('success', 'Categoría reordenada exitosamente.');
    }

    private function recalculateSortOrder($dragged, $target, $position): void
    {
        // Obtener todos los hermanos (incluyendo el arrastrado)
        $siblings = Category::where('parent_id', $dragged->parent_id)
            ->where('id', '!=', $dragged->id)
            ->ordered()
            ->get();

        $newOrder = 1;
        $inserted = false;

        foreach ($siblings as $sibling) {
            // Insertar antes del target
            if ($sibling->id == $target->id && $position === 'before') {
                $dragged->update(['sort_order' => $newOrder++]);
                $inserted = true;
            }
            
            // Actualizar el orden del hermano actual
            if ($sibling->sort_order != $newOrder) {
                $sibling->update(['sort_order' => $newOrder]);
            }
            $newOrder++;
            
            // Insertar después del target
            if ($sibling->id == $target->id && $position === 'after') {
                $dragged->update(['sort_order' => $newOrder++]);
                $inserted = true;
            }
        }

        // Si no se insertó (por ejemplo, cuando se mueve dentro de una categoría vacía)
        if (!$inserted) {
            $dragged->update(['sort_order' => $newOrder]);
        }

        // Normalizar órdenes para evitar decimales
        $this->normalizeSortOrders($dragged->parent_id);
    }

    /**
     * Normalize sort orders to integers
     */
    private function normalizeSortOrders($parentId = null): void
    {
        $categories = Category::where('parent_id', $parentId)
            ->ordered()
            ->get();
        
        foreach ($categories as $index => $category) {
            $category->update(['sort_order' => $index + 1]);
        }
    }

    /**
     * Get category history
     */
    public function history(Category $category): Response
    {
        // Aquí implementarías la lógica para obtener el historial
        // Por ahora retornamos datos de ejemplo
        $history = [
            [
                'id' => 1,
                'action' => 'created',
                'description' => 'Categoría creada',
                'user' => ['name' => 'Administrador'],
                'created_at' => now()->subDays(2)->format('Y-m-d H:i:s')
            ]
        ];

        return Inertia::render('admin/categories/History', [
            'category' => $category,
            'history' => $history
        ]);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): Response
    {
        $parentCategories = Category::whereNull('parent_id')
            ->active()
            ->ordered()
            ->get(['id', 'name']);

        // Calcular el siguiente orden disponible
        $nextSortOrder = Category::max('sort_order') + 1;

        return Inertia::render('admin/categories/Create', [
            'parentCategories' => $parentCategories,
            'nextSortOrder' => $nextSortOrder,
        ]);
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug',
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        // Si el orden ya existe, desplazar las demás categorías
        $existingCategory = Category::where('sort_order', $validated['sort_order'])->first();
        if ($existingCategory) {
            Category::where('sort_order', '>=', $validated['sort_order'])
                ->increment('sort_order');
        }

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría creada exitosamente.');
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(Category $category): Response
    {
        $parentCategories = Category::whereNull('parent_id')
            ->active()
            ->ordered()
            ->where('id', '!=', $category->id)
            ->get(['id', 'name']);

        // Obtener todos los órdenes disponibles para el dropdown
        $maxSortOrder = Category::max('sort_order');
        $availableOrders = range(1, $maxSortOrder + 1);

        return Inertia::render('admin/categories/Edit', [
            'category' => [
                'id' => $category->id,
                'name' => $category->name,
                'slug' => $category->slug,
                'description' => $category->description,
                'parent_id' => $category->parent_id,
                'sort_order' => $category->sort_order,
                'is_active' => $category->is_active,
                'image' => $category->image,
                'meta_title' => $category->meta_title,
                'meta_description' => $category->meta_description,
                'created_at' => $category->created_at ? $category->created_at->format('Y-m-d H:i') : 'N/A',
                'updated_at' => $category->updated_at ? $category->updated_at->format('Y-m-d H:i') : 'N/A',
            ],
            'parentCategories' => $parentCategories,
            'availableOrders' => $availableOrders,
            'maxSortOrder' => $maxSortOrder + 1,
        ]);
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:categories,slug,' . $category->id,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);

        // Si cambió el orden, reorganizar las categorías
        if ($validated['sort_order'] != $category->sort_order) {
            if ($validated['sort_order'] > $category->sort_order) {
                // Moviendo hacia abajo
                Category::where('sort_order', '>', $category->sort_order)
                    ->where('sort_order', '<=', $validated['sort_order'])
                    ->decrement('sort_order');
            } else {
                // Moviendo hacia arriba
                Category::where('sort_order', '>=', $validated['sort_order'])
                    ->where('sort_order', '<', $category->sort_order)
                    ->increment('sort_order');
            }
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category): RedirectResponse
    {
        // Verificar si la categoría tiene subcategorías
        if ($category->children()->count() > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar una categoría que tiene subcategorías.');
        }

        // Verificar si la categoría tiene libros asociados
        if ($category->books()->count() > 0) {
            return redirect()->back()
                ->with('error', 'No se puede eliminar una categoría que tiene libros asociados.');
        }

        // Antes de eliminar, ajustar los órdenes de las demás categorías
        Category::where('sort_order', '>', $category->sort_order)
            ->decrement('sort_order');

        $category->delete();

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría eliminada exitosamente.');
    }

    /**
     * Toggle category status
     */
    public function toggleStatus(Category $category): RedirectResponse
    {
        $category->update([
            'is_active' => !$category->is_active
        ]);

        $status = $category->is_active ? 'activada' : 'desactivada';

        return redirect()->back()
            ->with('success', "Categoría {$status} exitosamente.");
    }

}
