<!-- resources/js/pages/auth/Login.vue -->
<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';
import { BookOpen, Check, LoaderCircle, Plus } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>

    <Head title="Iniciar sesión" />

    <div class="grid min-h-screen lg:grid-cols-2">
        <!-- Lado izquierdo: Hero Section -->
        <div
            class="relative hidden flex-col justify-between overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 p-12 text-white lg:flex">
            <!-- Imagen de fondo con overlay -->
            <div
                class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1521587760476-6c12a4b040da?q=80&w=2070')] bg-cover bg-center opacity-20">
            </div>

            <div class="relative z-10">
                <div class="mb-8 flex items-center gap-2">
                    <img src="/images/logos/transparente-white.png" alt="Logo" class="h-20 w-auto" />
                </div>

                <div class="max-w-md">
                    <h1 class="mb-6 text-4xl leading-tight font-bold">
                        Descubre mundos infinitos a través de la lectura
                    </h1>
                    <p class="mb-8 text-lg text-slate-300">
                        Accede a miles de libros, artículos y recursos digitales
                        desde cualquier lugar.
                    </p>
                </div>
            </div>

            <div class="relative z-10 space-y-4">
                <div class="flex items-start gap-3">
                    <div class="mt-1 rounded-full bg-white/10 p-1">
                        <Check class="h-4 w-4" />
                    </div>
                    <div>
                        <h3 class="mb-1 font-semibold">Colección ilimitada</h3>
                        <p class="text-sm text-slate-300">
                            Miles de títulos disponibles 24/7
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="mt-1 rounded-full bg-white/10 p-1">
                        <Plus class="h-4 w-4" />
                    </div>
                    <div>
                        <h3 class="mb-1 font-semibold">
                            Acceso multiplataforma
                        </h3>
                        <p class="text-sm text-slate-300">
                            Lee desde tu dispositivo favorito
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lado derecho: Formulario de Login -->
        <div class="flex items-center justify-center bg-background p-8">
            <div class="w-full max-w-md">
                <!-- Logo móvil -->
                <div class="mb-8 flex items-center justify-center gap-2 lg:hidden">
                    <img src="/images/logos/transparente-dark.png" alt="Logo" class="block h-20 w-auto dark:hidden" />
                    <img src="/images/logos/transparente-white.png" alt="Logo" class="hidden h-20 w-auto dark:block" />
                </div>

                <div class="mb-8">
                    <h2 class="mb-2 text-3xl font-bold text-foreground">
                        Iniciar sesión
                    </h2>
                    <p class="text-muted-foreground">
                        Accede a tu cuenta para continuar
                    </p>
                </div>

                <div v-if="status"
                    class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 text-sm text-green-800">
                    {{ status }}
                </div>

                <Form v-bind="store.form()" :reset-on-success="['password']" v-slot="{ errors, processing }"
                    class="space-y-6">
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label for="email" class="font-medium text-foreground">Correo electrónico</Label>
                            <Input id="email" type="email" name="email" required autofocus :tabindex="1"
                                autocomplete="email" placeholder="tu@email.com" class="h-11" />
                            <InputError :message="errors.email" />
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="font-medium text-foreground">Contraseña</Label>
                                <TextLink v-if="canResetPassword" :href="request()"
                                    class="text-sm text-primary hover:text-primary/80" :tabindex="5">
                                    ¿Olvidaste tu contraseña?
                                </TextLink>
                            </div>
                            <Input id="password" type="password" name="password" required :tabindex="2"
                                autocomplete="current-password" placeholder="••••••••" class="h-11" />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="flex items-center">
                            <Label for="remember" class="flex cursor-pointer items-center space-x-2">
                                <Checkbox id="remember" name="remember" :tabindex="3" />
                                <span class="text-sm text-muted-foreground">Mantener sesión iniciada</span>
                            </Label>
                        </div>

                        <Button type="submit"
                            class="h-11 w-full bg-primary font-medium text-primary-foreground hover:bg-primary/90"
                            :tabindex="4" :disabled="processing" data-test="login-button">
                            <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                            Iniciar sesión
                        </Button>
                    </div>

                    <div class="pt-4 text-center text-sm text-muted-foreground" v-if="canRegister">
                        ¿No tienes una cuenta?
                        <TextLink :href="register()" :tabindex="5"
                            class="font-medium text-primary hover:text-primary/80">
                            Crear cuenta
                        </TextLink>
                    </div>
                </Form>
            </div>
        </div>
    </div>
</template>
