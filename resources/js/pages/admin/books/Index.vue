<!-- resources/js/pages/admin/books/Index.vue -->
<script setup lang="ts">
import FilterBar from '@/components/FilterBar.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    Archive,
    BookCopy,
    BookMarked,
    BookOpen,
    Download,
    Edit,
    Eye,
    FileDigit,
    Globe,
    Library,
    MoreHorizontal,
    Plus,
    Star,
    Trash2,
    TrendingUp,
    Users,
    X,
    Zap,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

const breadcrumbs = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Libros', href: '/admin/books' },
];

// Props
const props = defineProps<{
    books: {
        data: Array<{
            id: number;
            title: string;
            isbn: string;
            pages: number;
            publication_year: number;
            book_type: string;
            featured: boolean;
            is_active: boolean;
            publisher?: { name: string };
            categories: Array<{ id: number; name: string }>;
            downloads_count: number;
            physical_copies_count: number;
            loans_count: number;
        }>;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
    filters: {
        search?: string;
        category?: string;
        book_type?: string;
        status?: string;
    };
    categories: Array<{ id: number; name: string }>;
    stats: {
        total_books: number;
        active_books: number;
        digital_books: number;
        physical_books: number;
        total_physical_copies: number;
    };
}>();

// Refs usando el nuevo formato del FilterBar
const filters = ref({
    search: props.filters.search || '',
    book_type: props.filters.book_type || '',
    status: props.filters.status || '',
    category: props.filters.category || '',
});

// Configuración del FilterBar para libros
const filterConfig = {
    module: 'books' as const,
};

// Datos para el FilterBar
const filterData = {
    categories: props.categories,
};

// Computed
const activeFiltersCount = computed(() => {
    let count = 0;
    if (filters.value.search) count++;
    if (filters.value.category) count++;
    if (filters.value.book_type) count++;
    if (filters.value.status) count++;
    return count;
});

// Watchers
watch(
    filters,
    (newFilters) => {
        router.get(
            '/admin/books',
            {
                search: newFilters.search,
                category: newFilters.category,
                book_type: newFilters.book_type,
                status: newFilters.status,
            },
            {
                preserveState: true,
                replace: true,
                preserveScroll: true,
            },
        );
    },
    { deep: true },
);

// Methods
function clearFilters() {
    filters.value = {
        search: '',
        category: '',
        book_type: '',
        status: '',
    };
}

function deleteBook(book: any) {
    if (
        confirm(
            `¿Estás seguro de que quieres eliminar el libro "${book.title}"?`,
        )
    ) {
        router.delete(`/admin/books/${book.id}`);
    }
}

function toggleFeatured(book: any) {
    router.patch(`/admin/books/${book.id}/toggle-featured`);
}

function toggleActive(book: any) {
    router.patch(`/admin/books/${book.id}/toggle-active`);
}

// Book type labels usando colores CSS personalizados
const bookTypeLabels: Record<string, string> = {
    digital: 'Digital',
    physical: 'Físico',
    both: 'Híbrido',
};

const bookTypeColors: Record<string, string> = {
    digital:
        'bg-blue-500/10 text-blue-600 border-blue-200 dark:bg-blue-500/20 dark:text-blue-400 dark:border-blue-800',
    physical:
        'bg-emerald-500/10 text-emerald-600 border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-400 dark:border-emerald-800',
    both: 'bg-purple-500/10 text-purple-600 border-purple-200 dark:bg-purple-500/20 dark:text-purple-400 dark:border-purple-800',
};

const bookTypeIcons: Record<string, any> = {
    digital: FileDigit,
    physical: BookCopy,
    both: Globe,
};

// Safe accessor for publisher name
const getPublisherName = (book: any) => {
    return book.publisher?.name || 'Sin editorial';
};

// Safe accessor for categories
const getCategories = (book: any) => {
    return book.categories || [];
};
</script>

<template>

    <Head title="Gestión de Libros" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-6">
            <!-- Header Mejorado - Consistente con Dashboard -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">
                            Gestión de Libros
                        </h1>
                        <p class="mt-2 flex items-center gap-2 text-muted-foreground">
                            <Library class="h-4 w-4 text-primary" />
                            Administra el catálogo completo de libros digitales
                            y físicos
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <Button as-child class="bg-primary text-primary-foreground hover:bg-primary/90">
                            <a href="/admin/books/create" class="flex items-center gap-2">
                                <Plus class="h-4 w-4" />
                                Nuevo Libro
                            </a>
                        </Button>
                        <div
                            class="flex items-center gap-2 rounded-lg border border-primary/20 bg-primary/10 px-4 py-2 text-primary">
                            <Zap class="h-4 w-4 animate-pulse" />
                            <span class="text-sm font-medium">{{ books.data.length }} Libros</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards - Consistente con Dashboard -->
            <div class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
                <!-- Total Libros -->
                <div
                    class="group overflow-hidden rounded-xl border border-border bg-card shadow-lg transition-all duration-500 hover:-translate-y-1 hover:border-primary/30 hover:shadow-xl">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="mb-2 text-sm font-medium text-muted-foreground">
                                    Total Libros
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.total_books }}
                                </p>
                                <div class="text-success mt-2 flex items-center gap-1 text-xs">
                                    <TrendingUp class="h-3 w-3" />
                                    <span>+12% este mes</span>
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 transition-transform duration-300 group-hover:scale-110">
                                <BookOpen class="h-6 w-6 text-primary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 w-0 bg-gradient-to-r from-primary to-primary/60 transition-all duration-500 group-hover:w-full">
                    </div>
                </div>

                <!-- Libros Activos -->
                <div
                    class="group overflow-hidden rounded-xl border border-border bg-card shadow-lg transition-all duration-500 hover:-translate-y-1 hover:border-secondary/30 hover:shadow-xl">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="mb-2 text-sm font-medium text-muted-foreground">
                                    Libros Activos
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.active_books }}
                                </p>
                                <div class="text-success mt-2 flex items-center gap-1 text-xs">
                                    <TrendingUp class="h-3 w-3" />
                                    <span>+8% este mes</span>
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 transition-transform duration-300 group-hover:scale-110">
                                <BookOpen class="h-6 w-6 text-secondary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 w-0 bg-gradient-to-r from-secondary to-secondary/60 transition-all duration-500 group-hover:w-full">
                    </div>
                </div>

                <!-- Libros Digitales -->
                <div
                    class="group overflow-hidden rounded-xl border border-border bg-card shadow-lg transition-all duration-500 hover:-translate-y-1 hover:border-primary/30 hover:shadow-xl">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="mb-2 text-sm font-medium text-muted-foreground">
                                    Digitales
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.digital_books }}
                                </p>
                                <div class="text-success mt-2 flex items-center gap-1 text-xs">
                                    <TrendingUp class="h-3 w-3" />
                                    <span>+25% este mes</span>
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 transition-transform duration-300 group-hover:scale-110">
                                <Download class="h-6 w-6 text-primary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 w-0 bg-gradient-to-r from-primary to-primary/60 transition-all duration-500 group-hover:w-full">
                    </div>
                </div>

                <!-- Libros Físicos -->
                <div
                    class="group overflow-hidden rounded-xl border border-border bg-card shadow-lg transition-all duration-500 hover:-translate-y-1 hover:border-secondary/30 hover:shadow-xl">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="mb-2 text-sm font-medium text-muted-foreground">
                                    Físicos
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.physical_books }}
                                </p>
                                <div class="mt-2 flex items-center gap-1 text-xs text-muted-foreground">
                                    <BookCopy class="h-3 w-3" />
                                    <span>{{
                                        stats.total_physical_copies
                                    }}
                                        copias</span>
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 transition-transform duration-300 group-hover:scale-110">
                                <BookCopy class="h-6 w-6 text-secondary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 w-0 bg-gradient-to-r from-secondary to-secondary/60 transition-all duration-500 group-hover:w-full">
                    </div>
                </div>
            </div>

            <!-- FilterBar Component -->
            <FilterBar v-model="filters" :config="filterConfig" :data="filterData" />

            <!-- Results Count - Consistente con Categories -->
            <div class="text-sm text-muted-foreground">
                Mostrando {{ books.data.length }} de {{ books.total }} libros
            </div>

            <!-- Books Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                <Card v-for="book in books.data" :key="book.id"
                    class="group relative overflow-hidden rounded-2xl border border-border bg-card/95 backdrop-blur-sm transition-all duration-300 hover:-translate-y-1 hover:border-primary/40 hover:shadow-2xl">
                    <!-- Badge Destacado -->
                    <div v-if="book.featured" class="absolute top-4 right-4 z-10">
                        <Badge class="animate-pulse border-0 bg-amber-500 text-white shadow-lg">
                            <Star class="mr-1 h-3.5 w-3.5 fill-current" />
                            Destacado
                        </Badge>
                    </div>

                    <!-- Overlay Inactivo -->
                    <div v-if="!book.is_active"
                        class="absolute inset-0 z-20 flex items-center justify-center rounded-2xl bg-background/80 backdrop-blur-md">
                        <Badge variant="destructive" class="border-0 px-4 py-2 text-lg shadow-md">
                            <Archive class="mr-2 h-4 w-4" />
                            Inactivo
                        </Badge>
                    </div>

                    <CardHeader class="px-5 pt-5 pb-3">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0 flex-1 space-y-2">
                                <!-- Tipo de libro -->
                                <Badge :class="bookTypeColors[book.book_type]" class="border font-semibold shadow-sm">
                                    <component :is="bookTypeIcons[book.book_type]" class="mr-1 h-3.5 w-3.5" />
                                    {{ bookTypeLabels[book.book_type] }}
                                </Badge>

                                <!-- Título -->
                                <CardTitle
                                    class="line-clamp-2 text-xl leading-snug font-bold transition-colors duration-200 group-hover:text-primary">
                                    {{ book.title }}
                                </CardTitle>

                                <!-- Editorial -->
                                <CardDescription class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <BookMarked class="h-4 w-4 text-muted-foreground/80" />
                                    {{ getPublisherName(book) }}
                                </CardDescription>
                            </div>

                            <!-- Menú de acciones -->
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon"
                                        class="h-9 w-9 rounded-full opacity-0 transition-opacity duration-200 group-hover:opacity-100 hover:bg-primary/10">
                                        <MoreHorizontal class="h-4 w-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-52 shadow-lg">
                                    <DropdownMenuItem as-child>
                                        <a :href="`/admin/books/${book.id}`"
                                            class="flex cursor-pointer items-center text-foreground hover:text-primary">
                                            <Eye class="mr-2 h-4 w-4 text-blue-500" />
                                            Ver detalles
                                        </a>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem as-child>
                                        <a :href="`/admin/books/${book.id}/edit`"
                                            class="flex cursor-pointer items-center text-foreground hover:text-emerald-600">
                                            <Edit class="mr-2 h-4 w-4 text-emerald-500" />
                                            Editar libro
                                        </a>
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem @click="toggleFeatured(book)" class="cursor-pointer">
                                        <Star class="mr-2 h-4 w-4 text-amber-500" />
                                        {{
                                            book.featured
                                                ? 'Quitar destacado'
                                                : 'Destacar libro'
                                        }}
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="toggleActive(book)" class="cursor-pointer">
                                        <component :is="book.is_active
                                            ? Archive
                                            : BookOpen
                                            " class="mr-2 h-4 w-4 text-orange-500" />
                                        {{
                                            book.is_active
                                                ? 'Desactivar'
                                                : 'Activar'
                                        }}
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem @click="deleteBook(book)"
                                        class="cursor-pointer text-destructive focus:text-destructive">
                                        <Trash2 class="mr-2 h-4 w-4" />
                                        Eliminar libro
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </CardHeader>

                    <CardContent class="space-y-4 px-5 pb-5">
                        <!-- Categorías -->
                        <div class="flex flex-wrap gap-2">
                            <Badge v-for="category in getCategories(book).slice(
                                0,
                                2,
                            )" :key="category.id" variant="outline"
                                class="rounded-full border-secondary/20 bg-secondary/10 px-2.5 py-0.5 text-xs text-foreground">
                                {{ category.name }}
                            </Badge>
                            <Badge v-if="getCategories(book).length > 2" variant="outline"
                                class="rounded-full bg-muted text-xs text-muted-foreground">
                                +{{ getCategories(book).length - 2 }}
                            </Badge>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="space-y-1 rounded-lg bg-blue-500/10 p-2 transition hover:bg-blue-500/20">
                                <Download class="mx-auto h-4 w-4 text-blue-600" />
                                <p class="text-sm font-bold">
                                    {{ book.downloads_count || 0 }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Descargas
                                </p>
                            </div>
                            <div v-if="book.book_type !== 'digital'"
                                class="space-y-1 rounded-lg bg-emerald-500/10 p-2 transition hover:bg-emerald-500/20">
                                <BookCopy class="mx-auto h-4 w-4 text-emerald-600" />
                                <p class="text-sm font-bold">
                                    {{ book.physical_copies_count || 0 }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Copias
                                </p>
                            </div>
                            <div v-else class="space-y-1 rounded-lg bg-gray-500/10 p-2 transition hover:bg-gray-500/20">
                                <FileDigit class="mx-auto h-4 w-4 text-gray-600" />
                                <p class="text-sm font-bold text-foreground">
                                    Digital
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Solo
                                </p>
                            </div>
                            <div class="space-y-1 rounded-lg bg-purple-500/10 p-2 transition hover:bg-purple-500/20">
                                <Users class="mx-auto h-4 w-4 text-purple-600" />
                                <p class="text-sm font-bold">
                                    {{ book.loans_count || 0 }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Préstamos
                                </p>
                            </div>
                        </div>

                        <!-- Metadatos -->
                        <div class="space-y-2 text-sm">
                            <div v-for="(value, label) in {
                                ISBN: book.isbn || 'N/A',
                                Páginas: book.pages || 0,
                                Año: book.publication_year || 'N/A',
                            }" :key="label"
                                class="flex items-center justify-between rounded-lg bg-muted/40 p-2 transition hover:bg-muted/60">
                                <span class="font-medium text-muted-foreground">{{ label }}:</span>
                                <span class="font-mono font-semibold text-foreground">{{ value }}</span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="px-5 pt-0 pb-5">
                        <Button as-child variant="outline"
                            class="w-full border-primary/20 transition-all duration-200 hover:bg-primary hover:text-white">
                            <a :href="`/admin/books/${book.id}`" class="flex items-center justify-center gap-2">
                                <Eye class="h-4 w-4" />
                                Ver Detalles
                            </a>
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <!-- Empty State - Consistente con Categories -->
            <div v-if="books.data.length === 0"
                class="rounded-xl border-2 border-dashed border-border bg-card py-16 text-center">
                <div class="mx-auto max-w-md">
                    <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-primary/10 p-4">
                        <Library class="h-10 w-10 text-primary" />
                    </div>
                    <h3 class="mb-3 text-2xl font-bold text-foreground">
                        No se encontraron libros
                    </h3>
                    <p class="mb-6 text-lg text-muted-foreground">
                        {{
                            activeFiltersCount > 0
                                ? 'Intenta ajustar tus filtros de búsqueda'
                                : 'Comienza agregando el primer libro al catálogo'
                        }}
                    </p>
                    <Button as-child v-if="activeFiltersCount === 0"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-3 text-primary-foreground transition-colors hover:bg-primary/90">
                        <a href="/admin/books/create" class="flex items-center gap-2">
                            <Plus class="h-5 w-5" />
                            Crear Primer Libro
                        </a>
                    </Button>
                    <Button v-else variant="outline" @click="clearFilters"
                        class="border-primary/20 text-primary hover:bg-primary hover:text-primary-foreground">
                        <X class="mr-2 h-4 w-4" />
                        Limpiar Filtros
                    </Button>
                </div>
            </div>

            <!-- Pagination - Consistente con Categories -->
            <div v-if="books.data.length > 0" class="flex justify-center">
                <div class="flex gap-2">
                    <Button v-for="(link, index) in books.links" :key="index" :href="link.url" :disabled="!link.url"
                        :variant="link.active ? 'default' : 'outline'" size="sm" class="rounded-lg font-medium"
                        v-html="link.label" preserve-scroll />
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
