<!-- resources/js/pages/auth/VerifyEmail.vue -->
<script setup lang="ts">
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import AuthLayout from '@/layouts/AuthLayout.vue';
import { logout } from '@/routes';
import { send } from '@/routes/verification';
import { Form, Head } from '@inertiajs/vue3';
import { LoaderCircle } from 'lucide-vue-next';

defineProps<{
    status?: string;
}>();
</script>

<template>
    <AuthLayout title="Verificar correo electrónico"
        description="Por favor verifica tu correo electrónico haciendo clic en el enlace que te acabamos de enviar.">

        <Head title="Verificación de correo" />

        <div v-if="status === 'verification-link-sent'" class="mb-4 text-center text-sm font-medium text-green-600">
            Se ha enviado un nuevo enlace de verificación a la dirección de correo que proporcionaste durante el
            registro.
        </div>

        <Form v-bind="send.form()" class="space-y-6 text-center" v-slot="{ processing }">
            <Button :disabled="processing" variant="secondary">
                <LoaderCircle v-if="processing" class="h-4 w-4 animate-spin" />
                Reenviar correo de verificación
            </Button>

            <TextLink :href="logout()" as="button" class="mx-auto block text-sm">
                Cerrar sesión
            </TextLink>
        </Form>
    </AuthLayout>
</template>
