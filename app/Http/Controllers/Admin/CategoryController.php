<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Support\Facades\DB;

/**
 * Controlador de administración de categorías
 * 
 * Gestiona CRUD completo de categorías jerárquicas incluyendo:
 * - Listado en vista de tabla o árbol
 * - Creación y edición con soporte para subcategorías
 * - Reordenamiento mediante drag & drop
 * - Activación/desactivación
 * - Validación de categorías hoja para asignación de libros
 */
class CategoryController extends Controller
{
    // ===============================================
    // CONSTANTES
    // ===============================================

    /**
     * Tipos de vista disponibles
     */
    private const VIEW_TYPE_TABLE = 'table';
    private const VIEW_TYPE_TREE = 'tree';
    private const DEFAULT_VIEW_TYPE = self::VIEW_TYPE_TABLE;

    /**
     * Posiciones de reordenamiento
     */
    private const POSITION_BEFORE = 'before';
    private const POSITION_AFTER = 'after';
    private const POSITION_INSIDE = 'inside';

    // ===============================================
    // MÉTODOS PÚBLICOS
    // ===============================================

    /**
     * Listar categorías en vista de tabla o árbol
     * 
     * @param Request $request Parámetro de tipo de vista
     * @return Response Vista Inertia con categorías
     */
    public function index(Request $request): Response
    {
        $viewType = $request->get('view', self::DEFAULT_VIEW_TYPE);

        $categories = $this->loadCategories($viewType);

        return Inertia::render('admin/categories/Index', [
            'categories' => $categories,
            'viewType' => $viewType,
        ]);
    }

    /**
     * Reordenar categorías mediante drag & drop
     * 
     * @param Request $request Datos de reordenamiento
     * @return RedirectResponse
     */
    public function reorder(Request $request): RedirectResponse
    {
        $request->validate([
            'draggedId' => 'required|exists:categories,id',
            'targetId' => 'required|exists:categories,id',
            'position' => 'required|in:' . self::POSITION_BEFORE . ',' . self::POSITION_AFTER . ',' . self::POSITION_INSIDE,
            'newParentId' => 'nullable|exists:categories,id'
        ]);

        $draggedCategory = Category::find($request->draggedId);
        $targetCategory = Category::find($request->targetId);
        $newParentId = $request->position === self::POSITION_INSIDE ? $request->targetId : $request->newParentId;

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

    // ===============================================
    // MÉTODOS PRIVADOS - QUERIES
    // ===============================================

    /**
     * Cargar categorías según tipo de vista
     * 
     * @param string $viewType Tipo de vista (table o tree)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function loadCategories(string $viewType)
    {
        if ($viewType === self::VIEW_TYPE_TREE) {
            return $this->loadCategoriesForTree();
        }

        return $this->loadCategoriesForTable();
    }

    /**
     * Cargar categorías para vista de árbol
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function loadCategoriesForTree()
    {
        return Category::with(['children' => function ($query) {
                $query->ordered()->withCount(['books', 'children']);
            }])
            ->with(['parent'])
            ->withCount(['books', 'children'])
            ->ordered()
            ->get();
    }

    /**
     * Cargar categorías para vista de tabla
     * 
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function loadCategoriesForTable()
    {
        return Category::with(['parent'])
            ->withCount(['books', 'children'])
            ->ordered()
            ->get();
    }

    // ===============================================
    // MÉTODOS PRIVADOS - HELPERS
    // ===============================================

    /**
     * Recalcular orden de categorías tras reordenamiento
     * 
     * @param Category $dragged Categoría arrastrada
     * @param Category $target Categoría objetivo
     * @param string $position Posición (before, after, inside)
     * @return void
     */
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
     * Normalizar órdenes a enteros secuenciales
     * 
     * @param int|null $parentId ID de categoría padre
     * @return void
     */
    private function normalizeSortOrders(?int $parentId = null): void
    {
        $categories = Category::where('parent_id', $parentId)
            ->ordered()
            ->get();
        
        foreach ($categories as $index => $category) {
            $category->update(['sort_order' => $index + 1]);
        }
    }

    /**
     * Obtener historial de cambios de categoría
     * 
     * @param Category $category
     * @return Response
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
     * Mostrar formulario de creación de categoría
     * 
     * @return Response
     */
    public function create(): Response
    {
        $parentCategories = $this->getParentCategoriesForSelection();
        $nextSortOrder = $this->calculateNextSortOrder();

        return Inertia::render('admin/categories/Create', [
            'parentCategories' => $parentCategories,
            'nextSortOrder' => $nextSortOrder,
        ]);
    }

    /**
     * Almacenar nueva categoría
     * 
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateCategoryData($request);

        // Si el orden ya existe, desplazar las demás categorías
        $this->adjustSortOrdersForInsertion($validated['sort_order']);

        Category::create($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría creada exitosamente.');
    }

    /**
     * Mostrar formulario de edición de categoría
     * 
     * @param Category $category
     * @return Response
     */
    public function edit(Category $category): Response
    {
        $parentCategories = $this->getParentCategoriesForSelection($category->id);
        $maxSortOrder = Category::max('sort_order');

        return Inertia::render('admin/categories/Edit', [
            'category' => $this->formatCategoryForEdit($category),
            'parentCategories' => $parentCategories,
            'availableOrders' => range(1, $maxSortOrder + 1),
            'maxSortOrder' => $maxSortOrder + 1,
        ]);
    }

    /**
     * Actualizar categoría existente
     * 
     * @param Request $request
     * @param Category $category
     * @return RedirectResponse
     */
    public function update(Request $request, Category $category): RedirectResponse
    {
        $validated = $this->validateCategoryData($request, $category->id);

        // Si cambió el orden, reorganizar las categorías
        if ($validated['sort_order'] != $category->sort_order) {
            $this->adjustSortOrdersForUpdate($category, $validated['sort_order']);
        }

        $category->update($validated);

        return redirect()->route('admin.categories.index')
            ->with('success', 'Categoría actualizada exitosamente.');
    }

    /**
     * Eliminar categoría
     * 
     * @param Category $category
     * @return RedirectResponse
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
     * Alternar estado activo/inactivo de categoría
     * 
     * @param Category $category
     * @return RedirectResponse
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

    // ===============================================
    // MÉTODOS PRIVADOS - VALIDACIÓN
    // ===============================================

    /**
     * Validar datos de categoría
     * 
     * @param Request $request
     * @param int|null $categoryId ID de categoría para actualización
     * @return array
     */
    private function validateCategoryData(Request $request, ?int $categoryId = null): array
    {
        $slugRule = $categoryId 
            ? 'required|string|max:255|unique:categories,slug,' . $categoryId
            : 'required|string|max:255|unique:categories,slug';

        return $request->validate([
            'name' => 'required|string|max:255',
            'slug' => $slugRule,
            'description' => 'nullable|string',
            'parent_id' => 'nullable|exists:categories,id',
            'sort_order' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'image' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ]);
    }

    // ===============================================
    // MÉTODOS PRIVADOS - FORMATEO DE DATOS
    // ===============================================

    /**
     * Formatear categoría para edición
     * 
     * @param Category $category
     * @return array
     */
    private function formatCategoryForEdit(Category $category): array
    {
        return [
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
        ];
    }

    /**
     * Obtener categorías padre para selección
     * 
     * @param int|null $excludeId ID de categoría a excluir
     * @return \Illuminate\Database\Eloquent\Collection
     */
    private function getParentCategoriesForSelection(?int $excludeId = null)
    {
        $query = Category::whereNull('parent_id')
            ->active()
            ->ordered();

        if ($excludeId) {
            $query->where('id', '!=', $excludeId);
        }

        return $query->get(['id', 'name']);
    }

    /**
     * Calcular siguiente orden disponible
     * 
     * @return int
     */
    private function calculateNextSortOrder(): int
    {
        return Category::max('sort_order') + 1;
    }

    /**
     * Ajustar órdenes al insertar nueva categoría
     * 
     * @param int $sortOrder
     * @return void
     */
    private function adjustSortOrdersForInsertion(int $sortOrder): void
    {
        $existingCategory = Category::where('sort_order', $sortOrder)->first();
        
        if ($existingCategory) {
            Category::where('sort_order', '>=', $sortOrder)
                ->increment('sort_order');
        }
    }

    /**
     * Ajustar órdenes al actualizar categoría
     * 
     * @param Category $category
     * @param int $newSortOrder
     * @return void
     */
    private function adjustSortOrdersForUpdate(Category $category, int $newSortOrder): void
    {
        if ($newSortOrder > $category->sort_order) {
            // Moviendo hacia abajo
            Category::where('sort_order', '>', $category->sort_order)
                ->where('sort_order', '<=', $newSortOrder)
                ->decrement('sort_order');
        } else {
            // Moviendo hacia arriba
            Category::where('sort_order', '>=', $newSortOrder)
                ->where('sort_order', '<', $category->sort_order)
                ->increment('sort_order');
        }
    }
}
