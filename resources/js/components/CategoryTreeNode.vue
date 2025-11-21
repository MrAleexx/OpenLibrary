<!-- resources/js/components/CategoryTreeNode.vue -->
<script setup lang="ts">
import type { Category } from '@/types'; // ✅ Importar desde types
import { Link } from '@inertiajs/vue3';
import {
    Edit,
    Eye,
    EyeOff,
    Folder,
    GripVertical,
    History,
    Trash2,
} from 'lucide-vue-next';

interface Props {
    category: Category;
    depth: number;
}

defineProps<Props>();

// Definir emits para los eventos - CORREGIDO
const emit = defineEmits<{
    (e: 'drag-start', event: DragEvent, category: Category): void;
    (e: 'drop-before', event: DragEvent, category: Category): void;
    (e: 'drop-after', event: DragEvent, category: Category): void;
    (e: 'drop-inside', event: DragEvent, category: Category): void;
    (e: 'toggle-status', category: Category): void;
    (e: 'view-details', category: Category): void;
    (e: 'view-history', category: Category): void;
    (e: 'delete-category', category: Category): void;
}>();

// Handlers corregidos
const onDragStart = (event: DragEvent, category: Category) => {
    emit('drag-start', event, category);
};

const onDropBefore = (event: DragEvent, category: Category) => {
    emit('drop-before', event, category);
};

const onDropAfter = (event: DragEvent, category: Category) => {
    emit('drop-after', event, category);
};

const onDropInside = (event: DragEvent, category: Category) => {
    emit('drop-inside', event, category);
};

const onToggleStatus = (category: Category) => {
    emit('toggle-status', category);
};

const onViewDetails = (category: Category) => {
    emit('view-details', category);
};

const onViewHistory = (category: Category) => {
    emit('view-history', category);
};

const onDeleteCategory = (category: Category) => {
    emit('delete-category', category);
};

// Handler para dragover
const onDragOver = (event: DragEvent) => {
    event.preventDefault();
    if (event.dataTransfer) {
        event.dataTransfer.dropEffect = 'move';
    }
};
</script>

<template>
    <div class="category-node">
        <!-- Zona de drop "before" -->
        <div
            class="drop-zone drop-zone-before h-2 bg-transparent transition-colors hover:bg-primary/20"
            @dragover="onDragOver"
            @drop="onDropBefore($event, category)"
        ></div>

        <!-- Contenido principal de la categoría -->
        <div
            class="mb-1 flex cursor-move items-center justify-between rounded-lg border border-border p-4 transition-colors hover:bg-accent/50"
            draggable="true"
            @dragstart="onDragStart($event, category)"
            @dragover="onDragOver"
            @drop="onDropInside($event, category)"
            :style="{ marginLeft: depth * 20 + 'px' }"
        >
            <div class="flex flex-1 items-center gap-3">
                <GripVertical
                    class="h-4 w-4 cursor-move text-muted-foreground"
                />
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10"
                >
                    <Folder class="h-4 w-4 text-primary" />
                </div>
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-foreground">{{
                            category.name
                        }}</span>
                        <span class="text-xs text-muted-foreground"
                            >({{ category.books_count }} libros)</span
                        >
                    </div>
                    <p
                        v-if="category.description"
                        class="truncate text-sm text-muted-foreground"
                    >
                        {{ category.description }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2">
                <span
                    class="rounded bg-muted px-2 py-1 text-xs text-foreground"
                >
                    Orden: {{ category.sort_order }}
                </span>

                <button
                    @click.stop="onToggleStatus(category)"
                    class="flex items-center gap-1 rounded px-2 py-1 text-sm transition-colors"
                    :class="
                        category.is_active
                            ? 'bg-success/10 text-success hover:bg-success/20'
                            : 'bg-muted text-muted-foreground hover:bg-muted/80'
                    "
                >
                    <component
                        :is="category.is_active ? Eye : EyeOff"
                        class="h-3 w-3"
                    />
                    {{ category.is_active ? 'Activa' : 'Inactiva' }}
                </button>

                <button
                    @click.stop="onViewDetails(category)"
                    class="rounded p-1 text-muted-foreground transition-colors hover:bg-primary/10 hover:text-primary"
                    title="Ver detalles"
                >
                    <Eye class="h-4 w-4" />
                </button>

                <button
                    @click.stop="onViewHistory(category)"
                    class="rounded p-1 text-muted-foreground transition-colors hover:bg-primary/10 hover:text-primary"
                    title="Historial"
                >
                    <History class="h-4 w-4" />
                </button>

                <Link
                    :href="'/admin/categories/' + category.id + '/edit'"
                    class="rounded p-1 text-muted-foreground transition-colors hover:bg-primary/10 hover:text-primary"
                    title="Editar"
                >
                    <Edit class="h-4 w-4" />
                </Link>

                <button
                    @click.stop="onDeleteCategory(category)"
                    class="rounded p-1 text-muted-foreground transition-colors hover:bg-destructive/10 hover:text-destructive"
                    :disabled="
                        category.books_count > 0 ||
                        (category.children && category.children.length > 0)
                    "
                    title="Eliminar"
                >
                    <Trash2 class="h-4 w-4" />
                </button>
            </div>
        </div>

        <!-- Zona de drop "after" -->
        <div
            class="drop-zone drop-zone-after h-2 bg-transparent transition-colors hover:bg-primary/20"
            @dragover="onDragOver"
            @drop="onDropAfter($event, category)"
        ></div>

        <!-- Subcategorías -->
        <div
            v-if="category.children && category.children.length > 0"
            class="children-container"
        >
            <CategoryTreeNode
                v-for="child in category.children"
                :key="child.id"
                :category="child"
                :depth="depth + 1"
                @drag-start="
                    (event: DragEvent, childCategory: Category) =>
                        emit('drag-start', event, childCategory)
                "
                @drop-before="
                    (event: DragEvent, childCategory: Category) =>
                        emit('drop-before', event, childCategory)
                "
                @drop-after="
                    (event: DragEvent, childCategory: Category) =>
                        emit('drop-after', event, childCategory)
                "
                @drop-inside="
                    (event: DragEvent, childCategory: Category) =>
                        emit('drop-inside', event, childCategory)
                "
                @toggle-status="
                    (childCategory: Category) =>
                        emit('toggle-status', childCategory)
                "
                @view-details="
                    (childCategory: Category) =>
                        emit('view-details', childCategory)
                "
                @view-history="
                    (childCategory: Category) =>
                        emit('view-history', childCategory)
                "
                @delete-category="
                    (childCategory: Category) =>
                        emit('delete-category', childCategory)
                "
            />
        </div>
    </div>
</template>

<style scoped>
.drop-zone {
    transition: all 0.2s ease;
}

.drop-zone-before {
    margin-bottom: 2px;
}

.drop-zone-after {
    margin-top: 2px;
}

.drop-zone:hover {
    background-color: rgb(59 130 246 / 0.3) !important;
}
</style>
