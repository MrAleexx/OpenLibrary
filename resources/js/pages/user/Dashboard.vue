<!-- resources/js/pages/user/Dashboard.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { BookOpen, Download, Clock, Shield, AlertCircle, CheckCircle, ArrowRight, Search, User, BookMarked, Library, Zap, TrendingUp, RotateCcw, Calendar } from 'lucide-vue-next';
import { ref, onMounted, computed } from 'vue';

interface Props {
    recentDownloads?: Array<{
        id: number;
        downloaded_at: string;
        book: {
            title: string;
            author?: string;
        };
    }>;
    activeLoans?: Array<{
        id: number;
        due_date: string;
        physical_copy: {
            book: {
                title: string;
                author?: string;
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

// Estado para animaciones
const isVisible = ref(false);
const currentTime = ref(new Date());

// Computed para cálculos
const downloadsToday = computed(() => {
    return props.recentDownloads.filter(download => {
        const downloadDate = new Date(download.downloaded_at);
        return downloadDate.toDateString() === currentTime.value.toDateString();
    }).length;
});

const remainingDownloads = computed(() => {
    return Math.max(0, 5 - downloadsToday.value);
});

const upcomingDueLoans = computed(() => {
    const threeDaysFromNow = new Date();
    threeDaysFromNow.setDate(threeDaysFromNow.getDate() + 3);
    return props.activeLoans.filter(loan => {
        const dueDate = new Date(loan.due_date);
        return dueDate <= threeDaysFromNow;
    });
});

onMounted(() => {
    setTimeout(() => {
        isVisible.value = true;
    }, 100);
});
</script>

<template>
    <Head title="Mi Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-8">
            <!-- Header -->
            <div class="mb-8 animate-slide-up" :class="{ 'animate-in': isVisible }">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">
                            Mi Biblioteca Digital
                        </h1>
                        <p class="text-muted-foreground mt-2 flex items-center gap-2">
                            <BookOpen class="w-4 h-4 text-primary" />
                            Bienvenido a tu espacio personal de lectura
                            <span v-if="!emailVerified" class="text-warning text-sm ml-2 flex items-center gap-1">
                                <AlertCircle class="w-4 h-4" />
                                (Email no verificado)
                            </span>
                            <span v-else class="text-success text-sm ml-2 flex items-center gap-1">
                                <CheckCircle class="w-4 h-4" />
                                Cuenta verificada
                            </span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary rounded-lg border border-primary/20">
                        <Zap class="w-4 h-4" />
                        <span class="text-sm font-medium">Bienvenido, {{ $page.props.auth.user.name }}</span>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3 mb-8">
                <!-- Descargas Hoy -->
                <div 
                    class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-primary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1 animate-slide-up"
                    :class="{ 'animate-in': isVisible }"
                    style="animation-delay: 0.1s;"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground mb-2">
                                    Descargas Hoy
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ downloadsToday }}/5
                                </p>
                                <div class="flex items-center gap-1 mt-2 text-xs" :class="remainingDownloads > 2 ? 'text-success' : 'text-warning'">
                                    <TrendingUp class="w-3 h-3" />
                                    <span>{{ remainingDownloads }} restantes hoy</span>
                                </div>
                            </div>
                            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <Download class="w-6 h-6 text-primary" />
                            </div>
                        </div>
                    </div>
                    <div class="h-1 bg-gradient-to-r from-primary to-primary/60" :style="{ width: `${(downloadsToday / 5) * 100}%` }"></div>
                </div>

                <!-- Préstamos Activos -->
                <div 
                    class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-secondary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1 animate-slide-up"
                    :class="{ 'animate-in': isVisible }"
                    style="animation-delay: 0.2s;"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground mb-2">
                                    Préstamos Activos
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ activeLoans.length }}
                                </p>
                                <div v-if="upcomingDueLoans.length > 0" class="flex items-center gap-1 mt-2 text-xs text-warning">
                                    <AlertCircle class="w-3 h-3" />
                                    <span>{{ upcomingDueLoans.length }} por vencer</span>
                                </div>
                                <div v-else class="flex items-center gap-1 mt-2 text-xs text-success">
                                    <CheckCircle class="w-3 h-3" />
                                    <span>Al día</span>
                                </div>
                            </div>
                            <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <BookMarked class="w-6 h-6 text-secondary" />
                            </div>
                        </div>
                    </div>
                    <div class="h-1 bg-gradient-to-r from-secondary to-secondary/60" :style="{ width: `${(activeLoans.length / $page.props.auth.user.max_concurrent_loans) * 100}%` }"></div>
                </div>

                <!-- Límite Préstamos -->
                <div 
                    class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-primary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1 animate-slide-up"
                    :class="{ 'animate-in': isVisible }"
                    style="animation-delay: 0.3s;"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground mb-2">
                                    Límite Préstamos
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ activeLoans.length }}/{{ $page.props.auth.user.max_concurrent_loans }}
                                </p>
                                <div class="flex items-center gap-1 mt-2 text-xs text-muted-foreground">
                                    <Shield class="w-3 h-3" />
                                    <span>{{ $page.props.auth.user.max_concurrent_loans - activeLoans.length }} disponibles</span>
                                </div>
                            </div>
                            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <Library class="w-6 h-6 text-primary" />
                            </div>
                        </div>
                    </div>
                    <div class="h-1 bg-gradient-to-r from-primary to-primary/60" :style="{ width: `${(activeLoans.length / $page.props.auth.user.max_concurrent_loans) * 100}%` }"></div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Descargas Recientes -->
                <div class="bg-card shadow-lg rounded-xl border border-border p-6 animate-slide-up" :class="{ 'animate-in': isVisible }" style="animation-delay: 0.4s;">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-foreground">Descargas Recientes</h2>
                        <div class="w-2 h-2 bg-primary rounded-full animate-pulse"></div>
                    </div>
                    <div v-if="recentDownloads.length > 0" class="space-y-4">
                        <div 
                            v-for="download in recentDownloads.slice(0, 5)" 
                            :key="download.id" 
                            class="group flex items-center justify-between p-4 bg-background border border-border rounded-xl hover:border-primary/30 transition-all duration-300 hover:shadow-md hover:-translate-y-1"
                        >
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                    <Download class="w-5 h-5 text-primary" />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-foreground truncate">{{ download.book.title }}</h3>
                                    <p class="text-sm text-muted-foreground truncate" v-if="download.book.author">
                                        {{ download.book.author }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Descargado el {{ new Date(download.downloaded_at).toLocaleDateString('es-ES', { 
                                            day: 'numeric', 
                                            month: 'long', 
                                            year: 'numeric' 
                                        }) }}
                                    </p>
                                </div>
                            </div>
                            <button class="text-primary hover:text-primary/80 text-sm font-medium flex items-center gap-1 group-hover:gap-2 transition-all duration-300">
                                <Download class="w-4 h-4" />
                                <span class="hidden sm:inline">Descargar</span>
                            </button>
                        </div>
                    </div>
                    <div v-else class="text-center py-8">
                        <div class="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4">
                            <Download class="w-8 h-8 text-muted-foreground" />
                        </div>
                        <p class="text-muted-foreground mb-4">Aún no has descargado ningún libro</p>
                        <a href="/books" class="inline-flex items-center gap-2 text-primary font-medium hover:text-primary/80 transition-colors duration-300 group">
                            Explorar catálogo
                            <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300" />
                        </a>
                    </div>
                    <div v-if="recentDownloads.length > 0" class="mt-4 pt-4 border-t border-border">
                        <a href="/downloads" class="flex items-center justify-center gap-2 text-primary text-sm font-medium hover:text-primary/80 transition-colors duration-300 group">
                            Ver todas las descargas
                            <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300" />
                        </a>
                    </div>
                </div>

                <!-- Préstamos Activos -->
                <div class="bg-card shadow-lg rounded-xl border border-border p-6 animate-slide-up" :class="{ 'animate-in': isVisible }" style="animation-delay: 0.5s;">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-xl font-bold text-foreground">Mis Préstamos Activos</h2>
                        <div class="w-2 h-2 bg-secondary rounded-full animate-pulse"></div>
                    </div>
                    <div v-if="activeLoans.length > 0" class="space-y-4">
                        <div 
                            v-for="loan in activeLoans" 
                            :key="loan.id" 
                            class="group flex items-center justify-between p-4 bg-background border border-border rounded-xl hover:border-secondary/30 transition-all duration-300 hover:shadow-md hover:-translate-y-1"
                            :class="{
                                'border-warning/30 hover:border-warning/50': upcomingDueLoans.some(l => l.id === loan.id)
                            }"
                        >
                            <div class="flex items-center gap-4 flex-1">
                                <div class="w-10 h-10 bg-secondary/10 rounded-lg flex items-center justify-center group-hover:scale-110 transition-transform duration-300"
                                    :class="{
                                        'bg-warning/10': upcomingDueLoans.some(l => l.id === loan.id)
                                    }"
                                >
                                    <BookMarked class="w-5 h-5 text-secondary"
                                        :class="{
                                            'text-warning': upcomingDueLoans.some(l => l.id === loan.id)
                                        }"
                                    />
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-semibold text-foreground truncate">{{ loan.physical_copy.book.title }}</h3>
                                    <p class="text-sm text-muted-foreground truncate" v-if="loan.physical_copy.book.author">
                                        {{ loan.physical_copy.book.author }}
                                    </p>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Calendar class="w-3 h-3 text-muted-foreground" />
                                        <p class="text-xs" :class="upcomingDueLoans.some(l => l.id === loan.id) ? 'text-warning font-medium' : 'text-muted-foreground'">
                                            Vence el {{ new Date(loan.due_date).toLocaleDateString('es-ES', { 
                                                day: 'numeric', 
                                                month: 'long', 
                                                year: 'numeric' 
                                            }) }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <button class="text-secondary hover:text-secondary/80 text-sm font-medium flex items-center gap-1 group-hover:gap-2 transition-all duration-300"
                                :class="{
                                    'text-warning hover:text-warning/80': upcomingDueLoans.some(l => l.id === loan.id)
                                }"
                            >
                                <RotateCcw class="w-4 h-4" />
                                <span class="hidden sm:inline">Renovar</span>
                            </button>
                        </div>
                    </div>
                    <div v-else class="text-center py-8">
                        <div class="w-16 h-16 bg-muted rounded-full flex items-center justify-center mx-auto mb-4">
                            <BookMarked class="w-8 h-8 text-muted-foreground" />
                        </div>
                        <p class="text-muted-foreground mb-4">No tienes préstamos activos</p>
                        <a href="/books" class="inline-flex items-center gap-2 text-secondary font-medium hover:text-secondary/80 transition-colors duration-300 group">
                            Buscar libros físicos
                            <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300" />
                        </a>
                    </div>
                    <div v-if="activeLoans.length > 0" class="mt-4 pt-4 border-t border-border">
                        <a href="/loans" class="flex items-center justify-center gap-2 text-secondary text-sm font-medium hover:text-secondary/80 transition-colors duration-300 group">
                            Ver todos mis préstamos
                            <ArrowRight class="w-4 h-4 group-hover:translate-x-1 transition-transform duration-300" />
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-card shadow-lg rounded-xl border border-border p-6 animate-slide-up" :class="{ 'animate-in': isVisible }" style="animation-delay: 0.6s;">
                <h2 class="text-xl font-bold text-foreground mb-6">Acciones Rápidas</h2>
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    <a 
                        href="/books" 
                        class="group relative p-6 bg-background border border-border rounded-xl hover:border-primary/50 hover:shadow-md transition-all duration-300 hover:-translate-y-1 text-center overflow-hidden"
                    >
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="relative z-10">
                            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300">
                                <Search class="w-6 h-6 text-primary" />
                            </div>
                            <h3 class="font-semibold text-foreground group-hover:text-primary transition-colors duration-300">Explorar Catálogo</h3>
                        </div>
                    </a>

                    <a 
                        href="/profile" 
                        class="group relative p-6 bg-background border border-border rounded-xl hover:border-secondary/50 hover:shadow-md transition-all duration-300 hover:-translate-y-1 text-center overflow-hidden"
                    >
                        <div class="absolute inset-0 bg-gradient-to-br from-secondary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="relative z-10">
                            <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300">
                                <User class="w-6 h-6 text-secondary" />
                            </div>
                            <h3 class="font-semibold text-foreground group-hover:text-secondary transition-colors duration-300">Mi Perfil</h3>
                        </div>
                    </a>

                    <a 
                        href="/downloads" 
                        class="group relative p-6 bg-background border border-border rounded-xl hover:border-primary/50 hover:shadow-md transition-all duration-300 hover:-translate-y-1 text-center overflow-hidden"
                    >
                        <div class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="relative z-10">
                            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300">
                                <Download class="w-6 h-6 text-primary" />
                            </div>
                            <h3 class="font-semibold text-foreground group-hover:text-primary transition-colors duration-300">Mis Descargas</h3>
                        </div>
                    </a>

                    <a 
                        href="/loans" 
                        class="group relative p-6 bg-background border border-border rounded-xl hover:border-secondary/50 hover:shadow-md transition-all duration-300 hover:-translate-y-1 text-center overflow-hidden"
                    >
                        <div class="absolute inset-0 bg-gradient-to-br from-secondary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        <div class="relative z-10">
                            <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center mx-auto mb-3 group-hover:scale-110 transition-transform duration-300">
                                <BookMarked class="w-6 h-6 text-secondary" />
                            </div>
                            <h3 class="font-semibold text-foreground group-hover:text-secondary transition-colors duration-300">Mis Préstamos</h3>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Reading Progress -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 animate-slide-up" :class="{ 'animate-in': isVisible }" style="animation-delay: 0.7s;">
                <!-- Reading Recommendations -->
                <div class="bg-gradient-to-br from-primary/5 to-primary/10 rounded-xl border border-primary/20 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <TrendingUp class="w-6 h-6 text-primary" />
                        <h3 class="text-lg font-semibold text-foreground">Recomendaciones para ti</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-3 bg-background/50 rounded-lg border border-border hover:border-primary/30 transition-colors duration-300 cursor-pointer">
                            <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                                <BookOpen class="w-5 h-5 text-primary" />
                            </div>
                            <div>
                                <p class="font-medium text-foreground">Libros similares a tus descargas</p>
                                <p class="text-sm text-muted-foreground">Basado en tu historial de lectura</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-3 p-3 bg-background/50 rounded-lg border border-border hover:border-primary/30 transition-colors duration-300 cursor-pointer">
                            <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                                <Zap class="w-5 h-5 text-primary" />
                            </div>
                            <div>
                                <p class="font-medium text-foreground">Novedades en tu categoría</p>
                                <p class="text-sm text-muted-foreground">Libros agregados recientemente</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reading Stats -->
                <div class="bg-gradient-to-br from-secondary/5 to-secondary/10 rounded-xl border border-secondary/20 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <Library class="w-6 h-6 text-secondary" />
                        <h3 class="text-lg font-semibold text-foreground">Tu Progreso</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-muted-foreground">Libros leídos este mes</span>
                                <span class="text-sm font-medium text-foreground">3/8</span>
                            </div>
                            <div class="w-full bg-border rounded-full h-2">
                                <div class="bg-primary rounded-full h-2" style="width: 37.5%"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-sm text-muted-foreground">Objetivo anual</span>
                                <span class="text-sm font-medium text-foreground">15/50</span>
                            </div>
                            <div class="w-full bg-border rounded-full h-2">
                                <div class="bg-secondary rounded-full h-2" style="width: 30%"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.animate-slide-up {
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.6s ease-out;
}

.animate-slide-up.animate-in {
    opacity: 1;
    transform: translateY(0);
}

/* Custom colors for warning states */
.text-warning {
    color: hsl(33 95% 53%);
}

.bg-warning\/10 {
    background-color: hsl(33 95% 53% / 0.1);
}

.border-warning\/30 {
    border-color: hsl(33 95% 53% / 0.3);
}

.border-warning\/50 {
    border-color: hsl(33 95% 53% / 0.5);
}

.text-success {
    color: hsl(142 76% 36%);
}
</style>