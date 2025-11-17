<!-- resources/js/layouts/app/AppSiderLayout.vue -->
<script setup lang="ts">
import AppContent from '@/components/AppContent.vue';
import AppShell from '@/components/AppShell.vue';
import AppSidebar from '@/components/AppSidebar.vue';
import AppSidebarHeader from '@/components/AppSidebarHeader.vue';
import type { BreadcrumbItemType } from '@/types';
import { onBeforeUnmount, onMounted, ref } from 'vue';

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
                <AppContent variant="sidebar" class="min-w-0 flex-1">
                    <AppSidebarHeader :breadcrumbs="breadcrumbs" />
                    <Suspense>
                        <template #default>
                            <slot />
                        </template>
                        <template #fallback>
                            <div
                                class="flex h-full items-center justify-center"
                            >
                                <div class="text-center">
                                    <div
                                        class="mx-auto h-8 w-8 animate-spin rounded-full border-t-2 border-b-2 border-primary"
                                    ></div>
                                    <p
                                        class="mt-2 text-sm text-muted-foreground"
                                    >
                                        Cargando...
                                    </p>
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
