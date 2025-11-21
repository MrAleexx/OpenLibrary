<script setup lang="ts"></script>

<template>
    <section class="py-20 relative">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-card/30 pointer-events-none"></div>
        <div class="container mx-auto px-4">
            <div class="text-center max-w-3xl mx-auto mb-16" data-animate>
                <h2 class="text-3xl lg:text-4xl font-bold text-foreground mb-4 animate-slide-up">
                    Comienza en
                    <span class="text-primary">3 simples pasos</span>
                </h2>
                <p class="text-lg text-muted-foreground animate-slide-up" style="animation-delay: 0.1s">
                    Nuestra plataforma está diseñada para ser intuitiva y fácil
                    de usar, para que puedas concentrarte en lo que realmente
                    importa: aprender.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                <div v-for="(step, index) in [
                    {
                        number: '1',
                        title: 'Regístrate',
                        description:
                            'Crea tu cuenta con tu correo institucional en menos de 2 minutos.',
                    },
                    {
                        number: '2',
                        title: 'Explora',
                        description:
                            'Busca en nuestro catálogo por título, autor, categoría o palabra clave.',
                    },
                    {
                        number: '3',
                        title: 'Disfruta',
                        description:
                            'Descarga contenido digital o reserva ejemplares físicos al instante.',
                    },
                ]" :key="index" class="relative text-center group animate-slide-up" :style="{
                        animationDelay: `${index * 0.2 + 0.3}s`,
                    }" data-animate>
                    <div class="relative z-10">
                        <div
                            class="w-20 h-20 bg-primary text-primary-foreground rounded-2xl flex items-center justify-center mx-auto mb-6 text-xl font-bold shadow-lg shadow-primary/25 group-hover:scale-110 transition-transform duration-300 group-hover:shadow-primary/40 relative overflow-hidden">
                            <span class="z-10">{{ step.number }}</span>
                            <div class="absolute inset-0 bg-gradient-to-br from-white/20 to-transparent"></div>
                        </div>
                        <h3
                            class="text-xl font-semibold text-foreground mb-3 group-hover:text-primary transition-colors duration-300">
                            {{ step.title }}
                        </h3>
                        <p class="text-muted-foreground group-hover:text-foreground/80 transition-colors duration-300">
                            {{ step.description }}
                        </p>
                    </div>
                    <div v-if="index < 2"
                        class="absolute top-10 left-1/2 w-32 h-px bg-gradient-to-r from-primary to-transparent md:block hidden group-hover:scale-x-150 transition-transform duration-500"
                        :class="{
                            'animate-pulse': index === 0,
                        }"></div>
                </div>
            </div>
        </div>
    </section>
</template>
