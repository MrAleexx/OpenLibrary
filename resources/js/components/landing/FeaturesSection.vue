<script setup lang="ts">
import { BookOpen, Clock, Download, Users } from 'lucide-vue-next';
</script>

<template>
    <section class="py-20 bg-card/50 relative overflow-hidden">
        <!-- Background decorative elements -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden pointer-events-none">
            <div class="absolute -top-[20%] -right-[10%] w-[50%] h-[50%] bg-primary/5 rounded-full blur-3xl"></div>
            <div class="absolute -bottom-[20%] -left-[10%] w-[50%] h-[50%] bg-secondary/5 rounded-full blur-3xl"></div>
        </div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-16" data-animate>
                <h2 class="text-3xl lg:text-5xl font-bold text-foreground mb-6 animate-slide-up tracking-tight">
                    Todo lo que necesitas para tu
                    <span
                        class="bg-gradient-to-r from-primary to-secondary bg-clip-text text-transparent inline-block">éxito
                        académico</span>
                </h2>
                <p class="text-lg text-muted-foreground animate-slide-up leading-relaxed" style="animation-delay: 0.1s">
                    Una plataforma diseñada específicamente para la comunidad
                    universitaria con herramientas y recursos que facilitan tu
                    aprendizaje e investigación.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div v-for="(feature, index) in [
                    {
                        icon: BookOpen,
                        title: 'Catálogo Completo',
                        description:
                            'Miles de libros digitales y físicos organizados por categorías, autores y temas específicos para tu área de estudio.',
                        color: 'primary',
                    },
                    {
                        icon: Download,
                        title: 'Descargas Ilimitadas',
                        description:
                            'Acceso ilimitado a contenido digital con límites generosos diarios para que nunca te quedes sin material de estudio.',
                        color: 'secondary',
                    },
                    {
                        icon: Clock,
                        title: 'Préstamos Flexibles',
                        description:
                            'Sistema inteligente de préstamos con renovaciones automáticas y notificaciones para que nunca devuelvas tarde.',
                        color: 'primary',
                    },
                    {
                        icon: Users,
                        title: 'Comunidad Activa',
                        description:
                            'Conecta con estudiantes, profesores e investigadores que comparten tus mismos intereses académicos.',
                        color: 'secondary',
                    },
                ]" :key="index"
                    class="group p-8 rounded-2xl bg-background/60 backdrop-blur-md border border-border/50 hover:border-primary/30 transition-all duration-500 hover:shadow-2xl hover:-translate-y-2 animate-slide-up relative overflow-hidden"
                    :style="{
                        animationDelay: `${index * 0.1 + 0.2}s`,
                    }" data-animate>

                    <!-- Hover gradient background -->
                    <div
                        class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                    </div>

                    <div class="relative z-10">
                        <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform duration-300 group-hover:rotate-6 shadow-lg"
                            :class="{
                                'bg-primary/10 text-primary shadow-primary/10': feature.color === 'primary',
                                'bg-secondary/10 text-secondary shadow-secondary/10': feature.color === 'secondary',
                            }">
                            <component :is="feature.icon" class="w-8 h-8 transition-colors duration-300"
                                stroke-width="2" />
                        </div>
                        <h3
                            class="text-xl font-bold text-foreground mb-3 group-hover:translate-x-1 transition-transform duration-300">
                            {{ feature.title }}
                        </h3>
                        <p
                            class="text-muted-foreground leading-relaxed group-hover:text-foreground/80 transition-colors duration-300 text-sm">
                            {{ feature.description }}
                        </p>
                    </div>

                    <div
                        class="absolute bottom-0 left-0 w-0 h-1 bg-gradient-to-r from-primary to-secondary group-hover:w-full transition-all duration-500 ease-out">
                    </div>
                </div>
            </div>
        </div>
    </section>
</template>
