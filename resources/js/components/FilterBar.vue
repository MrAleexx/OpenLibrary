<!-- resources/js/components/FilterBar.vue -->
<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { Filter, X, Search, BookMarked, FileText, Sparkles, ChevronDown } from 'lucide-vue-next'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {
    Command,
    CommandEmpty,
    CommandGroup,
    CommandInput,
    CommandItem,
    CommandList
} from '@/components/ui/command'
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover'

const props = defineProps<{
    modelValue: {
        search?: string
        category?: string
        bookType?: string
        status?: string
    }
    categories: Array<{ id: number; name: string }>
}>()

const emit = defineEmits<{
    'update:modelValue': [value: typeof props.modelValue]
}>()

const filters = ref({ ...props.modelValue })
const isOpen = ref(false)

const bookTypes = [
    { value: 'digital', label: 'Digital' },
    { value: 'physical', label: 'Físico' },
    { value: 'both', label: 'Híbrido' }
]

const statusTypes = [
    { value: 'active', label: 'Activos' },
    { value: 'inactive', label: 'Inactivos' },
    { value: 'featured', label: 'Destacados' }
]

const activeFiltersCount = computed(() => {
    let count = 0
    if (filters.value.search) count++
    if (filters.value.category) count++
    if (filters.value.bookType) count++
    if (filters.value.status) count++
    return count
})

const getCategoryName = (id: string) => {
    return props.categories.find(c => c.id.toString() === id)?.name || id
}

const getBookTypeLabel = (value: string) => {
    return bookTypes.find(t => t.value === value)?.label || value
}

const getStatusLabel = (value: string) => {
    return statusTypes.find(s => s.value === value)?.label || value
}

watch(filters, (newValue) => {
    emit('update:modelValue', newValue)
}, { deep: true })

function clearFilters() {
    filters.value = {
        search: '',
        category: '',
        bookType: '',
        status: ''
    }
}

function handleSelect(value: string, type: 'category' | 'bookType' | 'status') {
    // Toggle selection - if already selected, clear it
    if (filters.value[type] === value) {
        filters.value[type] = ''
    } else {
        filters.value[type] = value
    }
}

// Computed para el texto del trigger
const triggerText = computed(() => {
    if (filters.value.search) {
        return `Búsqueda: "${filters.value.search}"`
    }
    return 'Buscar por título, ISBN, autor o editorial...'
})
</script>

<template>
    <div class="bg-card rounded-xl border border-border p-6 space-y-6">
        <div class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="p-2 bg-primary/10 rounded-lg">
                    <Filter class="w-5 h-5 text-primary" />
                </div>
                <div>
                    <h2 class="text-xl font-bold text-foreground">Filtros y Búsqueda</h2>
                    <p class="text-muted-foreground text-sm">Encuentra libros específicos en el catálogo</p>
                </div>
            </div>
            <Button v-if="activeFiltersCount > 0" variant="outline" size="sm" @click="clearFilters"
                class="border-destructive/20 text-destructive hover:bg-destructive/10 hover:text-destructive">
                <X class="w-4 h-4 mr-1" />
                Limpiar ({{ activeFiltersCount }})
            </Button>
        </div>

        <!-- Search Input Separado -->
        <div class="flex-1 w-full">
            <div class="relative">
                <Search class="w-4 h-4 text-muted-foreground absolute left-3 top-1/2 transform -translate-y-1/2" />
                <input 
                    v-model="filters.search"
                    type="text" 
                    placeholder="Buscar por título, ISBN, autor o editorial..."
                    class="w-full pl-10 pr-4 py-3 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-background text-foreground text-lg"
                />
            </div>
        </div>

        <!-- Filtros en Popover -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Category Filter -->
            <div class="space-y-2">
                <label class="text-sm font-medium text-foreground flex items-center gap-2">
                    <BookMarked class="w-4 h-4 text-primary" />
                    Categoría
                </label>
                <Popover v-model:open="isOpen">
                    <PopoverTrigger as-child>
                        <Button variant="outline" role="combobox" class="w-full justify-between">
                            <span class="truncate">
                                {{ filters.category ? getCategoryName(filters.category) : 'Todas las categorías' }}
                            </span>
                            <ChevronDown class="w-4 h-4 ml-2 opacity-50" />
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-full p-0">
                        <Command>
                            <CommandInput placeholder="Buscar categoría..." />
                            <CommandList>
                                <CommandEmpty>No se encontraron categorías.</CommandEmpty>
                                <CommandGroup>
                                    <CommandItem value="" @select="filters.category = ''">
                                        <span>Todas las categorías</span>
                                    </CommandItem>
                                    <CommandItem 
                                        v-for="category in categories" 
                                        :key="category.id"
                                        :value="category.id.toString()"
                                        @select="handleSelect(category.id.toString(), 'category')"
                                    >
                                        <BookMarked class="w-4 h-4 mr-2" />
                                        {{ category.name }}
                                    </CommandItem>
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    </PopoverContent>
                </Popover>
            </div>

            <!-- Book Type Filter -->
            <div class="space-y-2">
                <label class="text-sm font-medium text-foreground flex items-center gap-2">
                    <FileText class="w-4 h-4 text-primary" />
                    Tipo de Libro
                </label>
                <Popover v-model:open="isOpen">
                    <PopoverTrigger as-child>
                        <Button variant="outline" role="combobox" class="w-full justify-between">
                            <span class="truncate">
                                {{ filters.bookType ? getBookTypeLabel(filters.bookType) : 'Todos los tipos' }}
                            </span>
                            <ChevronDown class="w-4 h-4 ml-2 opacity-50" />
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-full p-0">
                        <Command>
                            <CommandList>
                                <CommandGroup>
                                    <CommandItem value="" @select="filters.bookType = ''">
                                        <span>Todos los tipos</span>
                                    </CommandItem>
                                    <CommandItem 
                                        v-for="type in bookTypes"
                                        :key="type.value"
                                        :value="type.value"
                                        @select="handleSelect(type.value, 'bookType')"
                                    >
                                        <FileText class="w-4 h-4 mr-2" />
                                        {{ type.label }}
                                    </CommandItem>
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    </PopoverContent>
                </Popover>
            </div>

            <!-- Status Filter -->
            <div class="space-y-2">
                <label class="text-sm font-medium text-foreground flex items-center gap-2">
                    <Sparkles class="w-4 h-4 text-primary" />
                    Estado
                </label>
                <Popover v-model:open="isOpen">
                    <PopoverTrigger as-child>
                        <Button variant="outline" role="combobox" class="w-full justify-between">
                            <span class="truncate">
                                {{ filters.status ? getStatusLabel(filters.status) : 'Todos los estados' }}
                            </span>
                            <ChevronDown class="w-4 h-4 ml-2 opacity-50" />
                        </Button>
                    </PopoverTrigger>
                    <PopoverContent class="w-full p-0">
                        <Command>
                            <CommandList>
                                <CommandGroup>
                                    <CommandItem value="" @select="filters.status = ''">
                                        <span>Todos los estados</span>
                                    </CommandItem>
                                    <CommandItem 
                                        v-for="status in statusTypes"
                                        :key="status.value"
                                        :value="status.value"
                                        @select="handleSelect(status.value, 'status')"
                                    >
                                        <Sparkles class="w-4 h-4 mr-2" />
                                        {{ status.label }}
                                    </CommandItem>
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    </PopoverContent>
                </Popover>
            </div>
        </div>

        <!-- Active Filters Info -->
        <div v-if="activeFiltersCount > 0" class="pt-4 border-t border-border">
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
                <Filter class="w-4 h-4" />
                <span>Filtros activos:</span>
                <span v-if="filters.search" class="bg-primary/10 text-primary px-2 py-1 rounded text-xs">
                    Búsqueda: "{{ filters.search }}"
                </span>
                <span v-if="filters.category" class="bg-secondary/10 text-secondary px-2 py-1 rounded text-xs">
                    Categoría: {{ getCategoryName(filters.category) }}
                </span>
                <span v-if="filters.bookType" class="bg-primary/10 text-primary px-2 py-1 rounded text-xs">
                    Tipo: {{ getBookTypeLabel(filters.bookType) }}
                </span>
                <span v-if="filters.status" class="bg-secondary/10 text-secondary px-2 py-1 rounded text-xs">
                    Estado: {{ getStatusLabel(filters.status) }}
                </span>
            </div>
        </div>
    </div>
</template>