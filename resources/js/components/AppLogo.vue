<script setup lang="ts">
// Logo component using SVGs
</script>

<template>
    <div class="flex items-center gap-2">
        <!-- Logo para modo claro (texto oscuro) -->
        <img src="/images/logos/whiteLogo.svg" alt="MIS Library" class="h-15 w-auto block dark:hidden" />
        <!-- Logo para modo oscuro (texto blanco) -->
        <img src="/images/logos/darkLogo.svg" alt="MIS Library" class="h-15 w-auto hidden dark:block" />
    </div>
</template>
