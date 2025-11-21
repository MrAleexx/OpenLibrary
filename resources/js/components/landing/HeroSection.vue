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
    <section class="relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 via-secondary/5 to-background" />

        <!-- Partículas flotantes -->
        <div class="absolute inset-0 pointer-events-none">
            <div v-for="i in 15" :key="i" class="absolute w-2 h-2 bg-primary/20 rounded-full animate-float" :style="{
                left: `${Math.random() * 100}%`,
                top: `${Math.random() * 100}%`,
                animationDelay: `${Math.random() * 5}s`,
                animationDuration: `${3 + Math.random() * 4}s`,
            }"></div>
        </div>

        <div class="container mx-auto px-4 py-20 relative">
            <div class="flex flex-col lg:flex-row items-center justify-between gap-16">
                <div class="flex-1 text-center lg:text-left px-15" data-animate>
                    <div
                        class="inline-flex items-center gap-2 bg-primary/10 text-primary px-4 py-2 rounded-full text-sm font-medium mb-6 border border-primary/20 animate-fade-in">
                        <Sparkles class="w-4 h-4 animate-pulse" />
                        Plataforma académica
                        <Star class="w-3 h-3 fill-current" />
                    </div>
                    <h1 class="text-4xl lg:text-6xl font-bold text-foreground mb-6 leading-tight animate-slide-up">
                        Descubre el conocimiento
                        <span
                            class="bg-gradient-to-r from-primary to-primary/60 bg-clip-text gradient-text animate-gradient">sin
                            límites</span>
                    </h1>
                    <p class="text-xl text-muted-foreground mb-8 max-w-2xl leading-relaxed animate-slide-up"
                        style="animation-delay: 0.1s">
                        Accede a una colección curada de miles de libros
                        digitales y físicos. Tu biblioteca universitaria en
                        línea con los mejores recursos académicos, materiales de
                        investigación y literatura especializada.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center lg:justify-start animate-slide-up"
                        style="animation-delay: 0.2s">
                        <Link v-if="!page.props.auth.user" :href="register()"
                            class="group relative rounded-xl bg-primary px-8 py-4 text-lg font-semibold text-primary-foreground hover:bg-primary/90 transition-all duration-300 shadow-2xl shadow-primary/25 hover:shadow-primary/40 flex items-center justify-center gap-3 overflow-hidden">
                        <div
                            class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent -skew-x-12 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-1000">
                        </div>
                        <BookOpen class="w-5 h-5 group-hover:scale-110 transition-transform duration-300 z-10" />
                        <span class="z-10">Comenzar Gratis</span>
                        <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300 z-10" />
                        </Link>
                        <Link :href="login()"
                            class="group rounded-xl border border-border bg-card px-8 py-4 text-lg font-semibold text-foreground hover:bg-accent transition-all duration-300 hover:shadow-lg flex items-center justify-center gap-3 hover:scale-105">
                        <Search class="w-5 h-5 group-hover:scale-110 transition-transform duration-300" />
                        Explorar Catálogo
                        </Link>
                    </div>

                    <!-- Stats animadas -->
                    <div class="flex flex-wrap gap-8 mt-12 justify-center lg:justify-start animate-slide-up"
                        style="animation-delay: 0.3s">
                        <div v-for="(stat, index) in [
                            {
                                number: '10,000+',
                                label: 'Libros Digitales',
                            },
                            {
                                number: '5,000+',
                                label: 'Usuarios Activos',
                            },
                            { number: '24/7', label: 'Disponible' },
                        ]" :key="index" class="text-center lg:text-left group">
                            <div
                                class="text-2xl font-bold text-foreground group-hover:scale-110 transition-transform duration-300">
                                {{ stat.number }}
                            </div>
                            <div
                                class="text-sm text-muted-foreground group-hover:text-foreground transition-colors duration-300">
                                {{ stat.label }}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Hero Visual con animaciones 3D -->
                <div class="flex-1 relative animate-float" style="animation-duration: 6s">
                    <div class="relative">
                        <div class="w-full aspect-square max-w-md mx-auto bg-gradient-to-br from-primary/20 to-secondary/20 rounded-3xl shadow-2xl flex items-center justify-center backdrop-blur-sm border border-border/50 hover:shadow-3xl transition-all duration-500"
                            :style="{
                                transform: `rotate3d(${(mousePosition.y - 50) / 50}, ${-(mousePosition.x - 50) / 50}, 0, 5deg)`,
                            }">
                            <div class="relative">
                                <Library class="w-32 h-32 text-primary animate-pulse" style="animation-duration: 3s" />
                                <div class="absolute -inset-4 bg-primary/10 rounded-full blur-xl animate-ping"
                                    style="animation-duration: 4s" />
                            </div>
                        </div>

                        <!-- Floating Cards con animaciones individuales -->
                        <div class="absolute -top-4 -left-4 w-20 h-20 bg-card rounded-2xl shadow-lg border border-border flex items-center justify-center transform rotate-12 animate-float hover:scale-110 transition-transform duration-300 group"
                            style="animation-delay: 1s">
                            <BookMarked
                                class="w-8 h-8 text-primary group-hover:scale-110 transition-transform duration-300" />
                            <div
                                class="absolute inset-0 bg-primary/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                        </div>
                        <div class="absolute -bottom-4 -right-4 w-16 h-16 bg-card rounded-2xl shadow-lg border border-border flex items-center justify-center transform -rotate-12 animate-float hover:scale-110 transition-transform duration-300 group"
                            style="animation-delay: 2s">
                            <GraduationCap
                                class="w-6 h-6 text-secondary group-hover:scale-110 transition-transform duration-300" />
                            <div
                                class="absolute inset-0 bg-secondary/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                        </div>
                        <div class="absolute top-1/2 -right-8 w-14 h-14 bg-card rounded-2xl shadow-lg border border-border flex items-center justify-center transform rotate-6 animate-float hover:scale-110 transition-transform duration-300 group"
                            style="animation-delay: 3s">
                            <Globe
                                class="w-5 h-5 text-primary group-hover:scale-110 transition-transform duration-300" />
                            <div
                                class="absolute inset-0 bg-primary/5 rounded-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
