<!-- resources/js/pages/admin/categories/Create.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { BreadcrumbItem } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { ArrowLeft, ChevronDown, ChevronUp, Save } from 'lucide-vue-next';
import { computed, ref } from 'vue';

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
        <div class="mx-auto max-w-4xl p-6">
            <!-- Header -->
            <div class="mb-8">
                <div class="mb-4 flex items-center gap-4">
                    <Link
                        href="/admin/categories"
                        class="rounded-lg p-2 text-muted-foreground transition-colors hover:bg-accent hover:text-foreground"
                    >
                        <ArrowLeft class="h-5 w-5" />
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">
                            Crear Nueva Categoría
                        </h1>
                        <p class="mt-2 text-muted-foreground">
                            Agrega una nueva categoría o subcategoría a tu
                            biblioteca
                        </p>
                    </div>
                </div>
            </div>

            <!-- Form -->
            <div class="rounded-xl border border-border bg-card p-6 shadow-lg">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                        <!-- Nombre -->
                        <div class="space-y-2">
                            <label
                                for="name"
                                class="block text-sm font-medium text-foreground"
                            >
                                Nombre de la Categoría *
                            </label>
                            <input
                                id="name"
                                v-model="form.name"
                                type="text"
                                required
                                @input="generateSlug"
                                class="w-full rounded-lg border border-border bg-background px-3 py-2 text-foreground focus:border-transparent focus:ring-2 focus:ring-primary focus:outline-none"
                                placeholder="Ej: Ciencias de la Computación"
                            />
                        </div>

                        <!-- Slug -->
                        <div class="space-y-2">
                            <label
                                for="slug"
                                class="block text-sm font-medium text-foreground"
                            >
                                Slug *
                            </label>
                            <input
                                id="slug"
                                v-model="form.slug"
                                type="text"
                                required
                                class="w-full rounded-lg border border-border bg-background px-3 py-2 font-mono text-foreground focus:border-transparent focus:ring-2 focus:ring-primary focus:outline-none"
                                placeholder="Ej: ciencias-computacion"
                            />
                        </div>

                        <!-- Categoría Padre -->
                        <div class="space-y-2">
                            <label
                                for="parent_id"
                                class="block text-sm font-medium text-foreground"
                            >
                                Categoría Padre
                            </label>
                            <select
                                id="parent_id"
                                v-model="form.parent_id"
                                class="w-full rounded-lg border border-border bg-background px-3 py-2 text-foreground focus:border-transparent focus:ring-2 focus:ring-primary focus:outline-none"
                            >
                                <option :value="null">
                                    Ninguna (Categoría Principal)
                                </option>
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
                            <label
                                for="sort_order"
                                class="block text-sm font-medium text-foreground"
                            >
                                Orden de Visualización
                            </label>
                            <div class="flex items-center gap-2">
                                <button
                                    type="button"
                                    @click="decrementOrder"
                                    :disabled="form.sort_order <= 1"
                                    class="rounded-lg border border-border p-2 transition-colors hover:bg-accent disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <ChevronDown class="h-4 w-4" />
                                </button>

                                <select
                                    id="sort_order"
                                    v-model.number="form.sort_order"
                                    required
                                    class="flex-1 rounded-lg border border-border bg-background px-3 py-2 text-foreground focus:border-transparent focus:ring-2 focus:ring-primary focus:outline-none"
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
                                    :disabled="
                                        form.sort_order >= props.nextSortOrder
                                    "
                                    class="rounded-lg border border-border p-2 transition-colors hover:bg-accent disabled:cursor-not-allowed disabled:opacity-50"
                                >
                                    <ChevronUp class="h-4 w-4" />
                                </button>
                            </div>
                            <p class="text-xs text-muted-foreground">
                                Las categorías se mostrarán en este orden en la
                                navegación
                            </p>
                        </div>
                    </div>

                    <!-- Descripción -->
                    <div class="space-y-2">
                        <label
                            for="description"
                            class="block text-sm font-medium text-foreground"
                        >
                            Descripción
                        </label>
                        <textarea
                            id="description"
                            v-model="form.description"
                            rows="3"
                            class="w-full rounded-lg border border-border bg-background px-3 py-2 text-foreground focus:border-transparent focus:ring-2 focus:ring-primary focus:outline-none"
                            placeholder="Descripción opcional de la categoría..."
                        ></textarea>
                    </div>

                    <!-- Estado -->
                    <div class="flex items-center gap-2">
                        <input
                            id="is_active"
                            v-model="form.is_active"
                            type="checkbox"
                            class="h-4 w-4 rounded border-border text-primary focus:ring-2 focus:ring-primary"
                        />
                        <label
                            for="is_active"
                            class="text-sm font-medium text-foreground"
                        >
                            Categoría activa
                        </label>
                    </div>

                    <!-- Actions -->
                    <div
                        class="flex items-center justify-end gap-4 border-t border-border pt-6"
                    >
                        <Link
                            href="/admin/categories"
                            class="rounded-lg border border-border px-4 py-2 text-foreground transition-colors hover:bg-accent"
                        >
                            Cancelar
                        </Link>
                        <button
                            type="submit"
                            class="flex items-center gap-2 rounded-lg bg-primary px-6 py-2 text-primary-foreground shadow-lg shadow-primary/25 transition-colors hover:bg-primary/90"
                        >
                            <Save class="h-4 w-4" />
                            Crear Categoría
                        </button>
                    </div>
                </form>
            </div>

            <!-- Información del orden -->
            <div class="mt-6 rounded-lg border border-blue-200 bg-blue-50 p-4">
                <div class="flex items-start gap-3">
                    <div
                        class="mt-0.5 flex h-6 w-6 items-center justify-center rounded-full bg-blue-100"
                    >
                        <span class="text-sm font-bold text-blue-600">i</span>
                    </div>
                    <div>
                        <h3 class="font-semibold text-blue-900">
                            Sobre el orden de categorías
                        </h3>
                        <p class="mt-1 text-sm text-blue-700">
                            El orden determina la posición en que aparecerán las
                            categorías en la navegación. Las categorías se
                            ordenan de menor a mayor número.
                        </p>
                        <p class="mt-1 text-sm text-blue-700">
                            <strong>Orden actual sugerido:</strong>
                            {{ form.sort_order }} de
                            {{ props.nextSortOrder }} posiciones disponibles
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
