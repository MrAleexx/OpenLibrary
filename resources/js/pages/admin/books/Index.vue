<!-- resources/js/pages/admin/books/Index.vue -->
<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue'
import FilterBar from '@/components/FilterBar.vue'
import {    Card,    CardContent,    CardDescription,    CardHeader,    CardTitle,    CardFooter
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {    DropdownMenu,    DropdownMenuContent,    DropdownMenuItem,    DropdownMenuTrigger,    DropdownMenuSeparator
} from '@/components/ui/dropdown-menu'
import {Plus, MoreHorizontal, Eye, Edit, Trash2, BookOpen, Download, Users, Library, BookMarked, BookCopy, Star, FileDigit, Globe, Archive, TrendingUp, Zap, X
} from 'lucide-vue-next'

const breadcrumbs = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Libros', href: '/admin/books' },
]

// Props
const props = defineProps<{
    books: {
        data: Array<{
            id: number
            title: string
            isbn: string
            pages: number
            publication_year: number
            book_type: string
            featured: boolean
            is_active: boolean
            publisher?: { name: string }
            categories: Array<{ id: number; name: string }>
            downloads_count: number
            physical_copies_count: number
            loans_count: number
        }>
        total: number
        links: Array<{ url: string | null; label: string; active: boolean }>
    }
    filters: {
        search?: string
        category?: string
        book_type?: string
        status?: string
    }
    categories: Array<{ id: number; name: string }>
    stats: {
        total_books: number
        active_books: number
        digital_books: number
        physical_books: number
        total_physical_copies: number
    }
}>()

// Refs usando el nuevo formato del FilterBar
const filters = ref({
    search: props.filters.search || '',
    book_type: props.filters.book_type || '',
    status: props.filters.status || '',
    category: props.filters.category || ''
})

// Configuración del FilterBar para libros
const filterConfig = {
    module: 'books' as const
}

// Datos para el FilterBar
const filterData = {
    categories: props.categories
}

// Computed
const activeFiltersCount = computed(() => {
    let count = 0
    if (filters.value.search) count++
    if (filters.value.category) count++
    if (filters.value.book_type) count++
    if (filters.value.status) count++
    return count
})

// Watchers
watch(filters, (newFilters) => {
    router.get('/admin/books', {
        search: newFilters.search,
        category: newFilters.category,
        book_type: newFilters.book_type,
        status: newFilters.status
    }, {
        preserveState: true,
        replace: true,
        preserveScroll: true
    })
}, { deep: true })

// Methods
function clearFilters() {
    filters.value = {
        search: '',
        category: '',
        book_type: '',
        status: ''
    }
}

function deleteBook(book: any) {
    if (confirm(`¿Estás seguro de que quieres eliminar el libro "${book.title}"?`)) {
        router.delete(`/admin/books/${book.id}`)
    }
}

function toggleFeatured(book: any) {
    router.patch(`/admin/books/${book.id}/toggle-featured`)
}

function toggleActive(book: any) {
    router.patch(`/admin/books/${book.id}/toggle-active`)
}

// Book type labels usando colores CSS personalizados
const bookTypeLabels: Record<string, string> = {
    digital: 'Digital',
    physical: 'Físico',
    both: 'Híbrido'
}

const bookTypeColors: Record<string, string> = {
    digital: 'bg-blue-500/10 text-blue-600 border-blue-200 dark:bg-blue-500/20 dark:text-blue-400 dark:border-blue-800',
    physical: 'bg-emerald-500/10 text-emerald-600 border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-400 dark:border-emerald-800',
    both: 'bg-purple-500/10 text-purple-600 border-purple-200 dark:bg-purple-500/20 dark:text-purple-400 dark:border-purple-800'
}

const bookTypeIcons: Record<string, any> = {
    digital: FileDigit,
    physical: BookCopy,
    both: Globe
}

// Safe accessor for publisher name
const getPublisherName = (book: any) => {
    return book.publisher?.name || 'Sin editorial'
}

// Safe accessor for categories
const getCategories = (book: any) => {
    return book.categories || []
}
</script>

<template>

    <Head title="Gestión de Libros" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-8">
            <!-- Header Mejorado - Consistente con Dashboard -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">
                            Gestión de Libros
                        </h1>
                        <p class="text-muted-foreground mt-2 flex items-center gap-2">
                            <Library class="w-4 h-4 text-primary" />
                            Administra el catálogo completo de libros digitales y físicos
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <Button as-child class="bg-primary text-primary-foreground hover:bg-primary/90">
                            <a href="/admin/books/create" class="flex items-center gap-2">
                                <Plus class="w-4 h-4" />
                                Nuevo Libro
                            </a>
                        </Button>
                        <div
                            class="flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary rounded-lg border border-primary/20">
                            <Zap class="w-4 h-4 animate-pulse" />
                            <span class="text-sm font-medium">{{ books.data.length }} Libros</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards - Consistente con Dashboard -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <!-- Total Libros -->
                <div
                    class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-primary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground mb-2">
                                    Total Libros
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.total_books }}
                                </p>
                                <div class="flex items-center gap-1 mt-2 text-xs text-success">
                                    <TrendingUp class="w-3 h-3" />
                                    <span>+12% este mes</span>
                                </div>
                            </div>
                            <div
                                class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <BookOpen class="w-6 h-6 text-primary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 bg-gradient-to-r from-primary to-primary/60 w-0 group-hover:w-full transition-all duration-500">
                    </div>
                </div>

                <!-- Libros Activos -->
                <div
                    class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-secondary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground mb-2">
                                    Libros Activos
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.active_books }}
                                </p>
                                <div class="flex items-center gap-1 mt-2 text-xs text-success">
                                    <TrendingUp class="w-3 h-3" />
                                    <span>+8% este mes</span>
                                </div>
                            </div>
                            <div
                                class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <BookOpen class="w-6 h-6 text-secondary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 bg-gradient-to-r from-secondary to-secondary/60 w-0 group-hover:w-full transition-all duration-500">
                    </div>
                </div>

                <!-- Libros Digitales -->
                <div
                    class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-primary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground mb-2">
                                    Digitales
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.digital_books }}
                                </p>
                                <div class="flex items-center gap-1 mt-2 text-xs text-success">
                                    <TrendingUp class="w-3 h-3" />
                                    <span>+25% este mes</span>
                                </div>
                            </div>
                            <div
                                class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <Download class="w-6 h-6 text-primary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 bg-gradient-to-r from-primary to-primary/60 w-0 group-hover:w-full transition-all duration-500">
                    </div>
                </div>

                <!-- Libros Físicos -->
                <div
                    class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-secondary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1">
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground mb-2">
                                    Físicos
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.physical_books }}
                                </p>
                                <div class="flex items-center gap-1 mt-2 text-xs text-muted-foreground">
                                    <BookCopy class="w-3 h-3" />
                                    <span>{{ stats.total_physical_copies }} copias</span>
                                </div>
                            </div>
                            <div
                                class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <BookCopy class="w-6 h-6 text-secondary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 bg-gradient-to-r from-secondary to-secondary/60 w-0 group-hover:w-full transition-all duration-500">
                    </div>
                </div>
            </div>

            <!-- FilterBar Component -->
            <FilterBar v-model="filters" :config="filterConfig" :data="filterData" />

            <!-- Results Count - Consistente con Categories -->
            <div class="text-sm text-muted-foreground">
                Mostrando {{ books.data.length }} de {{ books.total }} libros
            </div>

            <!-- Books Grid Mejorado -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4 gap-6">
                <Card v-for="book in books.data" :key="book.id"
                    class="group relative bg-card/95 backdrop-blur-sm rounded-2xl border border-border hover:border-primary/40 transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 overflow-hidden">
                    <!-- Badge Destacado -->
                    <div v-if="book.featured" class="absolute top-4 right-4 z-10">
                        <Badge class="bg-amber-500 text-white border-0 shadow-lg animate-pulse">
                            <Star class="w-3.5 h-3.5 mr-1 fill-current" />
                            Destacado
                        </Badge>
                    </div>

                    <!-- Overlay Inactivo -->
                    <div v-if="!book.is_active"
                        class="absolute inset-0 bg-background/80 backdrop-blur-md z-20 flex items-center justify-center rounded-2xl">
                        <Badge variant="destructive" class="text-lg py-2 px-4 border-0 shadow-md">
                            <Archive class="w-4 h-4 mr-2" />
                            Inactivo
                        </Badge>
                    </div>

                    <CardHeader class="pb-3 pt-5 px-5">
                        <div class="flex justify-between items-start gap-3">
                            <div class="space-y-2 flex-1 min-w-0">
                                <!-- Tipo de libro -->
                                <Badge :class="bookTypeColors[book.book_type]" class="border font-semibold shadow-sm">
                                    <component :is="bookTypeIcons[book.book_type]" class="w-3.5 h-3.5 mr-1" />
                                    {{ bookTypeLabels[book.book_type] }}
                                </Badge>

                                <!-- Título -->
                                <CardTitle
                                    class="text-xl font-bold leading-snug line-clamp-2 transition-colors duration-200 group-hover:text-primary">
                                    {{ book.title }}
                                </CardTitle>

                                <!-- Editorial -->
                                <CardDescription class="flex items-center gap-2 text-sm text-muted-foreground">
                                    <BookMarked class="w-4 h-4 text-muted-foreground/80" />
                                    {{ getPublisherName(book) }}
                                </CardDescription>
                            </div>

                            <!-- Menú de acciones -->
                            <DropdownMenu>
                                <DropdownMenuTrigger as-child>
                                    <Button variant="ghost" size="icon"
                                        class="opacity-0 group-hover:opacity-100 transition-opacity duration-200 w-9 h-9 rounded-full hover:bg-primary/10">
                                        <MoreHorizontal class="w-4 h-4" />
                                    </Button>
                                </DropdownMenuTrigger>
                                <DropdownMenuContent align="end" class="w-52 shadow-lg">
                                    <DropdownMenuItem as-child>
                                        <a :href="`/admin/books/${book.id}`"
                                            class="flex items-center cursor-pointer text-foreground hover:text-primary">
                                            <Eye class="w-4 h-4 mr-2 text-blue-500" />
                                            Ver detalles
                                        </a>
                                    </DropdownMenuItem>
                                    <DropdownMenuItem as-child>
                                        <a :href="`/admin/books/${book.id}/edit`"
                                            class="flex items-center cursor-pointer text-foreground hover:text-emerald-600">
                                            <Edit class="w-4 h-4 mr-2 text-emerald-500" />
                                            Editar libro
                                        </a>
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem @click="toggleFeatured(book)" class="cursor-pointer">
                                        <Star class="w-4 h-4 mr-2 text-amber-500" />
                                        {{ book.featured ? 'Quitar destacado' : 'Destacar libro' }}
                                    </DropdownMenuItem>
                                    <DropdownMenuItem @click="toggleActive(book)" class="cursor-pointer">
                                        <component :is="book.is_active ? Archive : BookOpen"
                                            class="w-4 h-4 mr-2 text-orange-500" />
                                        {{ book.is_active ? 'Desactivar' : 'Activar' }}
                                    </DropdownMenuItem>
                                    <DropdownMenuSeparator />
                                    <DropdownMenuItem @click="deleteBook(book)"
                                        class="cursor-pointer text-destructive focus:text-destructive">
                                        <Trash2 class="w-4 h-4 mr-2" />
                                        Eliminar libro
                                    </DropdownMenuItem>
                                </DropdownMenuContent>
                            </DropdownMenu>
                        </div>
                    </CardHeader>

                    <CardContent class="px-5 pb-5 space-y-4">
                        <!-- Categorías -->
                        <div class="flex flex-wrap gap-2">
                            <Badge v-for="category in getCategories(book).slice(0, 2)" :key="category.id"
                                variant="outline"
                                class="text-xs bg-secondary/10 text-foreground border-secondary/20 rounded-full px-2.5 py-0.5">
                                {{ category.name }}
                            </Badge>
                            <Badge v-if="getCategories(book).length > 2" variant="outline"
                                class="text-xs bg-muted text-muted-foreground rounded-full">
                                +{{ getCategories(book).length - 2 }}
                            </Badge>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-3 gap-3 text-center">
                            <div class="space-y-1 p-2 bg-blue-500/10 rounded-lg hover:bg-blue-500/20 transition">
                                <Download class="w-4 h-4 text-blue-600 mx-auto" />
                                <p class="text-sm font-bold">{{ book.downloads_count || 0 }}</p>
                                <p class="text-xs text-muted-foreground">Descargas</p>
                            </div>
                            <div v-if="book.book_type !== 'digital'"
                                class="space-y-1 p-2 bg-emerald-500/10 rounded-lg hover:bg-emerald-500/20 transition">
                                <BookCopy class="w-4 h-4 text-emerald-600 mx-auto" />
                                <p class="text-sm font-bold">{{ book.physical_copies_count || 0 }}</p>
                                <p class="text-xs text-muted-foreground">Copias</p>
                            </div>
                            <div v-else class="space-y-1 p-2 bg-gray-500/10 rounded-lg hover:bg-gray-500/20 transition">
                                <FileDigit class="w-4 h-4 text-gray-600 mx-auto" />
                                <p class="text-sm font-bold text-foreground">Digital</p>
                                <p class="text-xs text-muted-foreground">Solo</p>
                            </div>
                            <div class="space-y-1 p-2 bg-purple-500/10 rounded-lg hover:bg-purple-500/20 transition">
                                <Users class="w-4 h-4 text-purple-600 mx-auto" />
                                <p class="text-sm font-bold">{{ book.loans_count || 0 }}</p>
                                <p class="text-xs text-muted-foreground">Préstamos</p>
                            </div>
                        </div>

                        <!-- Metadatos -->
                        <div class="space-y-2 text-sm">
                            <div v-for="(value, label) in {
                                ISBN: book.isbn || 'N/A',
                                Páginas: book.pages || 0,
                                Año: book.publication_year || 'N/A'
                            }" :key="label"
                                class="flex items-center justify-between p-2 bg-muted/40 rounded-lg hover:bg-muted/60 transition">
                                <span class="text-muted-foreground font-medium">{{ label }}:</span>
                                <span class="font-mono font-semibold text-foreground">{{ value }}</span>
                            </div>
                        </div>
                    </CardContent>

                    <CardFooter class="pt-0 px-5 pb-5">
                        <Button as-child variant="outline"
                            class="w-full border-primary/20 hover:bg-primary hover:text-white transition-all duration-200">
                            <a :href="`/admin/books/${book.id}`" class="flex items-center justify-center gap-2">
                                <Eye class="w-4 h-4" />
                                Ver Detalles
                            </a>
                        </Button>
                    </CardFooter>
                </Card>
            </div>

            <!-- Empty State - Consistente con Categories -->
            <div v-if="books.data.length === 0"
                class="text-center py-16 border-2 border-dashed border-border rounded-xl bg-card">
                <div class="max-w-md mx-auto">
                    <div class="p-4 bg-primary/10 rounded-full w-20 h-20 mx-auto mb-6 flex items-center justify-center">
                        <Library class="w-10 h-10 text-primary" />
                    </div>
                    <h3 class="text-2xl font-bold text-foreground mb-3">No se encontraron libros</h3>
                    <p class="text-muted-foreground text-lg mb-6">
                        {{ activeFiltersCount > 0 ? 'Intenta ajustar tus filtros de búsqueda' : 'Comienza agregando el primer libro al catálogo' }}
                    </p>
                    <Button as-child v-if="activeFiltersCount === 0"
                        class="bg-primary text-primary-foreground px-6 py-3 rounded-lg inline-flex items-center gap-2 hover:bg-primary/90 transition-colors">
                        <a href="/admin/books/create" class="flex items-center gap-2">
                            <Plus class="w-5 h-5" />
                            Crear Primer Libro
                        </a>
                    </Button>
                    <Button v-else variant="outline" @click="clearFilters"
                        class="border-primary/20 text-primary hover:bg-primary hover:text-primary-foreground">
                        <X class="w-4 h-4 mr-2" />
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
    -webkit-box-orient: vertical;
    overflow: hidden;
}

.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>