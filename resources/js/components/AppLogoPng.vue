<script setup lang="ts">
interface Props {
    heightClass?: string;
}

withDefaults(defineProps<Props>(), {
    heightClass: 'h-14',
});
</script>

<template>
    <div class="flex items-center gap-2">
        <!-- Logo para modo claro (texto oscuro) -->
        <img src="/images/logos/logo-Sinfondo-Dark.png" alt="MIS Library" class="w-auto block dark:hidden"
            :class="heightClass" />
        <!-- Logo para modo oscuro (texto blanco) -->
        <img src="/images/logos/Logo-Sinfondo-white.png" alt="MIS Library" class="w-auto hidden dark:block"
            :class="heightClass" />
    </div>
</template>
