<script setup lang="ts">
import { login, register } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import { ArrowRight, Sparkles, Zap } from 'lucide-vue-next';

const page = usePage();
</script>

<template>
    <section class="py-20 bg-primary relative overflow-hidden">
        <!-- Efectos de fondo animados -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-0 w-72 h-72 bg-white/5 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-0 right-0 w-96 h-96 bg-white/5 rounded-full blur-3xl animate-pulse"
                style="animation-delay: 2s"></div>
        </div>

        <div class="container mx-auto px-4 text-center relative z-10" data-animate>
            <h2 class="text-3xl lg:text-4xl font-bold text-primary-foreground mb-4 animate-slide-up">
                ¿Listo para transformar tu
                <span class="bg-gradient-to-r from-white to-white/70 bg-clip-text text-transparent">experiencia
                    académica?</span>
            </h2>
            <p class="text-primary-foreground/80 mb-8 text-xl max-w-2xl mx-auto leading-relaxed animate-slide-up"
                style="animation-delay: 0.1s">
                Únete a miles de estudiantes, investigadores y profesores que ya
                están descubriendo nuevas formas de acceder al conocimiento.
            </p>
            <div class="flex flex-col sm:flex-row gap-4 justify-center animate-slide-up" style="animation-delay: 0.2s">
                <Link v-if="!page.props.auth.user" :href="register()"
                    class="group relative rounded-xl bg-primary-foreground px-8 py-4 text-lg font-semibold text-primary hover:bg-primary-foreground/90 transition-all duration-300 shadow-2xl hover:shadow-2xl hover:shadow-primary-foreground/20 flex items-center justify-center gap-3 overflow-hidden">
                <div
                    class="absolute inset-0 bg-gradient-to-r from-transparent via-primary/10 to-transparent -skew-x-12 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000">
                </div>
                <Zap class="w-5 h-5 group-hover:scale-110 transition-transform duration-300 z-10" />
                <span class="z-10">Comenzar Ahora</span>
                <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300 z-10" />
                </Link>
                <Link :href="login()"
                    class="group rounded-xl border border-primary-foreground/30 bg-transparent px-8 py-4 text-lg font-semibold text-primary-foreground hover:bg-primary-foreground/10 transition-all duration-300 flex items-center justify-center gap-3 hover:scale-105 backdrop-blur-sm">
                <Sparkles class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" />
                Acceder a Mi Cuenta
                </Link>
            </div>
            <p class="text-primary-foreground/60 text-sm mt-6 animate-fade-in" style="animation-delay: 0.4s">
                Sin costos ocultos • Cancelación en cualquier momento • Soporte
                24/7
            </p>
        </div>
    </section>
</template>
