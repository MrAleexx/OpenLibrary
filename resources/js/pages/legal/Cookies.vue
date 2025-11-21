<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';

defineProps<{
    canRegister?: boolean;
}>();
</script>

<template>

    <Head title="Política de Cookies" />

    <div class="min-h-screen bg-background flex flex-col">
        <LandingHeader :can-register="canRegister" />

        <main class="flex-grow container mx-auto px-4 py-12 mt-20">
            <div class="max-w-4xl mx-auto prose dark:prose-invert">
                <Breadcrumbs :breadcrumbs="[
                    { title: 'Inicio', href: '/' },
                    { title: 'Política de Cookies' }
                ]" class="mb-8" />

                <h1 class="text-4xl font-bold mb-8 text-foreground">Política de Cookies</h1>

                <div class="bg-card border border-border rounded-lg p-8 shadow-sm">
                    <p class="text-muted-foreground mb-6">
                        Última actualización: {{ new Date().toLocaleDateString() }}
                    </p>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">1. ¿Qué son las Cookies?</h2>
                        <p class="text-muted-foreground leading-relaxed mb-4">
                            Las cookies son pequeños archivos de texto que se almacenan en tu dispositivo cuando visitas
                            nuestro sitio web. Nos ayudan a recordar tus preferencias y a mejorar tu experiencia de
                            navegación.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">2. ¿Cómo usamos las Cookies?</h2>
                        <p class="text-muted-foreground leading-relaxed mb-4">
                            Utilizamos cookies para los siguientes propósitos:
                        </p>
                        <ul class="list-disc pl-6 text-muted-foreground space-y-2">
                            <li><strong>Esenciales:</strong> Necesarias para el funcionamiento básico del sitio (ej.
                                mantener tu sesión iniciada).</li>
                            <li><strong>Rendimiento:</strong> Nos ayudan a entender cómo interactúas con el sitio para
                                mejorarlo.</li>
                            <li><strong>Funcionales:</strong> Recuerdan tus preferencias (ej. idioma, tema
                                oscuro/claro).</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">3. Gestión de Cookies</h2>
                        <p class="text-muted-foreground leading-relaxed mb-4">
                            Puedes controlar y/o eliminar las cookies según desees. Puedes borrar todas las cookies que
                            ya están en tu ordenador y puedes configurar la mayoría de los navegadores para que eviten
                            que se coloquen. Sin embargo, si haces esto, es posible que tengas que ajustar manualmente
                            algunas preferencias cada vez que visites un sitio y que algunos servicios y funcionalidades
                            no funcionen.
                        </p>
                    </section>
                </div>
            </div>
        </main>
    </div>
</template>
