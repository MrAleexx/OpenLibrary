<!-- resources/js/components/FilterBar.vue -->
<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    BookCopy,
    BookMarked,
    BookOpen,
    Clock,
    FileText,
    Filter,
    Globe,
    IdCard,
    Library,
    Search,
    Shield,
    Sparkles,
    User,
    UserCheck,
    Users,
    UserX,
    X,
} from 'lucide-vue-next';
import { computed, ref, watch, type Component } from 'vue';

// Tipos para la configuración del FilterBar
interface FilterOption {
    value: string;
    label: string;
    icon?: Component;
}

interface FilterConfig {
    key: string;
    label: string;
    type: 'select' | 'search';
    options?: FilterOption[];
    placeholder?: string;
    icon?: Component;
}

interface FilterBarProps {
    modelValue: Record<string, any>;
    config: {
        module: 'users' | 'books' | 'loans' | 'categories' | 'reservations';
        filters?: FilterConfig[];
        searchPlaceholder?: string;
        title?: string;
        description?: string;
    };
    data?: {
        categories?: Array<{ id: number; name: string }>;
        [key: string]: any;
    };
}

const props = defineProps<FilterBarProps>();
const emit = defineEmits<{
    'update:modelValue': [value: Record<string, any>];
}>();

// Valores locales para los filtros
const localFilters = ref({ ...props.modelValue });

// Configuración por defecto basada en el módulo
const defaultConfig = computed(() => {
    const defaults: Record<string, any> = {
        users: {
            title: 'Filtros y Búsqueda',
            description: 'Encuentra usuarios específicos en el sistema',
            searchPlaceholder:
                'Buscar por nombre, email, DNI o código institucional...',
            filters: [
                {
                    key: 'user_type',
                    label: 'Tipo de Usuario',
                    type: 'select',
                    placeholder: 'Todos los tipos',
                    icon: Users,
                    options: [
                        { value: 'student', label: 'Estudiante', icon: Users },
                        { value: 'teacher', label: 'Docente', icon: Shield },
                        { value: 'external', label: 'Externo', icon: User },
                        {
                            value: 'librarian',
                            label: 'Bibliotecario',
                            icon: IdCard,
                        },
                        {
                            value: 'admin',
                            label: 'Administrador',
                            icon: Shield,
                        },
                    ],
                },
                {
                    key: 'status',
                    label: 'Estado',
                    type: 'select',
                    placeholder: 'Todos los estados',
                    icon: UserCheck,
                    options: [
                        { value: 'active', label: 'Activos', icon: UserCheck },
                        { value: 'inactive', label: 'Inactivos', icon: UserX },
                    ],
                },
                {
                    key: 'membership_status',
                    label: 'Membresía',
                    type: 'select',
                    placeholder: 'Estado membresía',
                    icon: Clock,
                    options: [
                        {
                            value: 'active',
                            label: 'Membresía Activa',
                            icon: UserCheck,
                        },
                        {
                            value: 'expired',
                            label: 'Membresía Expirada',
                            icon: Clock,
                        },
                    ],
                },
            ],
        },
        books: {
            title: 'Filtros y Búsqueda',
            description: 'Encuentra libros específicos en el catálogo',
            searchPlaceholder: 'Buscar por título, ISBN, autor o editorial...',
            filters: [
                {
                    key: 'book_type',
                    label: 'Tipo de Libro',
                    type: 'select',
                    placeholder: 'Todos los tipos',
                    icon: BookOpen,
                    options: [
                        { value: 'digital', label: 'Digital', icon: FileText },
                        { value: 'physical', label: 'Físico', icon: BookCopy },
                        { value: 'both', label: 'Híbrido', icon: Globe },
                    ],
                },
                {
                    key: 'status',
                    label: 'Estado',
                    type: 'select',
                    placeholder: 'Todos los estados',
                    icon: Sparkles,
                    options: [
                        { value: 'active', label: 'Activos', icon: Sparkles },
                        {
                            value: 'inactive',
                            label: 'Inactivos',
                            icon: BookOpen,
                        },
                        {
                            value: 'featured',
                            label: 'Destacados',
                            icon: Sparkles,
                        },
                    ],
                },
                {
                    key: 'category',
                    label: 'Categoría',
                    type: 'select',
                    placeholder: 'Todas las categorías',
                    icon: BookMarked,
                    options:
                        props.data?.categories?.map((cat) => ({
                            value: cat.id.toString(),
                            label: cat.name,
                            icon: BookMarked,
                        })) || [],
                },
            ],
        },
        categories: {
            title: 'Filtros y Búsqueda',
            description: 'Encuentra categorías específicas',
            searchPlaceholder: 'Buscar por nombre de categoría...',
            filters: [
                {
                    key: 'status',
                    label: 'Estado',
                    type: 'select',
                    placeholder: 'Todos los estados',
                    icon: Sparkles,
                    options: [
                        { value: 'active', label: 'Activas', icon: Sparkles },
                        {
                            value: 'inactive',
                            label: 'Inactivas',
                            icon: Library,
                        },
                    ],
                },
            ],
        },
    };

    return (
        defaults[props.config.module] || {
            title: 'Filtros y Búsqueda',
            description: 'Filtra y busca elementos',
            searchPlaceholder: 'Buscar...',
            filters: [],
        }
    );
});

// Combinar configuración por defecto con props
const finalConfig = computed(() => ({
    ...defaultConfig.value,
    ...props.config,
    filters: props.config.filters?.length
        ? props.config.filters
        : defaultConfig.value.filters,
}));

// Contador de filtros activos
const activeFiltersCount = computed(() => {
    let count = 0;
    Object.values(localFilters.value).forEach((value) => {
        if (value !== '' && value !== null && value !== undefined) {
            count++;
        }
    });
    return count;
});

// Emitir cambios con debounce
function debounce(fn: Function, wait: number) {
    let timeout: number | undefined;
    return function (this: any, ...args: any[]) {
        clearTimeout(timeout);
        timeout = window.setTimeout(() => fn.apply(this, args), wait);
    };
}

const debouncedEmit = debounce((filters: any) => {
    emit('update:modelValue', filters);
}, 300);

watch(
    localFilters,
    (newValue) => {
        debouncedEmit(newValue);
    },
    { deep: true },
);

// Métodos
function clearFilters() {
    const clearedFilters: Record<string, any> = {};
    Object.keys(localFilters.value).forEach((key) => {
        clearedFilters[key] = '';
    });
    localFilters.value = clearedFilters;
}

function getFilterDisplayValue(
    filterConfig: FilterConfig,
    value: string,
): string {
    if (!value) return filterConfig.placeholder || '';

    if (filterConfig.key === 'category' && props.data?.categories) {
        const category = props.data.categories.find(
            (cat) => cat.id.toString() === value,
        );
        return category?.name || value;
    }

    const option = filterConfig.options?.find((opt) => opt.value === value);
    return option?.label || value;
}

function getFilterIcon(filterConfig: FilterConfig, value: string): Component {
    if (!value) return filterConfig.icon || Filter;

    const option = filterConfig.options?.find((opt) => opt.value === value);
    return option?.icon || filterConfig.icon || Filter;
}
</script>

<template>
    <div class="rounded-xl border border-border bg-card shadow-lg">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div
                class="flex flex-col items-start justify-between gap-4 sm:flex-row sm:items-center"
            >
                <div class="flex items-center gap-2">
                    <Filter class="h-4 w-4 text-muted-foreground" />
                    <div>
                        <h3 class="text-lg font-semibold text-foreground">
                            {{ finalConfig.title }}
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            {{ finalConfig.description }}
                        </p>
                    </div>
                    <Badge
                        v-if="activeFiltersCount > 0"
                        variant="secondary"
                        class="border-primary/20 bg-primary/10 text-primary"
                    >
                        {{ activeFiltersCount }} activos
                    </Badge>
                </div>
                <Button
                    v-if="activeFiltersCount > 0"
                    variant="outline"
                    size="sm"
                    @click="clearFilters"
                    class="border-primary/20 text-primary hover:bg-primary hover:text-primary-foreground"
                >
                    <X class="mr-1 h-4 w-4" />
                    Limpiar
                </Button>
            </div>

            <!-- Search -->
            <div class="relative">
                <Search
                    class="absolute top-3 left-3 h-4 w-4 text-muted-foreground"
                />
                <Input
                    v-model="localFilters.search"
                    :placeholder="finalConfig.searchPlaceholder"
                    class="border-border bg-background pl-10 focus:border-primary"
                />
            </div>

            <!-- Filtros Dinámicos -->
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="filterItem in finalConfig.filters"
                    :key="filterItem.key"
                    class="space-y-2"
                >
                    <label
                        class="flex items-center gap-2 text-sm font-medium text-foreground"
                    >
                        <component
                            :is="
                                getFilterIcon(
                                    filterItem,
                                    localFilters[filterItem.key],
                                )
                            "
                            class="h-4 w-4 text-primary"
                        />
                        {{ filterItem.label }}
                    </label>
                    <Select
                        v-model="localFilters[filterItem.key]"
                        v-if="filterItem.type === 'select'"
                    >
                        <SelectTrigger
                            class="border-border bg-background focus:border-primary"
                        >
                            <SelectValue :placeholder="filterItem.placeholder">
                                <span class="flex items-center gap-2">
                                    <component
                                        :is="
                                            getFilterIcon(
                                                filterItem,
                                                localFilters[filterItem.key],
                                            )
                                        "
                                        class="h-3 w-3"
                                    />
                                    {{
                                        getFilterDisplayValue(
                                            filterItem,
                                            localFilters[filterItem.key],
                                        )
                                    }}
                                </span>
                            </SelectValue>
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="">
                                <span class="flex items-center gap-2">
                                    <component
                                        :is="filterItem.icon || Filter"
                                        class="h-3 w-3"
                                    />
                                    {{ filterItem.placeholder }}
                                </span>
                            </SelectItem>
                            <SelectItem
                                v-for="option in filterItem.options"
                                :key="option.value"
                                :value="option.value"
                            >
                                <span class="flex items-center gap-2">
                                    <component
                                        :is="
                                            option.icon ||
                                            filterItem.icon ||
                                            Filter
                                        "
                                        class="h-3 w-3"
                                    />
                                    {{ option.label }}
                                </span>
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            </div>

            <!-- Active Filters Info -->
            <div
                v-if="activeFiltersCount > 0"
                class="border-t border-border pt-4"
            >
                <div
                    class="flex flex-wrap items-center gap-2 text-sm text-muted-foreground"
                >
                    <Filter class="h-4 w-4" />
                    <span>Filtros activos:</span>
                    <template
                        v-for="filterItem in finalConfig.filters"
                        :key="filterItem.key"
                    >
                        <span
                            v-if="localFilters[filterItem.key]"
                            class="flex items-center gap-1 rounded bg-primary/10 px-2 py-1 text-xs text-primary"
                        >
                            <component
                                :is="
                                    getFilterIcon(
                                        filterItem,
                                        localFilters[filterItem.key],
                                    )
                                "
                                class="h-3 w-3"
                            />
                            {{ filterItem.label }}:
                            {{
                                getFilterDisplayValue(
                                    filterItem,
                                    localFilters[filterItem.key],
                                )
                            }}
                        </span>
                    </template>
                    <span
                        v-if="localFilters.search"
                        class="flex items-center gap-1 rounded bg-secondary/10 px-2 py-1 text-xs text-secondary"
                    >
                        <Search class="h-3 w-3" />
                        Búsqueda: "{{ localFilters.search }}"
                    </span>
                </div>
            </div>
        </div>
    </div>
</template>
