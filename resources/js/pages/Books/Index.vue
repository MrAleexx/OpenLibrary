<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import BookCard from '@/components/Books/BookCard.vue';
import BookFilters from '@/components/Books/BookFilters.vue';
import { BookOpen, ChevronLeft, ChevronRight, Library, Sparkles } from 'lucide-vue-next';
import { computed } from 'vue';

interface Book {
    id: number;
    title: string;
    cover_image: string | null;
    publication_year: number;
    pages: number;
    downloadable: boolean;
    book_type: 'physical' | 'digital' | 'both';
    total_views: number;
    available_copies_count?: number;
    physical_copies_count?: number;
    publisher?: {
        name: string;
    };
    contributors?: Array<{
        full_name: string;
        role: string;
    }>;
    categories?: Array<{
        name: string;
        color: string;
    }>;
}

interface Category {
    id: number;
    name: string;
    color: string;
    books_count?: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface Props {
    books: {
        data: Book[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        links: PaginationLink[];
    };
    categories: Category[];
    filters: {
        search?: string;
        category?: string;
        type?: string;
        availability?: string;
    };
    userLoanedBookIds?: number[];
}

const props = withDefaults(defineProps<Props>(), {
    userLoanedBookIds: () => []
});

const breadcrumbs = [
    { title: 'Inicio', href: '/' },
    { title: 'Catálogo de Libros', href: '/books' },
];

const hasBooks = computed(() => props.books.data.length > 0);
const totalBooks = computed(() => props.books.total);
const currentPage = computed(() => props.books.current_page);
const lastPage = computed(() => props.books.last_page);

const paginationRange = computed(() => {
    const range = [];
    const delta = 2; // Páginas a mostrar a cada lado de la actual
    
    for (let i = Math.max(2, currentPage.value - delta); i <= Math.min(lastPage.value - 1, currentPage.value + delta); i++) {
        range.push(i);
    }

    if (currentPage.value - delta > 2) {
        range.unshift('...');
    }
    if (currentPage.value + delta < lastPage.value - 1) {
        range.push('...');
    }

    range.unshift(1);
    if (lastPage.value > 1) {
        range.push(lastPage.value);
    }

    return range;
});

const resultText = computed(() => {
    const from = (currentPage.value - 1) * props.books.per_page + 1;
    const to = Math.min(currentPage.value * props.books.per_page, totalBooks.value);
    return `Mostrando ${from}-${to} de ${totalBooks.value} libros`;
});
</script>

<template>
    <Head title="Catálogo de Libros" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-8">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-4xl font-bold text-foreground flex items-center gap-3">
                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center">
                            <Library class="w-6 h-6 text-primary" />
                        </div>
                        Catálogo de Libros
                    </h1>
                    <p class="text-muted-foreground mt-2 flex items-center gap-2">
                        <BookOpen class="w-4 h-4" />
                        Explora nuestra colección de libros físicos y digitales
                    </p>
                </div>

                <!-- Stats Badge -->
                <div class="hidden md:flex items-center gap-2 px-6 py-3 bg-primary/10 text-primary rounded-xl border border-primary/20">
                    <Sparkles class="w-5 h-5" />
                    <div class="text-right">
                        <p class="text-2xl font-bold">{{ totalBooks }}</p>
                        <p class="text-xs text-primary/80">Libros disponibles</p>
                    </div>
                </div>
            </div>

            <!-- Filters -->
            <BookFilters :categories="categories" :filters="filters" />

            <!-- Results Info -->
            <div class="flex items-center justify-between">
                <p class="text-sm text-muted-foreground">
                    {{ resultText }}
                </p>
            </div>

            <!-- Books Grid -->
            <div v-if="hasBooks" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
                <BookCard 
                    v-for="book in books.data" 
                    :key="book.id" 
                    :book="book"
                    :user-has-loaned="userLoanedBookIds.includes(book.id)"
                />
            </div>

            <!-- Empty State -->
            <div v-else class="flex flex-col items-center justify-center py-20 space-y-4">
                <div class="w-20 h-20 bg-muted rounded-full flex items-center justify-center">
                    <BookOpen class="w-10 h-10 text-muted-foreground" />
                </div>
                <h3 class="text-xl font-semibold text-foreground">No se encontraron libros</h3>
                <p class="text-muted-foreground text-center max-w-md">
                    No hay libros que coincidan con los filtros seleccionados. 
                    Intenta ajustar tus criterios de búsqueda.
                </p>
                <Link
                    href="/books"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors"
                >
                    Ver todos los libros
                </Link>
            </div>

            <!-- Pagination -->
            <div v-if="hasBooks && lastPage > 1" class="flex items-center justify-center gap-2 pt-8">
                <!-- Previous Button -->
                <Link
                    v-if="books.links[0].url"
                    :href="books.links[0].url"
                    preserve-scroll
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-border bg-background hover:bg-muted transition-colors"
                >
                    <ChevronLeft class="w-4 h-4" />
                    <span class="hidden sm:inline">Anterior</span>
                </Link>
                <button
                    v-else
                    disabled
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-border bg-background opacity-50 cursor-not-allowed"
                >
                    <ChevronLeft class="w-4 h-4" />
                    <span class="hidden sm:inline">Anterior</span>
                </button>

                <!-- Page Numbers -->
                <div class="flex items-center gap-1">
                    <template v-for="(page, index) in paginationRange" :key="index">
                        <span
                            v-if="page === '...'"
                            class="px-4 py-2 text-muted-foreground"
                        >
                            ...
                        </span>
                        <Link
                            v-else-if="page !== currentPage"
                            :href="`/books?page=${page}`"
                            preserve-scroll
                            class="inline-flex items-center justify-center w-10 h-10 rounded-lg border border-border bg-background hover:bg-muted transition-colors"
                        >
                            {{ page }}
                        </Link>
                        <button
                            v-else
                            class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-primary text-primary-foreground font-medium"
                        >
                            {{ page }}
                        </button>
                    </template>
                </div>

                <!-- Next Button -->
                <Link
                    v-if="books.links[books.links.length - 1].url"
                    :href="books.links[books.links.length - 1].url!"
                    preserve-scroll
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-border bg-background hover:bg-muted transition-colors"
                >
                    <span class="hidden sm:inline">Siguiente</span>
                    <ChevronRight class="w-4 h-4" />
                </Link>
                <button
                    v-else
                    disabled
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-border bg-background opacity-50 cursor-not-allowed"
                >
                    <span class="hidden sm:inline">Siguiente</span>
                    <ChevronRight class="w-4 h-4" />
                </button>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
/* Animaciones para las cards */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.grid > * {
    animation: fadeInUp 0.5s ease-out;
    animation-fill-mode: both;
}

.grid > *:nth-child(1) { animation-delay: 0.05s; }
.grid > *:nth-child(2) { animation-delay: 0.1s; }
.grid > *:nth-child(3) { animation-delay: 0.15s; }
.grid > *:nth-child(4) { animation-delay: 0.2s; }
.grid > *:nth-child(5) { animation-delay: 0.25s; }
</style>
