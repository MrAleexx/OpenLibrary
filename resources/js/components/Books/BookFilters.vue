<script setup lang="ts">
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { router } from '@inertiajs/vue3';
import { BookOpen, Filter, Layers, Search, X } from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

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

// Watch props to update local state if URL changes externally
watch(() => props.filters, (newFilters) => {
    searchInput.value = newFilters.search || '';
    selectedCategory.value = newFilters.category || '';
    selectedType.value = newFilters.type || '';
    selectedAvailability.value = newFilters.availability || '';
}, { deep: true });

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

const handleCategoryChange = (value: any) => {
    selectedCategory.value = value;
    applyFilters();
};

const handleTypeChange = (value: any) => {
    selectedType.value = value;
    applyFilters();
};

const handleAvailabilityChange = (value: any) => {
    selectedAvailability.value = value;
    applyFilters();
};
</script>

<template>
    <div class="space-y-6">
        <!-- Search Bar -->
        <div class="relative max-w-2xl">
            <Search class="absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
            <Input v-model="searchInput" type="text" placeholder="Buscar por título, autor, ISBN..."
                class="h-12 pl-9 pr-12 text-base" @keyup.enter="handleSearch" />
            <button v-if="searchInput" @click="
                searchInput = '';
            applyFilters();
            " class="absolute top-1/2 right-3 -translate-y-1/2 rounded-full p-1 transition-colors hover:bg-muted">
                <X class="h-4 w-4 text-muted-foreground" />
            </button>
        </div>

        <!-- Filters Grid -->
        <div class="grid grid-cols-1 gap-4 md:grid-cols-4">
            <!-- Category Filter -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm font-medium text-foreground">
                    <Layers class="h-4 w-4" />
                    Categoría
                </label>
                <Select :model-value="selectedCategory" @update:model-value="handleCategoryChange">
                    <SelectTrigger>
                        <SelectValue placeholder="Todas las categorías" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todas las categorías</SelectItem>
                        <SelectItem v-for="category in categories" :key="category.id" :value="category.id.toString()">
                            <span class="flex items-center gap-2">
                                {{ category.name }}
                                <span v-if="category.books_count" class="text-xs text-muted-foreground">
                                    ({{ category.books_count }})
                                </span>
                            </span>
                        </SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Type Filter -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm font-medium text-foreground">
                    <BookOpen class="h-4 w-4" />
                    Tipo
                </label>
                <Select :model-value="selectedType" @update:model-value="handleTypeChange">
                    <SelectTrigger>
                        <SelectValue placeholder="Todos los tipos" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todos los tipos</SelectItem>
                        <SelectItem value="physical">Solo físicos</SelectItem>
                        <SelectItem value="digital">Solo digitales</SelectItem>
                        <SelectItem value="both">Físicos y digitales</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Availability Filter -->
            <div class="space-y-2">
                <label class="flex items-center gap-2 text-sm font-medium text-foreground">
                    <Filter class="h-4 w-4" />
                    Disponibilidad
                </label>
                <Select :model-value="selectedAvailability" @update:model-value="handleAvailabilityChange">
                    <SelectTrigger>
                        <SelectValue placeholder="Todas" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem value="all">Todas</SelectItem>
                        <SelectItem value="available">Solo disponibles</SelectItem>
                        <SelectItem value="downloadable">Descargables</SelectItem>
                    </SelectContent>
                </Select>
            </div>

            <!-- Actions -->
            <div class="flex items-end gap-2">
                <Button @click="handleSearch" class="flex-1">
                    Buscar
                </Button>
                <Button v-if="hasActiveFilters" @click="clearFilters" variant="secondary" size="icon"
                    title="Limpiar filtros">
                    <X class="h-4 w-4" />
                </Button>
            </div>
        </div>

        <!-- Active Filters Summary -->
        <div v-if="hasActiveFilters" class="flex flex-wrap gap-2">
            <span class="text-sm text-muted-foreground self-center">Filtros activos:</span>
            <span v-if="searchInput"
                class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-sm text-primary">
                "{{ searchInput }}"
                <X class="h-3 w-3 cursor-pointer hover:text-primary/80" @click="
                    searchInput = '';
                applyFilters();
                " />
            </span>
            <span v-if="selectedCategory && selectedCategory !== 'all'"
                class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-sm text-primary">
                Categoría
                <X class="h-3 w-3 cursor-pointer hover:text-primary/80" @click="
                    selectedCategory = '';
                applyFilters();
                " />
            </span>
            <span v-if="selectedType && selectedType !== 'all'"
                class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-sm text-primary">
                Tipo: {{ selectedType === 'physical' ? 'Físico' : selectedType === 'digital' ? 'Digital' : 'Ambos' }}
                <X class="h-3 w-3 cursor-pointer hover:text-primary/80" @click="
                    selectedType = '';
                applyFilters();
                " />
            </span>
            <span v-if="selectedAvailability && selectedAvailability !== 'all'"
                class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-sm text-primary">
                {{
                    selectedAvailability === 'available'
                        ? 'Disponibles'
                        : 'Descargables'
                }}
                <X class="h-3 w-3 cursor-pointer hover:text-primary/80" @click="
                    selectedAvailability = '';
                applyFilters();
                " />
            </span>
        </div>
    </div>
</template>
