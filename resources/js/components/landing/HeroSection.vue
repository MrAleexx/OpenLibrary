<script setup lang="ts">
import { login, register } from '@/routes';
import { Link, usePage } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookMarked,
    BookOpen,
    Globe,
    GraduationCap,
    Library,
    Search,
    Sparkles,
    Star,
} from 'lucide-vue-next';

defineProps<{
    mousePosition: { x: number; y: number };
}>();

const page = usePage();
</script>

<template>
    <section class="relative overflow-hidden pt-10 lg:pt-20">
        <!-- Partículas flotantes -->
        <div class="absolute inset-0 pointer-events-none">
            <div v-for="i in 15" :key="i" class="absolute w-2 h-2 bg-primary/20 rounded-full animate-float" :style="{
                left: `${Math.random() * 100}%`,
                top: `${Math.random() * 100}%`,
                animationDelay: `${Math.random() * 5}s`,
                animationDuration: `${3 + Math.random() * 4}s`,
            }"></div>
        </div>

        <div class="container mx-auto px-6 md:px-20 py-12 lg:py-10 relative">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-12 lg:gap-16">
                <div class="flex-1 text-center lg:text-left px-4 lg:px-0" data-animate>
                    <div
                        class="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-full text-sm font-medium mb-6 border border-primary/20 animate-fade-in backdrop-blur-sm">
                        <Sparkles class="w-4 h-4 animate-pulse" />
                        <span>Plataforma académica</span>
                        <Star class="w-3 h-3 fill-current" />
                    </div>
                    <h1
                        class="text-4xl sm:text-5xl lg:text-7xl font-bold text-foreground mb-6 leading-[1.1] animate-slide-up tracking-tight">
                        Descubre el conocimiento
                        <span
                            class="bg-gradient-to-r from-primary via-blue-500 to-secondary bg-clip-text gradient-text animate-gradient inline-block">sin
                            límites</span>
                    </h1>
                    <p class="text-lg sm:text-xl text-muted-foreground mb-8 max-w-2xl mx-auto lg:mx-0 leading-relaxed animate-slide-up"
                        style="animation-delay: 0.1s">
                        Accede a una colección curada de miles de libros
                        digitales y físicos. Tu biblioteca universitaria en
                        línea con los mejores recursos académicos, materiales de
                        investigación y literatura especializada.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start animate-slide-up"
                        style="animation-delay: 0.2s">
                        <Link v-if="!page.props.auth.user" :href="register()"
                            class="group relative rounded-xl bg-primary px-8 py-4 text-lg font-semibold text-primary-foreground hover:bg-primary/90 transition-all duration-300 shadow-xl shadow-primary/20 hover:shadow-2xl hover:shadow-primary/30 flex items-center justify-center gap-3 overflow-hidden transform hover:-translate-y-1">
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent -skew-x-12 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000">
                        </div>
                        <BookOpen class="w-5 h-5 group-hover:scale-110 transition-transform duration-300 z-10" />
                        <span class="z-10">Comenzar Gratis</span>
                        <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300 z-10" />
                        </Link>
                        <Link :href="login()"
                            class="group rounded-xl border border-border bg-card/50 px-8 py-4 text-lg font-semibold text-foreground hover:bg-accent/50 transition-all duration-300 hover:shadow-lg flex items-center justify-center gap-3 hover:scale-105 backdrop-blur-sm">
                        <Search class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" />
                        Explorar Catálogo
                        </Link>
                    </div>

                    <!-- Stats animadas -->
                    <div class="flex flex-wrap gap-8 sm:gap-12 mt-12 justify-center lg:justify-start animate-slide-up border-t border-border/50 pt-8"
                        style="animation-delay: 0.3s">
                        <div v-for="(stat, index) in [
                            {
                                number: '10k+',
                                label: 'Libros Digitales',
                            },
                            {
                                number: '5k+',
                                label: 'Usuarios Activos',
                            },
                            { number: '24/7', label: 'Disponibilidad' },
                        ]" :key="index" class="text-center lg:text-left group cursor-default">
                            <div
                                class="text-3xl font-bold text-foreground group-hover:text-primary transition-colors duration-300">
                                {{ stat.number }}
                            </div>
                            <div
                                class="text-sm text-muted-foreground font-medium group-hover:text-foreground transition-colors duration-300">
                                {{ stat.label }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hero Visual con animaciones 3D -->
                <div class="flex-1 relative w-full max-w-[500px] lg:max-w-none mx-auto animate-float"
                    style="animation-duration: 6s">
                    <div class="relative aspect-square">
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-primary/30 to-secondary/30 rounded-full blur-3xl opacity-30 animate-pulse">
                        </div>
                        <div class="w-full h-full bg-gradient-to-br from-white/10 to-white/5 rounded-[2rem] shadow-2xl flex items-center justify-center backdrop-blur-md border border-white/20 hover:shadow-primary/20 transition-all duration-500 relative z-10 overflow-hidden"
                            :style="{
                                transform: `perspective(1000px) rotateX(${(mousePosition.y - 50) * 0.05}deg) rotateY(${-(mousePosition.x - 50) * 0.05}deg)`,
                            }">

                            <!-- Grid pattern overlay -->
                            <div class="absolute inset-0 opacity-10"
                                style="background-image: linear-gradient(currentColor 1px, transparent 1px), linear-gradient(to right, currentColor 1px, transparent 1px); background-size: 32px 32px; color: rgba(0,0,0,0.2);">
                            </div>

                            <div class="relative z-20 flex flex-col items-center">
                                <div class="relative mb-6">
                                    <Library class="w-32 h-32 text-primary drop-shadow-lg" />
                                    <div class="absolute -inset-4 bg-primary/20 rounded-full blur-xl animate-pulse"
                                        style="animation-duration: 3s" />
                                </div>
                                <div class="text-2xl font-bold text-foreground text-center px-8">
                                    Tu puerta al conocimiento
                                </div>
                            </div>
                        </div>

                        <!-- Floating Cards con animaciones individuales -->
                        <div class="absolute -top-6 -left-6 w-24 h-24 bg-card rounded-2xl shadow-xl border border-border/50 flex items-center justify-center transform rotate-6 animate-float hover:scale-110 transition-transform duration-300 group z-20 backdrop-blur-md"
                            style="animation-delay: 1s">
                            <BookMarked
                                class="w-10 h-10 text-primary group-hover:scale-110 transition-transform duration-300" />
                            <div
                                class="absolute inset-0 bg-primary/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                        </div>
                        <div class="absolute -bottom-8 -right-4 w-20 h-20 bg-card rounded-2xl shadow-xl border border-border/50 flex items-center justify-center transform -rotate-6 animate-float hover:scale-110 transition-transform duration-300 group z-20 backdrop-blur-md"
                            style="animation-delay: 2s">
                            <GraduationCap
                                class="w-8 h-8 text-secondary group-hover:scale-110 transition-transform duration-300" />
                            <div
                                class="absolute inset-0 bg-secondary/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                        </div>
                        <div class="absolute top-1/2 -right-10 w-16 h-16 bg-card rounded-2xl shadow-xl border border-border/50 flex items-center justify-center transform rotate-12 animate-float hover:scale-110 transition-transform duration-300 group z-20 backdrop-blur-md"
                            style="animation-delay: 3s">
                            <Globe
                                class="w-6 h-6 text-blue-500 group-hover:scale-110 transition-transform duration-300" />
                            <div
                                class="absolute inset-0 bg-blue-500/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
