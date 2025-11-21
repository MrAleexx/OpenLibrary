<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';

defineProps<{
    canRegister?: boolean;
}>();
</script>

<template>

    <Head title="Políticas de Uso" />

    <div class="min-h-screen bg-background flex flex-col">
        <LandingHeader :can-register="canRegister" />

        <main class="flex-grow container mx-auto px-4 py-12 mt-20">
            <div class="max-w-4xl mx-auto prose dark:prose-invert">
                <Breadcrumbs :breadcrumbs="[
                    { title: 'Inicio', href: '/' },
                    { title: 'Políticas de Uso' }
                ]" class="mb-8" />

                <h1 class="text-4xl font-bold mb-8 text-foreground">Políticas de Uso</h1>

                <div class="bg-card border border-border rounded-lg p-8 shadow-sm">
                    <p class="text-muted-foreground mb-6">
                        Última actualización: {{ new Date().toLocaleDateString() }}
                    </p>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">1. Préstamos de Libros</h2>
                        <p class="text-muted-foreground leading-relaxed mb-4">
                            Los usuarios pueden tomar prestados libros digitales por un período determinado. Es
                            responsabilidad del usuario devolver o renovar el préstamo a tiempo.
                        </p>
                        <ul class="list-disc pl-6 text-muted-foreground space-y-2">
                            <li>Límite de préstamos simultáneos: 5 libros.</li>
                            <li>Duración estándar del préstamo: 14 días.</li>
                            <li>Renovaciones permitidas: 1 vez por libro (si no hay reservas pendientes).</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">2. Cuidado de los Recursos</h2>
                        <p class="text-muted-foreground leading-relaxed mb-4">
                            Aunque nuestros recursos son digitales, el acceso a ellos es un privilegio. El intento de
                            copiar, distribuir o modificar ilegalmente los archivos protegidos resultará en la
                            suspensión inmediata de la cuenta.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">3. Comportamiento en la Comunidad</h2>
                        <p class="text-muted-foreground leading-relaxed mb-4">
                            Esperamos que todos los usuarios se traten con respeto. Cualquier forma de acoso, discurso
                            de odio o comportamiento inapropiado en las secciones de comentarios o foros no será
                            tolerada.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">4. Sanciones</h2>
                        <p class="text-muted-foreground leading-relaxed">
                            El incumplimiento de estas políticas puede resultar en advertencias temporales, suspensión
                            de privilegios de préstamo o, en casos graves, la terminación permanente de la cuenta.
                        </p>
                    </section>
                </div>
            </div>
        </main>
    </div>
</template>
