<script setup lang="ts"></script>

<template>
    <section class="py-24 relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-card/30 pointer-events-none"></div>

        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-[0.02] pointer-events-none"
            style="background-image: radial-gradient(#000 1px, transparent 1px); background-size: 32px 32px;">
        </div>

        <div class="container mx-auto px-4 relative z-10">
            <div class="text-center max-w-3xl mx-auto mb-20" data-animate>
                <h2 class="text-3xl lg:text-5xl font-bold text-foreground mb-6 animate-slide-up tracking-tight">
                    Comienza en
                    <span class="text-primary relative inline-block">
                        3 simples pasos
                        <svg class="absolute w-full h-3 -bottom-1 left-0 text-primary/20" viewBox="0 0 100 10"
                            preserveAspectRatio="none">
                            <path d="M0 5 Q 50 10 100 5" stroke="currentColor" stroke-width="4" fill="none" />
                        </svg>
                    </span>
                </h2>
                <p class="text-lg text-muted-foreground animate-slide-up leading-relaxed" style="animation-delay: 0.1s">
                    Nuestra plataforma está diseñada para ser intuitiva y fácil
                    de usar, para que puedas concentrarte en lo que realmente
                    importa: aprender.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-12 max-w-6xl mx-auto relative">
                <!-- Connecting Line (Desktop) -->
                <div
                    class="hidden md:block absolute top-12 left-[16%] right-[16%] h-0.5 bg-gradient-to-r from-transparent via-border to-transparent z-0">
                </div>

                <div v-for="(step, index) in [
                    {
                        number: '1',
                        title: 'Regístrate',
                        description:
                            'Crea tu cuenta con tu correo institucional en menos de 2 minutos.',
                        delay: 0.2
                    },
                    {
                        number: '2',
                        title: 'Explora',
                        description:
                            'Busca en nuestro catálogo por título, autor, categoría o palabra clave.',
                        delay: 0.4
                    },
                    {
                        number: '3',
                        title: 'Disfruta',
                        description:
                            'Descarga contenido digital o reserva ejemplares físicos al instante.',
                        delay: 0.6
                    },
                ]" :key="index" class="relative text-center group animate-slide-up" :style="{
                    animationDelay: `${step.delay}s`,
                }" data-animate>

                    <div class="relative z-10 inline-block mb-8">
                        <div
                            class="w-24 h-24 bg-background rounded-2xl border-2 border-border flex items-center justify-center text-2xl font-bold shadow-xl group-hover:border-primary/50 group-hover:shadow-primary/20 transition-all duration-500 relative overflow-hidden group-hover:-translate-y-2">
                            <span
                                class="bg-gradient-to-br from-primary to-secondary bg-clip-text text-transparent z-10">{{
                                step.number }}</span>
                            <div
                                class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500">
                            </div>
                        </div>

                        <!-- Decorative glow behind number -->
                        <div
                            class="absolute -inset-4 bg-primary/20 rounded-full blur-2xl opacity-0 group-hover:opacity-100 transition-opacity duration-500 -z-10">
                        </div>
                    </div>

                    <h3
                        class="text-2xl font-bold text-foreground mb-4 group-hover:text-primary transition-colors duration-300">
                        {{ step.title }}
                    </h3>
                    <p
                        class="text-muted-foreground leading-relaxed px-4 group-hover:text-foreground/80 transition-colors duration-300">
                        {{ step.description }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</template>
