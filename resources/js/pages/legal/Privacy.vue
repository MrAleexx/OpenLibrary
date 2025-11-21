<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';

defineProps<{
    canRegister?: boolean;
}>();
</script>

<template>

    <Head title="Política de Privacidad" />

    <div class="min-h-screen bg-background flex flex-col">
        <LandingHeader :can-register="canRegister" />

        <main class="flex-grow container mx-auto px-4 py-12 mt-20">
            <div class="max-w-4xl mx-auto prose dark:prose-invert">
                <Breadcrumbs :breadcrumbs="[
                    { title: 'Inicio', href: '/' },
                    { title: 'Política de Privacidad' }
                ]" class="mb-8" />

                <h1 class="text-4xl font-bold mb-8 text-foreground">Política de Privacidad</h1>

                <div class="bg-card border border-border rounded-lg p-8 shadow-sm">
                    <p class="text-muted-foreground mb-6">
                        Última actualización: {{ new Date().toLocaleDateString() }}
                    </p>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">1. Información que Recopilamos</h2>
                        <p class="text-muted-foreground leading-relaxed mb-4">
                            En OpenLibrary, nos tomamos muy en serio tu privacidad. Recopilamos información que nos
                            proporcionas directamente, como cuando creas una cuenta, realizas un préstamo o te pones en
                            contacto con nosotros.
                        </p>
                        <ul class="list-disc pl-6 text-muted-foreground space-y-2">
                            <li>Información de contacto (nombre, correo electrónico).</li>
                            <li>Información de perfil (institución, carrera).</li>
                            <li>Historial de préstamos y lecturas.</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">2. Uso de la Información</h2>
                        <p class="text-muted-foreground leading-relaxed mb-4">
                            Utilizamos la información recopilada para:
                        </p>
                        <ul class="list-disc pl-6 text-muted-foreground space-y-2">
                            <li>Proporcionar, mantener y mejorar nuestros servicios.</li>
                            <li>Procesar tus préstamos y devoluciones.</li>
                            <li>Enviarte notificaciones técnicas y de soporte.</li>
                            <li>Personalizar tu experiencia de usuario.</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">3. Compartir Información</h2>
                        <p class="text-muted-foreground leading-relaxed mb-4">
                            No vendemos ni alquilamos tu información personal a terceros. Solo compartimos información
                            cuando es necesario para cumplir con la ley, proteger nuestros derechos o con tu
                            consentimiento explícito.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">4. Seguridad de los Datos</h2>
                        <p class="text-muted-foreground leading-relaxed mb-4">
                            Implementamos medidas de seguridad técnicas y organizativas para proteger tus datos
                            personales contra el acceso no autorizado, la pérdida o la alteración.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">5. Tus Derechos</h2>
                        <p class="text-muted-foreground leading-relaxed">
                            Tienes derecho a acceder, corregir o eliminar tu información personal. Si deseas ejercer
                            estos derechos, por favor contáctanos a través de nuestros canales de soporte.
                        </p>
                    </section>
                </div>
            </div>
        </main>
    </div>
</template>
