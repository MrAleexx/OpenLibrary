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
import { LoaderCircle, BookOpen, Users, Shield } from 'lucide-vue-next';
</script>

<template>
    <Head title="Crear cuenta" />
    
    <div class="min-h-screen grid lg:grid-cols-2">
        <!-- Lado izquierdo: Hero Section -->
        <div class="hidden lg:flex bg-gradient-to-br from-indigo-900 via-gray-900 to-indigo-900 text-white p-12 flex-col justify-between relative overflow-hidden">
            <!-- Imagen de fondo con overlay -->
            <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1481627834876-b7833e8f5570?q=80&w=2128')] bg-cover bg-center opacity-20"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-8">
                    <BookOpen class="h-8 w-8" />
                    <span class="text-2xl font-bold">OpenLibrary</span>
                </div>

                <div class="max-w-md">
                    <h1 class="text-4xl font-bold mb-6 leading-tight">
                        Únete a nuestra comunidad de lectores
                    </h1>
                    <p class="text-lg text-blue-100 mb-8">
                        Crea tu cuenta y comienza a explorar miles de libros y recursos educativos.
                    </p>
                </div>
            </div>

            <div class="relative z-10 space-y-4">
                <div class="flex items-start gap-3">
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-2 mt-1">
                        <Users class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold mb-1">Comunidad activa</h3>
                        <p class="text-sm text-blue-100">Miles de lectores compartiendo conocimiento</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="bg-white/10 backdrop-blur-sm rounded-lg p-2 mt-1">
                        <Shield class="h-5 w-5" />
                    </div>
                    <div>
                        <h3 class="font-semibold mb-1">Seguro y privado</h3>
                        <p class="text-sm text-blue-100">Tus datos están protegidos</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lado derecho: Formulario de Registro -->
        <div class="flex items-center justify-center p-8 bg-white overflow-y-auto">
            <div class="w-full max-w-md">
                <!-- Logo móvil -->
                <div class="lg:hidden flex items-center gap-2 mb-8 justify-center">
                    <BookOpen class="h-6 w-6 text-slate-900" />
                    <span class="text-xl font-bold text-slate-900">OpenReads</span>
                </div>

                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-slate-900 mb-2">Crear cuenta</h2>
                    <p class="text-slate-600">Ingresa tus datos para registrarte</p>
                </div>

                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password', 'password_confirmation']"
                    v-slot="{ errors, processing }"
                    class="space-y-5"
                >
                    <div class="space-y-4">
                        <!-- Fila: Nombre y Apellido -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="name" class="text-slate-700 font-medium">Nombres</Label>
                                <Input
                                    id="name"
                                    type="text"
                                    required
                                    autofocus
                                    :tabindex="1"
                                    autocomplete="given-name"
                                    name="name"
                                    placeholder="Tus nombres"
                                    class="h-11"
                                />
                                <InputError :message="errors.name" />
                            </div>

                            <div class="space-y-2">
                                <Label for="last_name" class="text-slate-700 font-medium">Apellidos</Label>
                                <Input
                                    id="last_name"
                                    type="text"
                                    required
                                    :tabindex="2"
                                    autocomplete="family-name"
                                    name="last_name"
                                    placeholder="Tus apellidos"
                                    class="h-11"
                                />
                                <InputError :message="errors.last_name" />
                            </div>
                        </div>

                        <!-- Fila: DNI y Teléfono -->
                        <div class="grid grid-cols-2 gap-4">
                            <div class="space-y-2">
                                <Label for="dni" class="text-slate-700 font-medium">DNI</Label>
                                <Input
                                    id="dni"
                                    type="text"
                                    required
                                    :tabindex="3"
                                    autocomplete="off"
                                    name="dni"
                                    placeholder="12345678"
                                    maxlength="8"
                                    class="h-11"
                                />
                                <InputError :message="errors.dni" />
                            </div>

                            <div class="space-y-2">
                                <Label for="phone" class="text-slate-700 font-medium">Teléfono</Label>
                                <Input
                                    id="phone"
                                    type="text"
                                    required
                                    :tabindex="4"
                                    autocomplete="tel"
                                    name="phone"
                                    placeholder="987654321"
                                    maxlength="9"
                                    class="h-11"
                                />
                                <InputError :message="errors.phone" />
                            </div>
                        </div>

                        <!-- Email -->
                        <div class="space-y-2">
                            <Label for="email" class="text-slate-700 font-medium">Correo electrónico</Label>
                            <Input
                                id="email"
                                type="email"
                                required
                                :tabindex="5"
                                autocomplete="email"
                                name="email"
                                placeholder="tu@email.com"
                                class="h-11"
                            />
                            <InputError :message="errors.email" />
                        </div>

                        <!-- Contraseña -->
                        <div class="space-y-2">
                            <Label for="password" class="text-slate-700 font-medium">Contraseña</Label>
                            <Input
                                id="password"
                                type="password"
                                required
                                :tabindex="6"
                                autocomplete="new-password"
                                name="password"
                                placeholder="••••••••"
                                class="h-11"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <!-- Confirmar contraseña -->
                        <div class="space-y-2">
                            <Label for="password_confirmation" class="text-slate-700 font-medium">Confirmar contraseña</Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                required
                                :tabindex="7"
                                autocomplete="new-password"
                                name="password_confirmation"
                                placeholder="••••••••"
                                class="h-11"
                            />
                            <InputError :message="errors.password_confirmation" />
                        </div>

                        <Button
                            type="submit"
                            class="w-full h-11 bg-blue-600 hover:bg-blue-700 text-white font-medium"
                            tabindex="8"
                            :disabled="processing"
                            data-test="register-user-button"
                        >
                            <LoaderCircle
                                v-if="processing"
                                class="h-4 w-4 animate-spin mr-2"
                            />
                            Crear cuenta
                        </Button>
                    </div>

                    <div class="text-center text-sm text-slate-600 pt-4">
                        ¿Ya tienes una cuenta?
                        <TextLink
                            :href="login()"
                            class="text-blue-600 hover:text-blue-700 font-medium"
                            :tabindex="9"
                        >
                            Iniciar sesión
                        </TextLink>
                    </div>
                </Form>
            </div>
        </div>
    </div>
</template>