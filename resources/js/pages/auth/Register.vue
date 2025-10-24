<!-- resources/js/pages/auth/Register.vue -->
<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import AuthBase from '@/layouts/AuthLayout.vue';
import { login } from '@/routes';
import { store } from '@/routes/register';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';
</script>

<template>
    <AuthBase
        title="Crear cuenta en la biblioteca"
        description="Ingresa tus datos para crear tu cuenta"
    >
        <Head title="Registro" />

        <Form
            v-bind="store.form()"
            :reset-on-success="['password', 'password_confirmation']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <!-- Nombre -->
                <div class="grid gap-2">
                    <Label for="name">Nombres</Label>
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="given-name"
                        name="name"
                        placeholder="Tus nombres"
                    />
                    <InputError :message="errors.name" />
                </div>

                <!-- Apellidos -->
                <div class="grid gap-2">
                    <Label for="last_name">Apellidos</Label>
                    <Input
                        id="last_name"
                        type="text"
                        required
                        :tabindex="2"
                        autocomplete="family-name"
                        name="last_name"
                        placeholder="Tus apellidos"
                    />
                    <InputError :message="errors.last_name" />
                </div>

                <!-- DNI -->
                <div class="grid gap-2">
                    <Label for="dni">DNI</Label>
                    <Input
                        id="dni"
                        type="text"
                        required
                        :tabindex="3"
                        autocomplete="off"
                        name="dni"
                        placeholder="12345678"
                        maxlength="8"
                    />
                    <InputError :message="errors.dni" />
                </div>

                <!-- Teléfono -->
                <div class="grid gap-2">
                    <Label for="phone">Teléfono</Label>
                    <Input
                        id="phone"
                        type="text"
                        required
                        :tabindex="4"
                        autocomplete="tel"
                        name="phone"
                        placeholder="987654321"
                        maxlength="9"
                    />
                    <InputError :message="errors.phone" />
                </div>

                <!-- Email -->
                <div class="grid gap-2">
                    <Label for="email">Correo electrónico</Label>
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="5"
                        autocomplete="email"
                        name="email"
                        placeholder="usuario@ejemplo.com"
                    />
                    <InputError :message="errors.email" />
                </div>

                <!-- Contraseña -->
                <div class="grid gap-2">
                    <Label for="password">Contraseña</Label>
                    <Input
                        id="password"
                        type="password"
                        required
                        :tabindex="6"
                        autocomplete="new-password"
                        name="password"
                        placeholder="Tu contraseña"
                    />
                    <InputError :message="errors.password" />
                </div>

                <!-- Confirmar contraseña -->
                <div class="grid gap-2">
                    <Label for="password_confirmation">Confirmar contraseña</Label>
                    <Input
                        id="password_confirmation"
                        type="password"
                        required
                        :tabindex="7"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="Confirma tu contraseña"
                    />
                    <InputError :message="errors.password_confirmation" />
                </div>

                <Button
                    type="submit"
                    class="mt-2 w-full"
                    tabindex="8"
                    :disabled="processing"
                    data-test="register-user-button"
                >
                    <LoaderCircle
                        v-if="processing"
                        class="h-4 w-4 animate-spin"
                    />
                    Crear cuenta
                </Button>
            </div>

            <div class="text-center text-sm text-muted-foreground">
                ¿Ya tienes una cuenta?
                <TextLink
                    :href="login()"
                    class="underline underline-offset-4"
                    :tabindex="9"
                    >Iniciar sesión</TextLink
                >
            </div>
        </Form>
    </AuthBase>
</template>