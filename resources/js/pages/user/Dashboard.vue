<!-- resources/js/pages/user/Dashboard.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';

interface Props {
    recentDownloads?: Array<{
        id: number;
        downloaded_at: string;
        book: {
            title: string;
        };
    }>;
    activeLoans?: Array<{
        id: number;
        due_date: string;
        physical_copy: {
            book: {
                title: string;
            };
        };
    }>;
    emailVerified?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    recentDownloads: () => [],
    activeLoans: () => [],
    emailVerified: false,
});

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Mi Dashboard',
        href: dashboard().url,
    },
];
</script>

<template>
    <Head title="Mi Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">
                    Mi Biblioteca Digital
                </h1>
                <p class="text-gray-600 mt-2">
                    Bienvenido a tu espacio personal de lectura
                    <span v-if="!emailVerified" class="text-yellow-600 text-sm ml-2">
                        (Email no verificado)
                    </span>
                </p>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-8">
                <!-- Descargas Hoy -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-blue-500 rounded-md flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Descargas Hoy
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ $page.props.auth.user.downloads_today || 0 }}/5
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Préstamos Activos -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-green-500 rounded-md flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Préstamos Activos
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ activeLoans.length }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Límite Préstamos -->
                <div class="bg-white overflow-hidden shadow rounded-lg">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="w-8 h-8 bg-purple-500 rounded-md flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                                    </svg>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-gray-500 truncate">
                                        Límite Préstamos
                                    </dt>
                                    <dd class="text-lg font-medium text-gray-900">
                                        {{ activeLoans.length }}/{{ $page.props.auth.user.max_concurrent_loans }}
                                    </dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Descargas Recientes -->
                <div class="bg-white shadow rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">
                            Descargas Recientes
                        </h2>
                        <div v-if="recentDownloads.length > 0" class="space-y-3">
                            <div v-for="download in recentDownloads" :key="download.id" 
                                 class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ download.book.title }}</h3>
                                    <p class="text-sm text-gray-500">
                                        Descargado el {{ new Date(download.downloaded_at).toLocaleDateString() }}
                                    </p>
                                </div>
                                <button class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                    Volver a descargar
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <p class="text-gray-500">Aún no has descargado ningún libro</p>
                            <a href="/books" class="text-blue-600 hover:text-blue-800 font-medium mt-2 inline-block">
                                Explorar catálogo
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Préstamos Activos -->
                <div class="bg-white shadow rounded-lg">
                    <div class="p-6">
                        <h2 class="text-lg font-semibold text-gray-900 mb-4">
                            Mis Préstamos Activos
                        </h2>
                        <div v-if="activeLoans.length > 0" class="space-y-3">
                            <div v-for="loan in activeLoans" :key="loan.id" 
                                 class="flex items-center justify-between p-3 border border-gray-200 rounded-lg">
                                <div>
                                    <h3 class="font-medium text-gray-900">{{ loan.physical_copy.book.title }}</h3>
                                    <p class="text-sm text-gray-500">
                                        Vence el {{ new Date(loan.due_date).toLocaleDateString() }}
                                    </p>
                                </div>
                                <button class="text-green-600 hover:text-green-800 text-sm font-medium">
                                    Renovar
                                </button>
                            </div>
                        </div>
                        <div v-else class="text-center py-8">
                            <svg class="w-12 h-12 text-gray-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.746 0 3.332.477 4.5 1.253v13C19.832 18.477 18.246 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                            </svg>
                            <p class="text-gray-500">No tienes préstamos activos</p>
                            <a href="/books" class="text-green-600 hover:text-green-800 font-medium mt-2 inline-block">
                                Buscar libros físicos
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white shadow rounded-lg p-6 mt-8">
                <h2 class="text-lg font-semibold text-gray-900 mb-4">Acciones Rápidas</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <a href="/books" class="group block p-4 border border-gray-200 rounded-lg hover:border-blue-500 hover:bg-blue-50 transition-colors text-center">
                        <svg class="w-8 h-8 text-blue-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                        </svg>
                        <h3 class="font-medium text-gray-900 group-hover:text-blue-700">Explorar Catálogo</h3>
                    </a>

                    <a href="/profile" class="group block p-4 border border-gray-200 rounded-lg hover:border-green-500 hover:bg-green-50 transition-colors text-center">
                        <svg class="w-8 h-8 text-green-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                        </svg>
                        <h3 class="font-medium text-gray-900 group-hover:text-green-700">Mi Perfil</h3>
                    </a>

                    <a href="/downloads" class="group block p-4 border border-gray-200 rounded-lg hover:border-purple-500 hover:bg-purple-50 transition-colors text-center">
                        <svg class="w-8 h-8 text-purple-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        <h3 class="font-medium text-gray-900 group-hover:text-purple-700">Mis Descargas</h3>
                    </a>

                    <a href="/loans" class="group block p-4 border border-gray-200 rounded-lg hover:border-yellow-500 hover:bg-yellow-50 transition-colors text-center">
                        <svg class="w-8 h-8 text-yellow-600 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <h3 class="font-medium text-gray-900 group-hover:text-yellow-700">Mis Préstamos</h3>
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>