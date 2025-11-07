<!-- resources/js/layouts/app/AppSiderLayout.vue -->
<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';
import { ref, onMounted, onBeforeUnmount } from 'vue';

interface Props {
    breadcrumbs?: BreadcrumbItemType[];
}

withDefaults(defineProps<Props>(), {
    breadcrumbs: () => [],
});

const isLoaded = ref(false);
let unmounted = false;

onMounted(() => {
    isLoaded.value = true;
});

onBeforeUnmount(() => {
    unmounted = true;
    isLoaded.value = false;
});
</script>

<template>
    <AppShell variant="sidebar">
        <Transition name="fade" mode="out-in">
            <div v-if="isLoaded" class="flex h-full w-full">
                <AppSidebar />
                <AppContent variant="sidebar" class="flex-1 min-w-0">
                    <AppSidebarHeader :breadcrumbs="breadcrumbs" />
                    <Suspense>
                        <template #default>
                            <slot />
                        </template>
                        <template #fallback>
                            <div class="flex h-full items-center justify-center">
                                <div class="text-center">
                                    <div class="animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-primary mx-auto"></div>
                                    <p class="mt-2 text-sm text-muted-foreground">Cargando...</p>
                                </div>
                            </div>
                        </template>
                    </Suspense>
                </AppContent>
            </div>
        </Transition>
    </AppShell>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}
</style>
