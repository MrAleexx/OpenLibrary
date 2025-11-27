<script setup lang="ts">
import AppLogoPng from '@/components/AppLogoPng.vue';
import { dashboard, login, register } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, BookText } from 'lucide-vue-next';
import { onMounted, onUnmounted, ref } from 'vue';

defineProps<{
    canRegister?: boolean;
}>();

const page = usePage();
const scrollY = ref(0);

const handleScroll = () => {
    scrollY.value = window.scrollY;
};

onMounted(() => {
    window.addEventListener('scroll', handleScroll);
});

onUnmounted(() => {
    window.removeEventListener('scroll', handleScroll);
});
</script>

<template>
    <header
        class="sticky top-0 z-50 bg-background/80 backdrop-blur-md border-b border-border transition-all duration-300"
        :class="{
            'bg-background/95 shadow-lg': scrollY > 50
        }">
        <div class="container mx-auto px-4 py-4">
            <nav class="flex items-center justify-between">
                <AppLogoPng height-class="h-18" />
                <div class="flex items-center gap-3">
                    <Link v-if="page.props.auth.user" :href="dashboard()"
                        class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-all duration-300 shadow-lg shadow-primary/20 hover:shadow-primary/30 flex items-center gap-2 hover:gap-3 group">
                    <BookText class="w-4 h-4 group-hover:scale-110 transition-transform" />
                    Dashboard
                    </Link>
                    <template v-else>
                        <Link :href="login()"
                            class="rounded-lg border border-border bg-card px-6 py-2.5 text-sm font-medium text-foreground hover:bg-accent transition-all duration-300 hover:shadow-md hover:scale-105">
                        Iniciar Sesión
                        </Link>
                        <Link v-if="canRegister" :href="register()"
                            class="rounded-lg bg-primary px-6 py-2.5 text-sm font-medium text-primary-foreground hover:bg-primary/90 transition-all duration-300 shadow-lg shadow-primary/20 hover:shadow-primary/30 flex items-center gap-2 hover:gap-3 group">
                        Registrarse
                        <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform" />
                        </Link>
                    </template>
                </div>
            </nav>
        </div>
    </header>
</template>
