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
    <SidebarInset
        v-if="props.variant === 'sidebar'"
        :class="[className, 'min-w-0 flex-1']"
    >
        <slot />
    </SidebarInset>
    <main
        v-else
        class="flex h-full w-full min-w-0 flex-1 flex-col"
        :class="className"
    >
        <slot />
    </main>
</template>
