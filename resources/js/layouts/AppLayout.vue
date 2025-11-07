<script setup lang="ts">
import AppLayout from '@/layouts/app/AppSidebarLayout.vue';
import type { BreadcrumbItemType } from '@/types';
import { onBeforeUnmount, onMounted } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

// Limpieza al desmontar
let cleanup: (() => void) | undefined;

onMounted(() => {
    cleanup = () => {
        // Limpiar cualquier listener o estado aquí
    };
});

onBeforeUnmount(() => {
    if (cleanup) {
        cleanup();
    }
});
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Suspense>
            <slot />
        </Suspense>
    </AppLayout>
</template>
