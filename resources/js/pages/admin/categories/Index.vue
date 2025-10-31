<!-- resources/js/pages/admin/categories/Index.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { BreadcrumbItem, Category } from '@/types'; 
import CategoryTreeNode from '@/components/CategoryTreeNode.vue';
import { 
    Plus, Edit, Trash2, Folder, FolderOpen, Eye, EyeOff, Search, 
    Filter, ListTree, Table, GripVertical, History, X 
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Props {
    categories: Category[];
    viewType?: 'table' | 'tree';
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Categorías', href: '/admin/categories' },
];

// Estados reactivos
const searchQuery = ref('');
const filterStatus = ref('all');
const filterType = ref('all');
const viewMode = ref(props.viewType || 'table');
const selectedCategory = ref<Category | null>(null);
const showDetailModal = ref(false);
const showHistoryModal = ref(false);
const categoryHistory = ref<any[]>([]);

// Computed: Categorías filtradas y buscadas
const filteredCategories = computed(() => {
    let filtered = props.categories;

    // Filtro por búsqueda
    if (searchQuery.value) {
        filtered = filtered.filter(category =>
            category.name.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            category.slug.toLowerCase().includes(searchQuery.value.toLowerCase()) ||
            (category.description && category.description.toLowerCase().includes(searchQuery.value.toLowerCase()))
        );
    }

    // Filtro por estado
    if (filterStatus.value !== 'all') {
        filtered = filtered.filter(category =>
            filterStatus.value === 'active' ? category.is_active : !category.is_active
        );
    }

    // Filtro por tipo
    if (filterType.value !== 'all') {
        filtered = filtered.filter(category =>
            filterType.value === 'parent' ? category.parent_id === null : category.parent_id !== null
        );
    }

    return filtered;
});

// Computed: Categorías organizadas en árbol (solo categorías raíz)
const rootCategories = computed(() => {
    return props.categories.filter(cat => cat.parent_id === null)
        .sort((a, b) => a.sort_order - b.sort_order);
});

// Funciones
const deleteCategory = (category: Category) => {
    if (confirm(`¿Estás seguro de que quieres eliminar la categoría "${category.name}"?`)) {
        router.delete(`/admin/categories/${category.id}`);
    }
};

const toggleStatus = (category: Category) => {
    router.patch(`/admin/categories/${category.id}/toggle-status`);
};

const viewDetails = (category: Category) => {
    selectedCategory.value = category;
    showDetailModal.value = true;
};

const viewHistory = async (category: Category) => {
    selectedCategory.value = category;
    categoryHistory.value = [
        {
            id: 1,
            action: 'created',
            description: 'Categoría creada',
            user: { name: 'Administrador' },
            created_at: '2024-01-15 10:30:00'
        }
    ];
    showHistoryModal.value = true;
};

// Drag and Drop para reordenar
const onDragStart = (event: DragEvent, category: Category) => {
    if (event.dataTransfer) {
        event.dataTransfer.setData('text/plain', category.id.toString());
        event.dataTransfer.effectAllowed = 'move';
    }
};

const onDragOver = (event: DragEvent) => {
    event.preventDefault();
    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
    }
};

const onDrop = async (event: DragEvent, targetCategory: Category, position: 'before' | 'after' | 'inside') => {
    event.preventDefault();
    const draggedCategoryId = event.dataTransfer?.getData('text/plain');
    
    if (!draggedCategoryId) return;

    // Evitar que una categoría se mueva dentro de sí misma
    if (position === 'inside' && parseInt(draggedCategoryId) === targetCategory.id) {
        return;
    }

    try {
        await router.patch('/admin/categories/reorder', {
            draggedId: parseInt(draggedCategoryId),
            targetId: targetCategory.id,
            position: position,
            newParentId: position === 'inside' ? targetCategory.id : targetCategory.parent_id
        });
        
        // Recargar para ver los cambios
        router.reload();
    } catch (error) {
        console.error('Error reordenando categoría:', error);
        alert('Error al reordenar la categoría');
    }
};

// Handlers específicos para las diferentes zonas
const onDropBefore = (event: DragEvent, category: Category) => onDrop(event, category, 'before');
const onDropAfter = (event: DragEvent, category: Category) => onDrop(event, category, 'after');
const onDropInside = (event: DragEvent, category: Category) => onDrop(event, category, 'inside');
</script>

<template>
    <Head title="Gestión de Categorías" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold text-foreground">Gestión de Categorías</h1>
                    <p class="text-muted-foreground mt-2">
                        Administra las categorías y subcategorías de tu biblioteca
                    </p>
                </div>
                <Link
                    href="/admin/categories/create"
                    class="bg-primary text-primary-foreground px-4 py-2 rounded-lg flex items-center gap-2 hover:bg-primary/90 transition-colors shadow-lg shadow-primary/25"
                >
                    <Plus class="w-4 h-4" />
                    Nueva Categoría
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="bg-card rounded-xl border border-border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Total Categorías</p>
                            <p class="text-2xl font-bold text-foreground mt-1">{{ categories.length }}</p>
                        </div>
                        <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                            <Folder class="w-6 h-6 text-primary" />
                        </div>
                    </div>
                </div>

                <div class="bg-card rounded-xl border border-border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Categorías Activas</p>
                            <p class="text-2xl font-bold text-foreground mt-1">
                                {{ categories.filter(c => c.is_active).length }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-success/10 rounded-lg flex items-center justify-center">
                            <Eye class="w-6 h-6 text-success" />
                        </div>
                    </div>
                </div>

                <div class="bg-card rounded-xl border border-border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Subcategorías</p>
                            <p class="text-2xl font-bold text-foreground mt-1">
                                {{ categories.filter(c => c.parent_id !== null).length }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-secondary/10 rounded-lg flex items-center justify-center">
                            <FolderOpen class="w-6 h-6 text-secondary" />
                        </div>
                    </div>
                </div>

                <div class="bg-card rounded-xl border border-border p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Promedio Libros</p>
                            <p class="text-2xl font-bold text-foreground mt-1">
                                {{ Math.round(categories.reduce((acc, c) => acc + c.books_count, 0) / categories.length) || 0 }}
                            </p>
                        </div>
                        <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                            <span class="text-lg font-bold text-primary">📚</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls Bar -->
            <div class="bg-card rounded-xl border border-border p-4">
                <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
                    <!-- Search -->
                    <div class="flex-1 w-full lg:max-w-md">
                        <div class="relative">
                            <Search class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 transform -translate-y-1/2" />
                            <input
                                v-model="searchQuery"
                                type="text"
                                placeholder="Buscar categorías por nombre, slug o descripción..."
                                class="w-full pl-10 pr-4 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-background text-foreground"
                            />
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="flex flex-wrap gap-2">
                        <select v-model="filterStatus" class="px-3 py-2 border border-border rounded-lg bg-background text-foreground text-sm">
                            <option value="all">Todos los estados</option>
                            <option value="active">Solo activas</option>
                            <option value="inactive">Solo inactivas</option>
                        </select>

                        <select v-model="filterType" class="px-3 py-2 border border-border rounded-lg bg-background text-foreground text-sm">
                            <option value="all">Todos los tipos</option>
                            <option value="parent">Solo principales</option>
                            <option value="child">Solo subcategorías</option>
                        </select>
                    </div>

                    <!-- View Toggle -->
                    <div class="flex border border-border rounded-lg p-1">
                        <button
                            @click="viewMode = 'table'"
                            :class="viewMode === 'table' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground'"
                            class="px-3 py-1 rounded flex items-center gap-2 text-sm transition-colors"
                        >
                            <Table class="w-4 h-4" />
                            Tabla
                        </button>
                        <button
                            @click="viewMode = 'tree'"
                            :class="viewMode === 'tree' ? 'bg-primary text-primary-foreground' : 'text-muted-foreground hover:text-foreground'"
                            class="px-3 py-1 rounded flex items-center gap-2 text-sm transition-colors"
                        >
                            <ListTree class="w-4 h-4" />
                            Árbol
                        </button>
                    </div>
                </div>

                <!-- Active Filters Info -->
                <div v-if="searchQuery || filterStatus !== 'all' || filterType !== 'all'" class="mt-3 pt-3 border-t border-border">
                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <Filter class="w-4 h-4" />
                        <span>Filtros activos:</span>
                        <span v-if="searchQuery" class="bg-primary/10 text-primary px-2 py-1 rounded text-xs">
                            Búsqueda: "{{ searchQuery }}"
                        </span>
                        <span v-if="filterStatus !== 'all'" class="bg-secondary/10 text-secondary px-2 py-1 rounded text-xs">
                            Estado: {{ filterStatus === 'active' ? 'Activas' : 'Inactivas' }}
                        </span>
                        <span v-if="filterType !== 'all'" class="bg-primary/10 text-primary px-2 py-1 rounded text-xs">
                            Tipo: {{ filterType === 'parent' ? 'Principales' : 'Subcategorías' }}
                        </span>
                        <button
                            @click="searchQuery = ''; filterStatus = 'all'; filterType = 'all'"
                            class="text-destructive hover:text-destructive/80 text-xs flex items-center gap-1"
                        >
                            <X class="w-3 h-3" />
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Results Count -->
            <div class="text-sm text-muted-foreground">
                Mostrando {{ filteredCategories.length }} de {{ categories.length }} categorías
            </div>

            <!-- Table View -->
            <div v-if="viewMode === 'table'" class="bg-card rounded-xl border border-border shadow-lg">
                <div class="p-6">
                    <div v-if="filteredCategories.length === 0" class="text-center py-12">
                        <FolderOpen class="w-16 h-16 text-muted-foreground mx-auto mb-4" />
                        <p class="text-muted-foreground text-lg mb-2">No se encontraron categorías</p>
                        <p class="text-muted-foreground mb-6">Intenta ajustar tus filtros de búsqueda</p>
                        <button
                            @click="searchQuery = ''; filterStatus = 'all'; filterType = 'all'"
                            class="bg-primary text-primary-foreground px-6 py-3 rounded-lg inline-flex items-center gap-2 hover:bg-primary/90 transition-colors"
                        >
                            <X class="w-4 h-4" />
                            Limpiar filtros
                        </button>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="text-left py-4 px-4 font-semibold text-foreground">Nombre</th>
                                    <th class="text-left py-4 px-4 font-semibold text-foreground">Slug</th>
                                    <th class="text-left py-4 px-4 font-semibold text-foreground">Categoría Padre</th>
                                    <th class="text-left py-4 px-4 font-semibold text-foreground">Libros</th>
                                    <th class="text-left py-4 px-4 font-semibold text-foreground">Subcategorías</th>
                                    <th class="text-left py-4 px-4 font-semibold text-foreground">Estado</th>
                                    <th class="text-left py-4 px-4 font-semibold text-foreground">Orden</th>
                                    <th class="text-left py-4 px-4 font-semibold text-foreground">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr 
                                    v-for="category in filteredCategories" 
                                    :key="category.id"
                                    class="border-b border-border hover:bg-accent/50 transition-colors"
                                    draggable="true"
                                    @dragstart="onDragStart($event, category)"
                                    @dragover="onDragOver"
                                    @drop="onDropInside($event, category)"
                                >
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <GripVertical class="w-4 h-4 text-muted-foreground cursor-move" />
                                            <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                                                <Folder class="w-5 h-5 text-primary" />
                                            </div>
                                            <div>
                                                <p class="font-semibold text-foreground">{{ category.name }}</p>
                                                <p v-if="category.description" class="text-sm text-muted-foreground truncate max-w-xs">
                                                    {{ category.description }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="py-4 px-4 text-muted-foreground font-mono text-sm">
                                        {{ category.slug }}
                                    </td>
                                    <td class="py-4 px-4">
                                        <span v-if="category.parent_name" class="bg-secondary/10 text-secondary px-2 py-1 rounded text-sm">
                                            {{ category.parent_name }}
                                        </span>
                                        <span v-else class="text-muted-foreground text-sm">—</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="bg-primary/10 text-primary px-3 py-1 rounded-full text-sm font-medium">
                                            {{ category.books_count }} libros
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span v-if="category.children_count > 0" class="bg-secondary/10 text-secondary px-3 py-1 rounded-full text-sm font-medium">
                                            {{ category.children_count }} sub
                                        </span>
                                        <span v-else class="text-muted-foreground text-sm">—</span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <button
                                            @click="toggleStatus(category)"
                                            class="flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium transition-colors"
                                            :class="category.is_active 
                                                ? 'bg-success/10 text-success hover:bg-success/20' 
                                                : 'bg-muted text-muted-foreground hover:bg-muted/80'"
                                        >
                                            <component :is="category.is_active ? Eye : EyeOff" class="w-4 h-4" />
                                            {{ category.is_active ? 'Activa' : 'Inactiva' }}
                                        </button>
                                    </td>
                                    <td class="py-4 px-4">
                                        <span class="bg-muted text-foreground px-2 py-1 rounded text-sm font-mono">
                                            {{ category.sort_order }}
                                        </span>
                                    </td>
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-1">
                                            <button
                                                @click="viewDetails(category)"
                                                class="p-2 text-muted-foreground hover:text-primary transition-colors rounded-lg hover:bg-primary/10"
                                                title="Ver detalles"
                                            >
                                                <Eye class="w-4 h-4" />
                                            </button>
                                            <button
                                                @click="viewHistory(category)"
                                                class="p-2 text-muted-foreground hover:text-primary transition-colors rounded-lg hover:bg-primary/10"
                                                title="Historial"
                                            >
                                                <History class="w-4 h-4" />
                                            </button>
                                            <Link
                                                :href="`/admin/categories/${category.id}/edit`"
                                                class="p-2 text-muted-foreground hover:text-primary transition-colors rounded-lg hover:bg-primary/10"
                                            >
                                                <Edit class="w-4 h-4" />
                                            </Link>
                                            <button
                                                @click="deleteCategory(category)"
                                                class="p-2 text-muted-foreground hover:text-destructive transition-colors rounded-lg hover:bg-destructive/10"
                                                :disabled="category.books_count > 0 || category.children_count > 0"
                                            >
                                                <Trash2 class="w-4 h-4" />
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tree View -->
            <div v-else-if="viewMode === 'tree'" class="bg-card rounded-xl border border-border shadow-lg p-6">
                <div v-if="rootCategories.length === 0" class="text-center py-12">
                    <FolderOpen class="w-16 h-16 text-muted-foreground mx-auto mb-4" />
                    <p class="text-muted-foreground text-lg mb-2">No se encontraron categorías</p>
                </div>
                <div v-else class="space-y-2">
                    <CategoryTreeNode
                        v-for="category in rootCategories"
                        :key="category.id"
                        :category="category"
                        :depth="0"
                        @drag-start="onDragStart"
                        @drop-before="onDropBefore"
                        @drop-after="onDropAfter"
                        @drop-inside="onDropInside"
                        @toggle-status="toggleStatus"
                        @view-details="viewDetails"
                        @view-history="viewHistory"
                        @delete-category="deleteCategory"
                    />
                </div>
            </div>
        </div>

        <!-- Modal de Detalles -->
        <div v-if="showDetailModal && selectedCategory" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-card rounded-xl border border-border p-6 max-w-md w-full max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-foreground">Detalles de Categoría</h3>
                    <button @click="showDetailModal = false" class="p-1 hover:bg-accent rounded transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>
                
                <div class="space-y-4">
                    <div class="flex items-center gap-3 p-3 bg-primary/5 rounded-lg">
                        <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                            <Folder class="w-6 h-6 text-primary" />
                        </div>
                        <div>
                            <h4 class="font-semibold text-foreground">{{ selectedCategory.name }}</h4>
                            <p class="text-sm text-muted-foreground">{{ selectedCategory.slug }}</p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground">Estado:</span>
                            <span :class="selectedCategory.is_active ? 'text-success' : 'text-warning'" class="font-medium ml-2">
                                {{ selectedCategory.is_active ? 'Activa' : 'Inactiva' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Orden:</span>
                            <span class="font-medium ml-2">{{ selectedCategory.sort_order }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Libros:</span>
                            <span class="font-medium ml-2">{{ selectedCategory.books_count }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Subcategorías:</span>
                            <span class="font-medium ml-2">{{ selectedCategory.children_count }}</span>
                        </div>
                    </div>

                    <div v-if="selectedCategory.description">
                        <span class="text-muted-foreground text-sm block mb-1">Descripción:</span>
                        <p class="text-foreground">{{ selectedCategory.description }}</p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground">Creado:</span>
                            <span class="font-medium ml-2">{{ selectedCategory.created_at }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Actualizado:</span>
                            <span class="font-medium ml-2">{{ selectedCategory.updated_at }}</span>
                        </div>
                    </div>

                    <div class="flex gap-2 pt-4 border-t border-border">
                        <button
                            @click="viewHistory(selectedCategory)"
                            class="flex-1 bg-secondary text-secondary-foreground py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-secondary/90 transition-colors"
                        >
                            <History class="w-4 h-4" />
                            Ver Historial
                        </button>
                        <Link
                            :href="`/admin/categories/${selectedCategory.id}/edit`"
                            class="flex-1 bg-primary text-primary-foreground py-2 rounded-lg flex items-center justify-center gap-2 hover:bg-primary/90 transition-colors"
                        >
                            <Edit class="w-4 h-4" />
                            Editar
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Historial -->
        <div v-if="showHistoryModal && selectedCategory" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
            <div class="bg-card rounded-xl border border-border p-6 max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-xl font-bold text-foreground">
                        Historial: {{ selectedCategory.name }}
                    </h3>
                    <button @click="showHistoryModal = false" class="p-1 hover:bg-accent rounded transition-colors">
                        <X class="w-5 h-5" />
                    </button>
                </div>

                <div v-if="categoryHistory.length === 0" class="text-center py-8">
                    <History class="w-16 h-16 text-muted-foreground mx-auto mb-4" />
                    <p class="text-muted-foreground">No hay historial disponible</p>
                </div>

                <div v-else class="space-y-3">
                    <div
                        v-for="log in categoryHistory"
                        :key="log.id"
                        class="flex items-start gap-3 p-3 border border-border rounded-lg"
                    >
                        <div class="w-2 h-2 bg-primary rounded-full mt-2 flex-shrink-0"></div>
                        <div class="flex-1">
                            <p class="font-medium text-foreground">{{ log.description }}</p>
                            <p class="text-sm text-muted-foreground">
                                Por {{ log.user.name }} • {{ log.created_at }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>