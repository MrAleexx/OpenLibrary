<!-- resorces/js/components/AppContent.vue -->
<script setup lang="ts">
import { SidebarInset } from '@/components/ui/sidebar';
import { computed } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
    class?: string;
}

const props = defineProps<Props>();
const className = computed(() => props.class);
</script>

<template>
    <SidebarInset v-if="props.variant === 'sidebar'" :class="[className, 'flex-1 min-w-0']">
        <slot />
    </SidebarInset>
    <main
        v-else
        class="flex h-full w-full flex-1 flex-col min-w-0"
        :class="className"
    >
        <slot />
    </main>
</template>
