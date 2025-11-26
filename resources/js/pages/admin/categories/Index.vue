<!-- resources/js/pages/admin/categories/Index.vue -->
<script setup lang="ts">
import CategoryTreeNode from '@/components/CategoryTreeNode.vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem, Category } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    Edit,
    Eye,
    EyeOff,
    Filter,
    Folder,
    FolderOpen,
    GripVertical,
    History,
    ListTree,
    Plus,
    Search,
    Table,
    Trash2,
    X,
    BookOpen,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

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
        filtered = filtered.filter(
            (category) =>
                category.name
                    .toLowerCase()
                    .includes(searchQuery.value.toLowerCase()) ||
                category.slug
                    .toLowerCase()
                    .includes(searchQuery.value.toLowerCase()) ||
                (category.description &&
                    category.description
                        .toLowerCase()
                        .includes(searchQuery.value.toLowerCase())),
        );
    }

    // Filtro por estado
    if (filterStatus.value !== 'all') {
        filtered = filtered.filter((category) =>
            filterStatus.value === 'active'
                ? category.is_active
                : !category.is_active,
        );
    }

    // Filtro por tipo
    if (filterType.value !== 'all') {
        filtered = filtered.filter((category) =>
            filterType.value === 'parent'
                ? category.parent_id === null
                : category.parent_id !== null,
        );
    }

    return filtered;
});

// Computed: Categorías organizadas en árbol (solo categorías raíz)
const rootCategories = computed(() => {
    return props.categories
        .filter((cat) => cat.parent_id === null)
        .sort((a, b) => a.sort_order - b.sort_order);
});

// Funciones
const deleteCategory = (category: Category) => {
    if (
        confirm(
            `¿Estás seguro de que quieres eliminar la categoría "${category.name}"?`,
        )
    ) {
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
            created_at: '2024-01-15 10:30:00',
        },
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

const onDrop = async (
    event: DragEvent,
    targetCategory: Category,
    position: 'before' | 'after' | 'inside',
) => {
    event.preventDefault();
    const draggedCategoryId = event.dataTransfer?.getData('text/plain');

    if (!draggedCategoryId) return;

    // Evitar que una categoría se mueva dentro de sí misma
    if (
        position === 'inside' &&
        parseInt(draggedCategoryId) === targetCategory.id
    ) {
        return;
    }

    try {
        await router.patch('/admin/categories/reorder', {
            draggedId: parseInt(draggedCategoryId),
            targetId: targetCategory.id,
            position: position,
            newParentId:
                position === 'inside'
                    ? targetCategory.id
                    : targetCategory.parent_id,
        });

        // Recargar para ver los cambios
        router.reload();
    } catch (error) {
        console.error('Error reordenando categoría:', error);
        alert('Error al reordenar la categoría');
    }
};

// Handlers específicos para las diferentes zonas
const onDropBefore = (event: DragEvent, category: Category) =>
    onDrop(event, category, 'before');
const onDropAfter = (event: DragEvent, category: Category) =>
    onDrop(event, category, 'after');
const onDropInside = (event: DragEvent, category: Category) =>
    onDrop(event, category, 'inside');
</script>

<template>

    <Head title="Gestión de Categorías" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-foreground">
                        Gestión de Categorías
                    </h1>
                    <p class="mt-2 text-muted-foreground">
                        Administra las categorías y subcategorías de tu
                        biblioteca
                    </p>
                </div>
                <Link href="/admin/categories/create"
                    class="flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-primary-foreground shadow-lg shadow-primary/25 transition-colors hover:bg-primary/90">
                <Plus class="h-4 w-4" />
                Nueva Categoría
                </Link>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <div
                    class="animate__animated animate__fadeIn rounded-xl border border-border bg-card p-6 shadow-sm transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">
                                Total Categorías
                            </p>
                            <p class="mt-1 text-2xl font-bold text-foreground">
                                {{ categories.length }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                            <Folder class="h-6 w-6 text-primary" />
                        </div>
                    </div>
                </div>

                <div
                    class="animate__animated animate__fadeIn animate__delay-100ms rounded-xl border border-border bg-card p-6 shadow-sm transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">
                                Categorías Activas
                            </p>
                            <p class="mt-1 text-2xl font-bold text-foreground">
                                {{
                                    categories.filter((c) => c.is_active).length
                                }}
                            </p>
                        </div>
                        <div class="bg-success/10 flex h-12 w-12 items-center justify-center rounded-lg">
                            <Eye class="text-success h-6 w-6" />
                        </div>
                    </div>
                </div>

                <div
                    class="animate__animated animate__fadeIn animate__delay-200ms rounded-xl border border-border bg-card p-6 shadow-sm transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">
                                Subcategorías
                            </p>
                            <p class="mt-1 text-2xl font-bold text-foreground">
                                {{
                                    categories.filter(
                                        (c) => c.parent_id !== null,
                                    ).length
                                }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-secondary/10">
                            <FolderOpen class="h-6 w-6 text-secondary" />
                        </div>
                    </div>
                </div>

                <div
                    class="animate__animated animate__fadeIn animate__delay-300ms rounded-xl border border-border bg-card p-6 shadow-sm transition-all duration-200 hover:shadow-md">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">
                                Promedio Libros
                            </p>
                            <p class="mt-1 text-2xl font-bold text-foreground">
                                {{
                                    Math.round(
                                        categories.reduce(
                                            (acc, c) => acc + c.books_count,
                                            0,
                                        ) / categories.length,
                                    ) || 0
                                }}
                            </p>
                        </div>
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                            <BookOpen class="h-6 w-6 text-blue-500" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Controls Bar -->
            <div
                class="animate__animated animate__fadeIn animate__delay-400ms rounded-xl border border-border bg-card p-4 shadow-sm">
                <div class="flex flex-col items-start justify-between gap-4 lg:flex-row lg:items-center">
                    <!-- Search -->
                    <div class="w-full flex-1 lg:max-w-md">
                        <div class="relative">
                            <Search
                                class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 transform text-muted-foreground" />
                            <input v-model="searchQuery" type="text"
                                placeholder="Buscar categorías por nombre, slug o descripción..."
                                class="w-full rounded-lg border border-border bg-background py-2 pr-4 pl-10 text-foreground focus:border-transparent focus:ring-2 focus:ring-primary focus:outline-none" />
                        </div>
                    </div>

                    <!-- Filters -->
                    <div class="flex flex-wrap gap-2">
                        <select v-model="filterStatus"
                            class="rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground">
                            <option value="all">Todos los estados</option>
                            <option value="active">Solo activas</option>
                            <option value="inactive">Solo inactivas</option>
                        </select>

                        <select v-model="filterType"
                            class="rounded-lg border border-border bg-background px-3 py-2 text-sm text-foreground">
                            <option value="all">Todos los tipos</option>
                            <option value="parent">Solo principales</option>
                            <option value="child">Solo subcategorías</option>
                        </select>
                    </div>

                    <!-- View Toggle -->
                    <div class="flex rounded-lg border border-border p-1">
                        <button @click="viewMode = 'table'" :class="viewMode === 'table'
                            ? 'bg-primary text-primary-foreground'
                            : 'text-muted-foreground hover:text-foreground'
                            " class="flex items-center gap-2 rounded px-3 py-1 text-sm transition-colors">
                            <Table class="h-4 w-4" />
                            Tabla
                        </button>
                        <button @click="viewMode = 'tree'" :class="viewMode === 'tree'
                            ? 'bg-primary text-primary-foreground'
                            : 'text-muted-foreground hover:text-foreground'
                            " class="flex items-center gap-2 rounded px-3 py-1 text-sm transition-colors">
                            <ListTree class="h-4 w-4" />
                            Árbol
                        </button>
                    </div>
                </div>

                <!-- Active Filters Info -->
                <div v-if="
                    searchQuery ||
                    filterStatus !== 'all' ||
                    filterType !== 'all'
                " class="mt-3 border-t border-border pt-3">
                    <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <Filter class="h-4 w-4" />
                        <span>Filtros activos:</span>
                        <span v-if="searchQuery" class="rounded bg-primary/10 px-2 py-1 text-xs text-primary">
                            Búsqueda: "{{ searchQuery }}"
                        </span>
                        <span v-if="filterStatus !== 'all'"
                            class="rounded bg-secondary/10 px-2 py-1 text-xs text-secondary">
                            Estado:
                            {{
                                filterStatus === 'active'
                                    ? 'Activas'
                                    : 'Inactivas'
                            }}
                        </span>
                        <span v-if="filterType !== 'all'" class="rounded bg-primary/10 px-2 py-1 text-xs text-primary">
                            Tipo:
                            {{
                                filterType === 'parent'
                                    ? 'Principales'
                                    : 'Subcategorías'
                            }}
                        </span>
                        <button @click="
                            searchQuery = '';
                        filterStatus = 'all';
                        filterType = 'all';
                        " class="flex items-center gap-1 text-xs text-destructive hover:text-destructive/80">
                            <X class="h-3 w-3" />
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Results Count -->
            <div class="text-sm text-muted-foreground">
                Mostrando {{ filteredCategories.length }} de
                {{ categories.length }} categorías
            </div>

            <!-- Table View -->
            <div v-if="viewMode === 'table'"
                class="animate__animated animate__fadeIn animate__delay-500ms rounded-xl border border-border bg-card shadow-lg">
                <div class="p-6">
                    <div v-if="filteredCategories.length === 0" class="py-12 text-center">
                        <FolderOpen class="mx-auto mb-4 h-16 w-16 text-muted-foreground" />
                        <p class="mb-2 text-lg text-muted-foreground">
                            No se encontraron categorías
                        </p>
                        <p class="mb-6 text-muted-foreground">
                            Intenta ajustar tus filtros de búsqueda
                        </p>
                        <button @click="
                            searchQuery = '';
                        filterStatus = 'all';
                        filterType = 'all';
                        " class="inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-3 text-primary-foreground transition-colors hover:bg-primary/90">
                            <X class="h-4 w-4" />
                            Limpiar filtros
                        </button>
                    </div>

                    <div v-else class="overflow-x-auto">
                        <table class="w-full whitespace-nowrap">
                            <thead>
                                <tr class="border-b border-border">
                                    <th class="px-4 py-4 text-left font-semibold text-foreground">
                                        Nombre
                                    </th>
                                    <th class="px-4 py-4 text-left font-semibold text-foreground">
                                        Slug
                                    </th>
                                    <th class="px-4 py-4 text-left font-semibold text-foreground">
                                        Categoría Padre
                                    </th>
                                    <th class="px-4 py-4 text-left font-semibold text-foreground">
                                        Libros
                                    </th>
                                    <th class="px-4 py-4 text-left font-semibold text-foreground">
                                        Subcategorías
                                    </th>
                                    <th class="px-4 py-4 text-left font-semibold text-foreground">
                                        Estado
                                    </th>
                                    <th class="px-4 py-4 text-left font-semibold text-foreground">
                                        Orden
                                    </th>
                                    <th class="px-4 py-4 text-left font-semibold text-foreground">
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="category in filteredCategories" :key="category.id"
                                    class="group border-b border-border transition-colors hover:bg-muted/50"
                                    draggable="true" @dragstart="onDragStart($event, category)" @dragover="onDragOver"
                                    @drop="onDropInside($event, category)">
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-3">
                                            <GripVertical class="h-4 w-4 cursor-move text-muted-foreground" />
                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10">
                                                <Folder class="h-5 w-5 text-primary" />
                                            </div>
                                            <div>
                                                <p class="font-semibold text-foreground">
                                                    {{ category.name }}
                                                </p>
                                                <p v-if="category.description"
                                                    class="max-w-xs truncate text-sm text-muted-foreground">
                                                    {{ category.description }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-4 font-mono text-sm text-muted-foreground">
                                        {{ category.slug }}
                                    </td>
                                    <td class="px-4 py-4">
                                        <span v-if="category.parent_name"
                                            class="rounded bg-secondary/10 px-2 py-1 text-sm text-secondary">
                                            {{ category.parent_name }}
                                        </span>
                                        <span v-else class="text-sm text-muted-foreground">—</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span
                                            class="rounded-full bg-primary/10 px-3 py-1 text-sm font-medium text-primary">
                                            {{ category.books_count }} libros
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span v-if="category.children_count > 0"
                                            class="rounded-full bg-secondary/10 px-3 py-1 text-sm font-medium text-secondary">
                                            {{ category.children_count }} sub
                                        </span>
                                        <span v-else class="text-sm text-muted-foreground">—</span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <button @click="toggleStatus(category)"
                                            class="flex items-center gap-2 rounded-full px-3 py-1 text-sm font-medium transition-colors"
                                            :class="category.is_active
                                                ? 'bg-success/10 text-success hover:bg-success/20'
                                                : 'bg-muted text-muted-foreground hover:bg-muted/80'
                                                ">
                                            <component :is="category.is_active
                                                ? Eye
                                                : EyeOff
                                                " class="h-4 w-4" />
                                            {{
                                                category.is_active
                                                    ? 'Activa'
                                                    : 'Inactiva'
                                            }}
                                        </button>
                                    </td>
                                    <td class="px-4 py-4">
                                        <span class="rounded bg-muted px-2 py-1 font-mono text-sm text-foreground">
                                            {{ category.sort_order }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-4">
                                        <div class="flex items-center gap-1">
                                            <button @click="viewDetails(category)"
                                                class="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-primary/10 hover:text-primary"
                                                title="Ver detalles">
                                                <Eye class="h-4 w-4" />
                                            </button>
                                            <button @click="viewHistory(category)"
                                                class="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-primary/10 hover:text-primary"
                                                title="Historial">
                                                <History class="h-4 w-4" />
                                            </button>
                                            <Link :href="`/admin/categories/${category.id}/edit`"
                                                class="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-primary/10 hover:text-primary">
                                            <Edit class="h-4 w-4" />
                                            </Link>
                                            <button @click="
                                                deleteCategory(category)
                                                " class="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                                                :disabled="category.books_count > 0 ||
                                                    category.children_count > 0
                                                    ">
                                                <Trash2 class="h-4 w-4" />
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
            <div v-else-if="viewMode === 'tree'"
                class="animate__animated animate__fadeIn animate__delay-500ms rounded-xl border border-border bg-card p-6 shadow-lg">
                <div v-if="rootCategories.length === 0" class="py-12 text-center">
                    <FolderOpen class="mx-auto mb-4 h-16 w-16 text-muted-foreground" />
                    <p class="mb-2 text-lg text-muted-foreground">
                        No se encontraron categorías
                    </p>
                </div>
                <div v-else class="space-y-2">
                    <CategoryTreeNode v-for="category in rootCategories" :key="category.id" :category="category"
                        :depth="0" @drag-start="onDragStart" @drop-before="onDropBefore" @drop-after="onDropAfter"
                        @drop-inside="onDropInside" @toggle-status="toggleStatus" @view-details="viewDetails"
                        @view-history="viewHistory" @delete-category="deleteCategory" />
                </div>
            </div>
        </div>

        <!-- Modal de Detalles -->
        <div v-if="showDetailModal && selectedCategory"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="max-h-[90vh] w-full max-w-md overflow-y-auto rounded-xl border border-border bg-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-foreground">
                        Detalles de Categoría
                    </h3>
                    <button @click="showDetailModal = false" class="rounded p-1 transition-colors hover:bg-accent">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div class="space-y-4">
                    <div class="flex items-center gap-3 rounded-lg bg-primary/5 p-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10">
                            <Folder class="h-6 w-6 text-primary" />
                        </div>
                        <div>
                            <h4 class="font-semibold text-foreground">
                                {{ selectedCategory.name }}
                            </h4>
                            <p class="text-sm text-muted-foreground">
                                {{ selectedCategory.slug }}
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground">Estado:</span>
                            <span :class="selectedCategory.is_active
                                ? 'text-success'
                                : 'text-warning'
                                " class="ml-2 font-medium">
                                {{
                                    selectedCategory.is_active
                                        ? 'Activa'
                                        : 'Inactiva'
                                }}
                            </span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Orden:</span>
                            <span class="ml-2 font-medium">{{
                                selectedCategory.sort_order
                            }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Libros:</span>
                            <span class="ml-2 font-medium">{{
                                selectedCategory.books_count
                            }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Subcategorías:</span>
                            <span class="ml-2 font-medium">{{
                                selectedCategory.children_count
                            }}</span>
                        </div>
                    </div>

                    <div v-if="selectedCategory.description">
                        <span class="mb-1 block text-sm text-muted-foreground">Descripción:</span>
                        <p class="text-foreground">
                            {{ selectedCategory.description }}
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-sm">
                        <div>
                            <span class="text-muted-foreground">Creado:</span>
                            <span class="ml-2 font-medium">{{
                                selectedCategory.created_at
                            }}</span>
                        </div>
                        <div>
                            <span class="text-muted-foreground">Actualizado:</span>
                            <span class="ml-2 font-medium">{{
                                selectedCategory.updated_at
                            }}</span>
                        </div>
                    </div>

                    <div class="flex gap-2 border-t border-border pt-4">
                        <button @click="viewHistory(selectedCategory)"
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-secondary py-2 text-secondary-foreground transition-colors hover:bg-secondary/90">
                            <History class="h-4 w-4" />
                            Ver Historial
                        </button>
                        <Link :href="`/admin/categories/${selectedCategory.id}/edit`"
                            class="flex flex-1 items-center justify-center gap-2 rounded-lg bg-primary py-2 text-primary-foreground transition-colors hover:bg-primary/90">
                        <Edit class="h-4 w-4" />
                        Editar
                        </Link>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal de Historial -->
        <div v-if="showHistoryModal && selectedCategory"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 p-4">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-xl border border-border bg-card p-6">
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xl font-bold text-foreground">
                        Historial: {{ selectedCategory.name }}
                    </h3>
                    <button @click="showHistoryModal = false" class="rounded p-1 transition-colors hover:bg-accent">
                        <X class="h-5 w-5" />
                    </button>
                </div>

                <div v-if="categoryHistory.length === 0" class="py-8 text-center">
                    <History class="mx-auto mb-4 h-16 w-16 text-muted-foreground" />
                    <p class="text-muted-foreground">
                        No hay historial disponible
                    </p>
                </div>

                <div v-else class="space-y-3">
                    <div v-for="log in categoryHistory" :key="log.id"
                        class="flex items-start gap-3 rounded-lg border border-border p-3">
                        <div class="mt-2 h-2 w-2 flex-shrink-0 rounded-full bg-primary"></div>
                        <div class="flex-1">
                            <p class="font-medium text-foreground">
                                {{ log.description }}
                            </p>
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
