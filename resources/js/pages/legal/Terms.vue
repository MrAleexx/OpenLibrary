<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import Breadcrumbs from '@/components/Breadcrumbs.vue';

defineProps<{
    canRegister?: boolean;
}>();
</script>

<template>

    <Head title="Términos y Condiciones" />

    <div class="min-h-screen bg-background flex flex-col">
        <LandingHeader :can-register="canRegister" />

        <main class="flex-grow container mx-auto px-4 py-12 mt-20">
            <div class="max-w-4xl mx-auto prose dark:prose-invert">
                <Breadcrumbs :breadcrumbs="[
                    { title: 'Inicio', href: '/' },
                    { title: 'Términos y Condiciones' }
                ]" class="mb-8" />

                <h1 class="text-4xl font-bold mb-8 text-foreground">Términos y Condiciones</h1>

                <div class="bg-card border border-border rounded-lg p-8 shadow-sm">
                    <p class="text-muted-foreground mb-6">
                        Última actualización: {{ new Date().toLocaleDateString() }}
                    </p>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">1. Introducción</h2>
                        <p class="text-muted-foreground leading-relaxed mb-4">
                            Bienvenido a OpenLibrary. Al acceder y utilizar nuestro sitio web, aceptas cumplir con los
                            siguientes términos y condiciones. Si no estás de acuerdo con alguna parte de estos
                            términos, te rogamos que no utilices nuestros servicios.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">2. Uso del Servicio</h2>
                        <p class="text-muted-foreground leading-relaxed mb-4">
                            OpenLibrary proporciona acceso a una colección digital de recursos educativos. Te
                            comprometes a utilizar este servicio solo para fines legales y de una manera que no infrinja
                            los derechos de, restrinja o inhiba el uso y disfrute del servicio por parte de cualquier
                            tercero.
                        </p>
                        <ul class="list-disc pl-6 text-muted-foreground space-y-2">
                            <li>No debes usar el servicio para transmitir contenido ilegal o dañino.</li>
                            <li>No debes intentar acceder sin autorización a nuestros sistemas.</li>
                            <li>Debes respetar los derechos de propiedad intelectual de los contenidos.</li>
                        </ul>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">3. Cuentas de Usuario</h2>
                        <p class="text-muted-foreground leading-relaxed mb-4">
                            Para acceder a ciertas funciones, es posible que debas crear una cuenta. Eres responsable de
                            mantener la confidencialidad de tu contraseña y cuenta, y eres totalmente responsable de
                            todas las actividades que ocurran bajo tu contraseña o cuenta.
                        </p>
                    </section>

                    <section class="mb-8">
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">4. Propiedad Intelectual</h2>
                        <p class="text-muted-foreground leading-relaxed mb-4">
                            El contenido, diseño y funcionalidad de OpenLibrary son propiedad exclusiva de OpenLibrary y
                            sus licenciantes, y están protegidos por leyes de derechos de autor y otras leyes de
                            propiedad intelectual.
                        </p>
                    </section>

                    <section>
                        <h2 class="text-2xl font-semibold mb-4 text-foreground">5. Modificaciones</h2>
                        <p class="text-muted-foreground leading-relaxed">
                            Nos reservamos el derecho de modificar estos términos en cualquier momento. Te recomendamos
                            revisar esta página periódicamente para estar al tanto de cualquier cambio.
                        </p>
                    </section>
                </div>
            </div>
        </main>
    </div>
</template>
