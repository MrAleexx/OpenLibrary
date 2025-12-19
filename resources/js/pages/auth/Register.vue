<!-- resources/js/pages/auth/Register.vue -->
<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { Form, Head } from '@inertiajs/vue3';
import { BookOpen, LoaderCircle, Shield, Users } from 'lucide-vue-next';
</script>

<template>

    <Head title="Crear cuenta" />

    <div class="grid min-h-screen lg:grid-cols-2">
        <!-- Lado izquierdo: Hero Section -->
        <div
            class="relative hidden flex-col justify-between overflow-hidden bg-gradient-to-br from-indigo-900 via-gray-900 to-indigo-900 p-12 text-white lg:flex">
            <!-- Imagen de fondo con overlay -->
            <div
                class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?q=80&w=2128')] bg-cover bg-center opacity-20">
            </div>

            <div class="relative z-10">
                <div class="mb-8 flex items-center gap-2">
                    <img src="/images/logos/Logo-Sinfondo-white.png" alt="Logo" class="h-20 w-auto" />
                </div>

                <div class="max-w-md">
                    <h1 class="mb-6 text-4xl leading-tight font-bold">
                        Únete a nuestra comunidad de lectores
                    </h1>
                    <p class="mb-8 text-lg text-blue-100">
                        Crea tu cuenta y comienza a explorar miles de libros y
                        recursos educativos.
                    </p>
                </div>
            </div>

            <div class="relative z-10 space-y-4">
                <div class="flex items-start gap-3">
                    <div class="mt-1 rounded-lg bg-white/10 p-2 backdrop-blur-sm">
                        <Users class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="mb-1 font-semibold">Comunidad activa</h3>
                        <p class="text-sm text-blue-100">
                            Miles de lectores compartiendo conocimiento
                        </p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="mt-1 rounded-lg bg-white/10 p-2 backdrop-blur-sm">
                        <Shield class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="mb-1 font-semibold">Seguro y privado</h3>
                        <p class="text-sm text-blue-100">
                            Tus datos están protegidos
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lado derecho: Formulario de Registro -->
        <div class="flex items-center justify-center overflow-y-auto bg-background p-8">
            <div class="w-full max-w-md">
                <div class="mb-8 flex items-center justify-center gap-2 lg:hidden">
                    <img src="/images/logos/transparente-dark.png" alt="Logo" class="block h-20 w-auto dark:hidden" />
                    <img src="/images/logos/transparente-white.png" alt="Logo" class="hidden h-20 w-auto dark:block" />
                </div>
                <div class="mb-8">
                    <h2 class="mb-2 text-3xl font-bold text-foreground">
                        Crear cuenta
                    </h2>
                    <p class="text-muted-foreground">
                        Ingresa tus datos para registrarte
                    </p>
                </div>

                <Form v-bind="store.form()" :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }" class="space-y-5">
                    <div class="space-y-4">
                        <!-- Fila: Nombre y Apellido -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="name" class="font-medium text-foreground">Nombres</Label>
                                <Input id="name" type="text" required autofocus :tabindex="1" autocomplete="given-name"
                                    name="name" placeholder="Tus nombres" class="h-11" />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="last_name" class="font-medium text-foreground">Apellidos</Label>
                                <Input id="last_name" type="text" required :tabindex="2" autocomplete="family-name"
                                    name="last_name" placeholder="Tus apellidos" class="h-11" />
                                <InputError :message="errors.last_name" />
                            </div>
                        </div>

                        <!-- Fila: DNI y Teléfono -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="dni" class="font-medium text-foreground">DNI</Label>
                                <Input id="dni" type="text" required :tabindex="3" autocomplete="off" name="dni"
                                    placeholder="12345678" maxlength="8" class="h-11" />
                                <InputError :message="errors.dni" />
                            </div>

                            <div class="space-y-2">
                                <Label for="phone" class="font-medium text-foreground">Teléfono</Label>
                                <Input id="phone" type="text" required :tabindex="4" autocomplete="tel" name="phone"
                                    placeholder="987654321" maxlength="9" class="h-11" />
                                <InputError :message="errors.phone" />
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <Label for="email" class="font-medium text-foreground">Correo electrónico</Label>
                            <Input id="email" type="email" required :tabindex="5" autocomplete="email" name="email"
                                placeholder="tu@email.com" class="h-11" />
                            <InputError :message="errors.email" />
                        </div>

                        <!-- Contraseña -->
                        <div class="space-y-2">
                            <Label for="password" class="font-medium text-foreground">Contraseña</Label>
                            <Input id="password" type="password" required :tabindex="6" autocomplete="new-password"
                                name="password" placeholder="••••••••" class="h-11" />
                            <InputError :message="errors.password" />
                        </div>

                        <!-- Confirmar contraseña -->
                        <div class="space-y-2">
                            <Label for="password_confirmation" class="font-medium text-foreground">Confirmar
                                contraseña</Label>
                            <Input id="password_confirmation" type="password" required :tabindex="7"
                                autocomplete="new-password" name="password_confirmation" placeholder="••••••••"
                                class="h-11" />
                            <InputError :message="errors.password_confirmation" />
                        </div>

                        <Button type="submit"
                            class="h-11 w-full bg-primary font-medium text-primary-foreground hover:bg-primary/90"
                            tabindex="8" :disabled="processing" data-test="register-user-button">
                            <LoaderCircle v-if="processing" class="mr-2 h-4 w-4 animate-spin" />
                            Crear cuenta
                        </Button>
                    </div>

                    <div class="pt-4 text-center text-sm text-muted-foreground">
                        ¿Ya tienes una cuenta?
                        <TextLink :href="login()" class="font-medium text-primary hover:text-primary/80" :tabindex="9">
                            Iniciar sesión
                        </TextLink>
                    </div>
                </Form>
            </div>
        </div>
    </div>
</template>
