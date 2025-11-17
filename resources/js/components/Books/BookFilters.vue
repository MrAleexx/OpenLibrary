<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { BookOpen, Filter, Layers, Search, X } from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Category {
    id: number;
    name: string;
    color: string;
    books_count?: number;
}

interface Props {
    categories: Category[];
    filters: {
        search?: string;
        category?: string;
        type?: string;
        availability?: string;
    };
}

const props = defineProps<Props>();

const searchInput = ref(props.filters.search || '');
const selectedCategory = ref(props.filters.category || '');
const selectedType = ref(props.filters.type || '');
const selectedAvailability = ref(props.filters.availability || '');

const hasActiveFilters = computed(() => {
    return (
        searchInput.value ||
        selectedCategory.value ||
        selectedType.value ||
        selectedAvailability.value
    );
});

const applyFilters = () => {
    router.get(
        '/books',
        {
            search: searchInput.value || undefined,
            category: selectedCategory.value || undefined,
            type: selectedType.value || undefined,
            availability: selectedAvailability.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const clearFilters = () => {
    searchInput.value = '';
    selectedCategory.value = '';
    selectedType.value = '';
    selectedAvailability.value = '';
    router.get(
        '/books',
        {},
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

const handleSearch = () => {
    applyFilters();
};
</script>

<template>
    <div class="space-y-4">
        <!-- Search Bar -->
        <div class="relative">
            <Search
                class="absolute top-1/2 left-4 h-5 w-5 -translate-y-1/2 text-muted-foreground"
            />
            <input
                v-model="searchInput"
                type="text"
                placeholder="Buscar por título, autor, ISBN..."
                class="w-full rounded-xl border border-border bg-background py-4 pr-12 pl-12 text-foreground transition-all placeholder:text-muted-foreground focus:border-primary focus:ring-2 focus:ring-primary/20"
                @keyup.enter="handleSearch"
            />
            <button
                v-if="searchInput"
                @click="
                    searchInput = '';
                    applyFilters();
                "
                class="absolute top-1/2 right-4 -translate-y-1/2 rounded-lg p-1 transition-colors hover:bg-muted"
            >
                <X class="h-5 w-5 text-muted-foreground" />
            </button>
        </div>

        <!-- Filters Grid -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <!-- Category Filter -->
            <div class="space-y-2">
                <label
                    class="flex items-center gap-2 text-sm font-medium text-foreground"
                >
                    <Layers class="h-4 w-4" />
                    Categoría
                </label>
                <select
                    v-model="selectedCategory"
                    @change="applyFilters"
                    class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-foreground transition-all focus:border-primary focus:ring-2 focus:ring-primary/20"
                >
                    <option value="">Todas las categorías</option>
                    <option
                        v-for="category in categories"
                        :key="category.id"
                        :value="category.id"
                    >
                        {{ category.name }}
                        <span v-if="category.books_count"
                            >({{ category.books_count }})</span
                        >
                    </option>
                </select>
            </div>

            <!-- Type Filter -->
            <div class="space-y-2">
                <label
                    class="flex items-center gap-2 text-sm font-medium text-foreground"
                >
                    <BookOpen class="h-4 w-4" />
                    Tipo
                </label>
                <select
                    v-model="selectedType"
                    @change="applyFilters"
                    class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-foreground transition-all focus:border-primary focus:ring-2 focus:ring-primary/20"
                >
                    <option value="">Todos los tipos</option>
                    <option value="physical">Solo físicos</option>
                    <option value="digital">Solo digitales</option>
                    <option value="both">Físicos y digitales</option>
                </select>
            </div>

            <!-- Availability Filter -->
            <div class="space-y-2">
                <label
                    class="flex items-center gap-2 text-sm font-medium text-foreground"
                >
                    <Filter class="h-4 w-4" />
                    Disponibilidad
                </label>
                <select
                    v-model="selectedAvailability"
                    @change="applyFilters"
                    class="w-full rounded-lg border border-border bg-background px-4 py-2.5 text-foreground transition-all focus:border-primary focus:ring-2 focus:ring-primary/20"
                >
                    <option value="">Todas</option>
                    <option value="available">Solo disponibles</option>
                    <option value="downloadable">Descargables</option>
                </select>
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2">
                <button
                    @click="handleSearch"
                    class="flex-1 rounded-lg bg-primary px-4 py-2.5 font-medium text-primary-foreground transition-colors hover:bg-primary/90"
                >
                    Buscar
                </button>
                <button
                    v-if="hasActiveFilters"
                    @click="clearFilters"
                    class="rounded-lg bg-muted px-4 py-2.5 text-muted-foreground transition-colors hover:bg-muted/80"
                    title="Limpiar filtros"
                >
                    <X class="h-5 w-5" />
                </button>
            </div>
        </div>

        <!-- Active Filters Summary -->
        <div v-if="hasActiveFilters" class="flex flex-wrap gap-2">
            <span class="text-sm text-muted-foreground">Filtros activos:</span>
            <span
                v-if="searchInput"
                class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-sm text-primary"
            >
                "{{ searchInput }}"
                <X
                    class="h-3 w-3 cursor-pointer"
                    @click="
                        searchInput = '';
                        applyFilters();
                    "
                />
            </span>
            <span
                v-if="selectedCategory"
                class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-sm text-primary"
            >
                Categoría
                <X
                    class="h-3 w-3 cursor-pointer"
                    @click="
                        selectedCategory = '';
                        applyFilters();
                    "
                />
            </span>
            <span
                v-if="selectedType"
                class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-sm text-primary"
            >
                Tipo: {{ selectedType }}
                <X
                    class="h-3 w-3 cursor-pointer"
                    @click="
                        selectedType = '';
                        applyFilters();
                    "
                />
            </span>
            <span
                v-if="selectedAvailability"
                class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-sm text-primary"
            >
                {{
                    selectedAvailability === 'available'
                        ? 'Disponibles'
                        : 'Descargables'
                }}
                <X
                    class="h-3 w-3 cursor-pointer"
                    @click="
                        selectedAvailability = '';
                        applyFilters();
                    "
                />
            </span>
        </div>
    </div>
</template>
