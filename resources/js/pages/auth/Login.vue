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
import { LoaderCircle, BookOpen, Check, Plus } from 'lucide-vue-next';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <Head title="Iniciar sesión" />
    
    <div class="min-h-screen grid lg:grid-cols-2">
        <!-- Lado izquierdo: Hero Section -->
        <div class="hidden lg:flex bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 text-white p-12 flex-col justify-between relative overflow-hidden">
            <!-- Imagen de fondo con overlay -->
            <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1521587760476-6c12a4b040da?q=80&w=2070')] bg-cover bg-center opacity-20"></div>
            
            <div class="relative z-10">
                <div class="flex items-center gap-2 mb-8">
                    <BookOpen class="h-8 w-8" />
                    <span class="text-2xl font-bold">OpenLibrary</span>
                </div>

                <div class="max-w-md">
                    <h1 class="text-4xl font-bold mb-6 leading-tight">
                        Descubre mundos infinitos a través de la lectura
                    </h1>
                    <p class="text-lg text-slate-300 mb-8">
                        Accede a miles de libros, artículos y recursos digitales desde cualquier lugar.
                    </p>
                </div>
            </div>

            <div class="relative z-10 space-y-4">
                <div class="flex items-start gap-3">
                    <div class="bg-white/10 rounded-full p-1 mt-1">
                        <Check class="h-4 w-4" />
                    </div>
                    <div>
                        <h3 class="font-semibold mb-1">Colección ilimitada</h3>
                        <p class="text-sm text-slate-300">Miles de títulos disponibles 24/7</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="bg-white/10 rounded-full p-1 mt-1">
                        <Plus class="h-4 w-4" />
                    </div>
                    <div>
                        <h3 class="font-semibold mb-1">Acceso multiplataforma</h3>
                        <p class="text-sm text-slate-300">Lee desde tu dispositivo favorito</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Lado derecho: Formulario de Login -->
        <div class="flex items-center justify-center p-8 bg-white">
            <div class="w-full max-w-md">
                <!-- Logo móvil -->
                <div class="lg:hidden flex items-center gap-2 mb-8 justify-center">
                    <BookOpen class="h-6 w-6 text-slate-900" />
                    <span class="text-xl font-bold text-slate-900">OpenReads</span>
                </div>

                <div class="mb-8">
                    <h2 class="text-3xl font-bold text-slate-900 mb-2">Iniciar sesión</h2>
                    <p class="text-slate-600">Accede a tu cuenta para continuar</p>
                </div>

                <div
                    v-if="status"
                    class="mb-4 p-3 rounded-lg bg-green-50 border border-green-200 text-sm text-green-800"
                >
                    {{ status }}
                </div>

                <Form
                    v-bind="store.form()"
                    :reset-on-success="['password']"
                    v-slot="{ errors, processing }"
                    class="space-y-6"
                >
                    <div class="space-y-4">
                        <div class="space-y-2">
                            <Label for="email" class="text-slate-700 font-medium">Correo electrónico</Label>
                            <Input
                                id="email"
                                type="email"
                                name="email"
                                required
                                autofocus
                                :tabindex="1"
                                autocomplete="email"
                                placeholder="tu@email.com"
                                class="h-11"
                            />
                            <InputError :message="errors.email" />
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <Label for="password" class="text-slate-700 font-medium">Contraseña</Label>
                                <TextLink
                                    v-if="canResetPassword"
                                    :href="request()"
                                    class="text-sm text-blue-600 hover:text-blue-700"
                                    :tabindex="5"
                                >
                                    ¿Olvidaste tu contraseña?
                                </TextLink>
                            </div>
                            <Input
                                id="password"
                                type="password"
                                name="password"
                                required
                                :tabindex="2"
                                autocomplete="current-password"
                                placeholder="••••••••"
                                class="h-11"
                            />
                            <InputError :message="errors.password" />
                        </div>

                        <div class="flex items-center">
                            <Label for="remember" class="flex items-center space-x-2 cursor-pointer">
                                <Checkbox id="remember" name="remember" :tabindex="3" />
                                <span class="text-sm text-slate-700">Mantener sesión iniciada</span>
                            </Label>
                        </div>

                        <Button
                            type="submit"
                            class="w-full h-11 bg-slate-900 hover:bg-slate-800 text-white font-medium"
                            :tabindex="4"
                            :disabled="processing"
                            data-test="login-button"
                        >
                            <LoaderCircle
                                v-if="processing"
                                class="h-4 w-4 animate-spin mr-2"
                            />
                            Iniciar sesión
                        </Button>
                    </div>

                    <div
                        class="text-center text-sm text-slate-600 pt-4"
                        v-if="canRegister"
                    >
                        ¿No tienes una cuenta?
                        <TextLink 
                            :href="register()" 
                            :tabindex="5"
                            class="text-blue-600 hover:text-blue-700 font-medium"
                        >
                            Crear cuenta
                        </TextLink>
                    </div>
                </Form>
            </div>
        </div>
    </div>
</template>
