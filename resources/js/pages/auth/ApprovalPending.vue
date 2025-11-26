<!-- resources/js/pages/auth/ApprovalPending.vue -->
<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Clock, LogOut, UserCog } from 'lucide-vue-next';

const logout = () => {
    router.post('/logout');
};
</script>

<template>

    <Head title="Cuenta Pendiente de Aprobación" />

    <div class="min-h-screen flex flex-col items-center justify-center bg-gray-50 dark:bg-gray-900 p-4">
        <div class="w-full max-w-md bg-white dark:bg-gray-800 rounded-lg shadow-lg overflow-hidden">
            <div class="p-8 text-center">
                <div
                    class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-amber-100 dark:bg-amber-900/30 mb-6">
                    <Clock class="h-8 w-8 text-amber-600 dark:text-amber-500" />
                </div>

                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">
                    Cuenta Pendiente
                </h1>

                <p class="text-gray-600 dark:text-gray-400 mb-8">
                    Tu registro ha sido exitoso, pero tu cuenta requiere aprobación de un administrador antes de poder
                    acceder a la biblioteca.
                    Te notificaremos cuando tu cuenta haya sido activada.
                </p>

                <div class="space-y-3">
                    <Button as-child variant="outline" class="w-full justify-center gap-2">
                        <Link href="/settings/profile">
                        <UserCog class="h-4 w-4" />
                        Editar mis datos
                        </Link>
                    </Button>

                    <Button @click="logout" variant="ghost"
                        class="w-full justify-center gap-2 text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                        <LogOut class="h-4 w-4" />
                        Cerrar sesión
                    </Button>
                </div>
            </div>

            <div
                class="bg-gray-50 dark:bg-gray-800/50 px-8 py-4 border-t border-gray-100 dark:border-gray-700 text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400">
                    Si crees que esto es un error, por favor contacta al administrador.
                </p>
            </div>
        </div>
    </div>
</template>
