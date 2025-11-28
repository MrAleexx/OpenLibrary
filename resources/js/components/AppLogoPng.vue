<script setup lang="ts">
interface Props {
    heightClass?: string;
}

withDefaults(defineProps<Props>(), {
    heightClass: 'h-14',
});

const getAssetUrl = (path: string) => {
    const base = import.meta.env.BASE_URL || '/';
    return `${base}${path.replace(/^\//, '')}`;
};
</script>

<template>
    <div class="flex items-center gap-2">
        <!-- Logo para modo claro (texto oscuro) -->
        <img :src="getAssetUrl('images/logos/transparente-dark.png')" alt="MIS Library" class="w-auto block dark:hidden"
            :class="heightClass" />
        <!-- Logo para modo oscuro (texto blanco) -->
        <img :src="getAssetUrl('images/logos/transparente-white.png')" alt="MIS Library"
            class="w-auto hidden dark:block" :class="heightClass" />
    </div>
</template>
