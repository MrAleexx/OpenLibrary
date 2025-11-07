<!-- resources/js/components/CategoryTreeNode.vue -->
<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { Folder, GripVertical, Eye, EyeOff, History, Edit, Trash2 } from 'lucide-vue-next';
import type { Category } from '@/types'; // ✅ Importar desde types

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
            class="drop-zone drop-zone-before h-2 bg-transparent hover:bg-primary/20 transition-colors"
            @dragover="onDragOver"
            @drop="onDropBefore($event, category)"
        ></div>

        <!-- Contenido principal de la categoría -->
        <div 
            class="flex items-center justify-between p-4 hover:bg-accent/50 transition-colors cursor-move border border-border rounded-lg mb-1"
            draggable="true"
            @dragstart="onDragStart($event, category)"
            @dragover="onDragOver"
            @drop="onDropInside($event, category)"
            :style="{ marginLeft: depth * 20 + 'px' }"
        >
            <div class="flex items-center gap-3 flex-1">
                <GripVertical class="w-4 h-4 text-muted-foreground cursor-move" />
                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                    <Folder class="w-4 h-4 text-primary" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="font-semibold text-foreground">{{ category.name }}</span>
                        <span class="text-xs text-muted-foreground">({{ category.books_count }} libros)</span>
                    </div>
                    <p v-if="category.description" class="text-sm text-muted-foreground truncate">
                        {{ category.description }}
                    </p>
                </div>
            </div>
            
            <div class="flex items-center gap-2">
                <span class="text-xs bg-muted text-foreground px-2 py-1 rounded">
                    Orden: {{ category.sort_order }}
                </span>
                
                <button
                    @click.stop="onToggleStatus(category)"
                    class="flex items-center gap-1 px-2 py-1 rounded text-sm transition-colors"
                    :class="category.is_active 
                        ? 'bg-success/10 text-success hover:bg-success/20' 
                        : 'bg-muted text-muted-foreground hover:bg-muted/80'"
                >
                    <component :is="category.is_active ? Eye : EyeOff" class="w-3 h-3" />
                    {{ category.is_active ? 'Activa' : 'Inactiva' }}
                </button>

                <button
                    @click.stop="onViewDetails(category)"
                    class="p-1 text-muted-foreground hover:text-primary transition-colors rounded hover:bg-primary/10"
                    title="Ver detalles"
                >
                    <Eye class="w-4 h-4" />
                </button>

                <button
                    @click.stop="onViewHistory(category)"
                    class="p-1 text-muted-foreground hover:text-primary transition-colors rounded hover:bg-primary/10"
                    title="Historial"
                >
                    <History class="w-4 h-4" />
                </button>

                <Link
                    :href="'/admin/categories/' + category.id + '/edit'"
                    class="p-1 text-muted-foreground hover:text-primary transition-colors rounded hover:bg-primary/10"
                    title="Editar"
                >
                    <Edit class="w-4 h-4" />
                </Link>

                <button
                    @click.stop="onDeleteCategory(category)"
                    class="p-1 text-muted-foreground hover:text-destructive transition-colors rounded hover:bg-destructive/10"
                    :disabled="category.books_count > 0 || (category.children && category.children.length > 0)"
                    title="Eliminar"
                >
                    <Trash2 class="w-4 h-4" />
                </button>
            </div>
        </div>

        <!-- Zona de drop "after" -->
        <div 
            class="drop-zone drop-zone-after h-2 bg-transparent hover:bg-primary/20 transition-colors"
            @dragover="onDragOver"
            @drop="onDropAfter($event, category)"
        ></div>

        <!-- Subcategorías -->
        <div v-if="category.children && category.children.length > 0" class="children-container">
            <CategoryTreeNode
                v-for="child in category.children"
                :key="child.id"
                :category="child"
                :depth="depth + 1"
                @drag-start="(event: DragEvent, childCategory: Category) => emit('drag-start', event, childCategory)"
                @drop-before="(event: DragEvent, childCategory: Category) => emit('drop-before', event, childCategory)"
                @drop-after="(event: DragEvent, childCategory: Category) => emit('drop-after', event, childCategory)"
                @drop-inside="(event: DragEvent, childCategory: Category) => emit('drop-inside', event, childCategory)"
                @toggle-status="(childCategory: Category) => emit('toggle-status', childCategory)"
                @view-details="(childCategory: Category) => emit('view-details', childCategory)"
                @view-history="(childCategory: Category) => emit('view-history', childCategory)"
                @delete-category="(childCategory: Category) => emit('delete-category', childCategory)"
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