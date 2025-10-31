<script setup lang="ts">
import { Search, X, Filter, BookOpen, Download, Layers } from 'lucide-vue-next';
import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

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
    return searchInput.value || selectedCategory.value || selectedType.value || selectedAvailability.value;
});

const applyFilters = () => {
    router.get('/books', {
        search: searchInput.value || undefined,
        category: selectedCategory.value || undefined,
        type: selectedType.value || undefined,
        availability: selectedAvailability.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    searchInput.value = '';
    selectedCategory.value = '';
    selectedType.value = '';
    selectedAvailability.value = '';
    router.get('/books', {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const handleSearch = () => {
    applyFilters();
};
</script>

<template>
    <div class="space-y-4">
        <!-- Search Bar -->
        <div class="relative">
            <Search class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-muted-foreground" />
            <input
                v-model="searchInput"
                type="text"
                placeholder="Buscar por título, autor, ISBN..."
                class="w-full pl-12 pr-12 py-4 rounded-xl border border-border bg-background text-foreground placeholder:text-muted-foreground focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                @keyup.enter="handleSearch"
            />
            <button
                v-if="searchInput"
                @click="searchInput = ''; applyFilters();"
                class="absolute right-4 top-1/2 -translate-y-1/2 p-1 hover:bg-muted rounded-lg transition-colors"
            >
                <X class="w-5 h-5 text-muted-foreground" />
            </button>
        </div>

        <!-- Filters Grid -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Category Filter -->
            <div class="space-y-2">
                <label class="text-sm font-medium text-foreground flex items-center gap-2">
                    <Layers class="w-4 h-4" />
                    Categoría
                </label>
                <select
                    v-model="selectedCategory"
                    @change="applyFilters"
                    class="w-full px-4 py-2.5 rounded-lg border border-border bg-background text-foreground focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                >
                    <option value="">Todas las categorías</option>
                    <option 
                        v-for="category in categories" 
                        :key="category.id" 
                        :value="category.id"
                    >
                        {{ category.name }}
                        <span v-if="category.books_count">({{ category.books_count }})</span>
                    </option>
                </select>
            </div>

            <!-- Type Filter -->
            <div class="space-y-2">
                <label class="text-sm font-medium text-foreground flex items-center gap-2">
                    <BookOpen class="w-4 h-4" />
                    Tipo
                </label>
                <select
                    v-model="selectedType"
                    @change="applyFilters"
                    class="w-full px-4 py-2.5 rounded-lg border border-border bg-background text-foreground focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
                >
                    <option value="">Todos los tipos</option>
                    <option value="physical">Solo físicos</option>
                    <option value="digital">Solo digitales</option>
                    <option value="both">Físicos y digitales</option>
                </select>
            </div>

            <!-- Availability Filter -->
            <div class="space-y-2">
                <label class="text-sm font-medium text-foreground flex items-center gap-2">
                    <Filter class="w-4 h-4" />
                    Disponibilidad
                </label>
                <select
                    v-model="selectedAvailability"
                    @change="applyFilters"
                    class="w-full px-4 py-2.5 rounded-lg border border-border bg-background text-foreground focus:border-primary focus:ring-2 focus:ring-primary/20 transition-all"
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
                    class="flex-1 px-4 py-2.5 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors font-medium"
                >
                    Buscar
                </button>
                <button
                    v-if="hasActiveFilters"
                    @click="clearFilters"
                    class="px-4 py-2.5 bg-muted text-muted-foreground rounded-lg hover:bg-muted/80 transition-colors"
                    title="Limpiar filtros"
                >
                    <X class="w-5 h-5" />
                </button>
            </div>
        </div>

        <!-- Active Filters Summary -->
        <div v-if="hasActiveFilters" class="flex flex-wrap gap-2">
            <span class="text-sm text-muted-foreground">Filtros activos:</span>
            <span v-if="searchInput" class="inline-flex items-center gap-1 px-3 py-1 bg-primary/10 text-primary rounded-full text-sm">
                "{{ searchInput }}"
                <X class="w-3 h-3 cursor-pointer" @click="searchInput = ''; applyFilters();" />
            </span>
            <span v-if="selectedCategory" class="inline-flex items-center gap-1 px-3 py-1 bg-primary/10 text-primary rounded-full text-sm">
                Categoría
                <X class="w-3 h-3 cursor-pointer" @click="selectedCategory = ''; applyFilters();" />
            </span>
            <span v-if="selectedType" class="inline-flex items-center gap-1 px-3 py-1 bg-primary/10 text-primary rounded-full text-sm">
                Tipo: {{ selectedType }}
                <X class="w-3 h-3 cursor-pointer" @click="selectedType = ''; applyFilters();" />
            </span>
            <span v-if="selectedAvailability" class="inline-flex items-center gap-1 px-3 py-1 bg-primary/10 text-primary rounded-full text-sm">
                {{ selectedAvailability === 'available' ? 'Disponibles' : 'Descargables' }}
                <X class="w-3 h-3 cursor-pointer" @click="selectedAvailability = ''; applyFilters();" />
            </span>
        </div>
    </div>
</template>
