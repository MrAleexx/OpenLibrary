<!-- resources/js/pages/admin/categories/Create.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { BreadcrumbItem } from '@/types';
import { ArrowLeft, Save, ChevronUp, ChevronDown } from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface ParentCategory {
    id: number;
    name: string;
}

interface Props {
    parentCategories: ParentCategory[];
    nextSortOrder: number;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Categorías', href: '/admin/categories' },
    { title: 'Crear Categoría', href: '/admin/categories/create' },
];

const form = ref({
    name: '',
    slug: '',
    description: '',
    parent_id: null as number | null,
    sort_order: props.nextSortOrder,
    is_active: true,
    image: '',
    meta_title: '',
    meta_description: '',
});

// Computed para obtener los órdenes disponibles
const availableOrders = computed(() => {
    const orders = [];
    for (let i = 1; i <= props.nextSortOrder; i++) {
        orders.push(i);
    }
    return orders;
});

const submit = () => {
    router.post('/admin/categories', form.value);
};

// Generar slug automáticamente desde el nombre
const generateSlug = () => {
    if (!form.value.slug) {
        form.value.slug = form.value.name
            .toLowerCase()
            .replace(/[^\w ]+/g, '')
            .replace(/ +/g, '-');
    }
};

// Incrementar orden
const incrementOrder = () => {
    if (form.value.sort_order < props.nextSortOrder) {
        form.value.sort_order++;
    }
};

// Decrementar orden
const decrementOrder = () => {
    if (form.value.sort_order > 1) {
        form.value.sort_order--;
    }
};
</script>

<template>
    <Head title="Crear Categoría" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-4xl mx-auto">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-4 mb-4">
                    <Link
                        href="/admin/categories"
                        class="p-2 text-muted-foreground hover:text-foreground transition-colors rounded-lg hover:bg-accent"
                    >
                        <ArrowLeft class="w-5 h-5" />
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">Crear Nueva Categoría</h1>
                        <p class="text-muted-foreground mt-2">
                            Agrega una nueva categoría o subcategoría a tu biblioteca
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="bg-card rounded-xl border border-border shadow-lg p-6">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Nombre -->
                        <div class="space-y-2">
                            <label for="name" class="block text-sm font-medium text-foreground">
                                Nombre de la Categoría *
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                @input="generateSlug"
                                class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-background text-foreground"
                                placeholder="Ej: Ciencias de la Computación"
                            />
                        </div>

                        <!-- Slug -->
                        <div class="space-y-2">
                            <label for="slug" class="block text-sm font-medium text-foreground">
                                Slug *
                            </label>
                            <input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                required
                                class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-background text-foreground font-mono"
                                placeholder="Ej: ciencias-computacion"
                            />
                        </div>

                        <!-- Categoría Padre -->
                        <div class="space-y-2">
                            <label for="parent_id" class="block text-sm font-medium text-foreground">
                                Categoría Padre
                            </label>
                            <select
                                id="parent_id"
                                v-model="form.parent_id"
                                class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-background text-foreground"
                            >
                                <option :value="null">Ninguna (Categoría Principal)</option>
                                <option 
                                    v-for="parent in parentCategories" 
                                    :key="parent.id" 
                                    :value="parent.id"
                                >
                                    {{ parent.name }}
                                </option>
                            </select>
                        </div>

                        <!-- Orden - Mejorado con controles -->
                        <div class="space-y-2">
                            <label for="sort_order" class="block text-sm font-medium text-foreground">
                                Orden de Visualización
                            </label>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="decrementOrder"
                                    :disabled="form.sort_order <= 1"
                                    class="p-2 border border-border rounded-lg hover:bg-accent transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <ChevronDown class="w-4 h-4" />
                                </button>
                                
                                <select
                                    id="sort_order"
                                    v-model.number="form.sort_order"
                                    required
                                    class="flex-1 px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-background text-foreground"
                                >
                                    <option 
                                        v-for="order in availableOrders" 
                                        :key="order" 
                                        :value="order"
                                        :selected="order === form.sort_order"
                                    >
                                        Posición {{ order }}
                                    </option>
                                </select>

                                <button
                                    type="button"
                                    @click="incrementOrder"
                                    :disabled="form.sort_order >= props.nextSortOrder"
                                    class="p-2 border border-border rounded-lg hover:bg-accent transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                >
                                    <ChevronUp class="w-4 h-4" />
                                </button>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Las categorías se mostrarán en este orden en la navegación
                            </p>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="space-y-2">
                        <label for="description" class="block text-sm font-medium text-foreground">
                            Descripción
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full px-3 py-2 border border-border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent bg-background text-foreground"
                            placeholder="Descripción opcional de la categoría..."
                        ></textarea>
                    </div>

                    <!-- Estado -->
                    <div class="flex items-center gap-2">
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="w-4 h-4 text-primary border-border rounded focus:ring-primary focus:ring-2"
                        />
                        <label for="is_active" class="text-sm font-medium text-foreground">
                            Categoría activa
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-4 pt-6 border-t border-border">
                        <Link
                            href="/admin/categories"
                            class="px-4 py-2 text-foreground border border-border rounded-lg hover:bg-accent transition-colors"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            class="bg-primary text-primary-foreground px-6 py-2 rounded-lg flex items-center gap-2 hover:bg-primary/90 transition-colors shadow-lg shadow-primary/25"
                        >
                            <Save class="w-4 h-4" />
                            Crear Categoría
                        </button>
                    </div>
                </form>
            </div>

            <!-- Información del orden -->
            <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 bg-blue-100 rounded-full flex items-center justify-center mt-0.5">
                        <span class="text-blue-600 text-sm font-bold">i</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-blue-900">Sobre el orden de categorías</h3>
                        <p class="text-blue-700 text-sm mt-1">
                            El orden determina la posición en que aparecerán las categorías en la navegación. 
                            Las categorías se ordenan de menor a mayor número.
                        </p>
                        <p class="text-blue-700 text-sm mt-1">
                            <strong>Orden actual sugerido:</strong> {{ form.sort_order }} de {{ props.nextSortOrder }} posiciones disponibles
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>