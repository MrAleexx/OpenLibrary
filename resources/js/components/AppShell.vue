<script setup lang="ts">
import { SidebarProvider } from '@/components/ui/sidebar';
import { usePage } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

interface Props {
    variant?: 'header' | 'sidebar';
}

defineProps<Props>();

// Aseguramos que isOpen sea siempre un booleano
const isOpen = ref<boolean>(true);

// Actualizamos isOpen cuando cambian las props
onMounted(() => {
    const page = usePage();
    isOpen.value = Boolean(page.props.sidebarOpen);
});
</script>

<template>
    <div v-if="variant === 'header'" class="flex min-h-screen w-full flex-col">
        <slot />
    </div>
    <SidebarProvider v-else :default-open="isOpen">
        <slot />
    </SidebarProvider>
</template>
