<script setup lang="ts">
import { login, register } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, Sparkles, Zap } from 'lucide-vue-next';

const page = usePage();
</script>

<template>
    <section class="py-24 bg-primary relative overflow-hidden">
        <!-- Efectos de fondo animados -->
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-[url('/images/grid.svg')] opacity-10"></div>
            <div
                class="absolute top-0 left-0 w-full h-full bg-gradient-to-br from-primary via-primary to-secondary/80 opacity-90">
            </div>
            <div class="absolute top-0 left-0 w-96 h-96 bg-white/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-0 right-0 w-[500px] h-[500px] bg-secondary/20 rounded-full blur-3xl animate-pulse"
                style="animation-delay: 2s"></div>
        </div>

        <div class="container mx-auto px-4 text-center relative z-10" data-animate>
            <div
                class="inline-flex items-center gap-2 bg-white/10 text-white px-4 py-2 rounded-full text-sm font-medium mb-8 border border-white/20 animate-fade-in backdrop-blur-sm">
                <Sparkles class="w-4 h-4" />
                <span>Únete a la revolución educativa</span>
            </div>

            <h2 class="text-4xl lg:text-6xl font-bold text-primary-foreground mb-6 animate-slide-up tracking-tight">
                ¿Listo para transformar tu
                <span
                    class="block mt-2 bg-gradient-to-r from-white via-white to-white/70 bg-clip-text text-transparent">experiencia
                    académica?</span>
            </h2>

            <p class="text-primary-foreground/90 mb-10 text-xl max-w-2xl mx-auto leading-relaxed animate-slide-up font-medium"
                style="animation-delay: 0.1s">
                Únete a miles de estudiantes, investigadores y profesores que ya
                están descubriendo nuevas formas de acceder al conocimiento.
            </p>

            <div class="flex flex-col sm:flex-row gap-6 justify-center animate-slide-up" style="animation-delay: 0.2s">
                <Link v-if="!page.props.auth.user" :href="register()"
                    class="group relative rounded-2xl bg-white px-8 py-4 text-lg font-bold text-primary hover:bg-white/90 transition-all duration-300 shadow-2xl hover:shadow-white/20 hover:-translate-y-1 flex items-center justify-center gap-3 overflow-hidden min-w-[200px]">
                <div
                    class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/5 to-transparent -skew-x-12 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000">
                </div>
                <Zap class="w-5 h-5 group-hover:scale-110 transition-transform duration-300 z-10" />
                <span class="z-10">Comenzar Ahora</span>
                <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300 z-10" />
                </Link>

                <Link :href="login()"
                    class="group rounded-2xl border-2 border-white/30 bg-transparent px-8 py-4 text-lg font-bold text-white hover:bg-white/10 transition-all duration-300 flex items-center justify-center gap-3 hover:scale-105 backdrop-blur-sm min-w-[200px]">
                <Sparkles class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" />
                Acceder a Mi Cuenta
                </Link>
            </div>

            <div class="mt-12 flex flex-wrap justify-center gap-8 text-primary-foreground/70 text-sm font-medium animate-fade-in"
                style="animation-delay: 0.4s">
                <div class="flex items-center gap-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-white/50"></div>
                    Sin costos ocultos
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-white/50"></div>
                    Acceso inmediato
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-1.5 h-1.5 rounded-full bg-white/50"></div>
                    Soporte 24/7
                </div>
            </div>
        </div>
    </section>
</template>
