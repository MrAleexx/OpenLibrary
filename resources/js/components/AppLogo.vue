<script setup lang="ts">
import { BookOpen } from 'lucide-vue-next';
</script>

<template>
    <div class="flex items-center space-x-3 group">
        <div
            class="w-10 h-10 bg-primary rounded-xl flex items-center justify-center shadow-lg shadow-primary/20 group-hover:shadow-primary/30 transition-all duration-300 group-hover:scale-110">
            <BookOpen class="w-5 h-5 text-primary-foreground group-hover:rotate-12 transition-transform duration-300" />
        </div>
        <div>
            <span class="text-xl font-bold text-foreground">OpenLibrary</span>
            <span class="block text-xs text-muted-foreground">Biblioteca Digital</span>
        </div>
    </div>
</template>
