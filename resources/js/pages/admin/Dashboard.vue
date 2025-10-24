<!-- resources/js/pages/admin/Dashboard.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { BookOpen, Users, Download, Clock, BookMarked, Plus, UserPlus, FileText, ArrowRight, TrendingUp, Library, Shield, Zap } from 'lucide-vue-next';
import { ref, onMounted } from 'vue';

defineProps<{
    stats: {
        total_books: number;
        total_users: number;
        total_downloads: number;
        active_loans: number;
        pending_reservations: number;
    };
    userRole: string;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard Administración',
        href: dashboard().url,
    },
];

// Estado para animaciones
const isVisible = ref(false);

onMounted(() => {
    setTimeout(() => {
        isVisible.value = true;
    }, 100);
});
</script>

<template>
    <Head title="Dashboard Administración" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-8">
            <!-- Header -->
            <div class="mb-8 animate-slide-up" :class="{ 'animate-in': isVisible }">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">
                            Dashboard de Administración
                        </h1>
                        <p class="text-muted-foreground mt-2 flex items-center gap-2">
                            <Shield class="w-4 h-4 text-primary" />
                            Bienvenido al panel de control de la biblioteca
                            <span class="font-semibold capitalize text-primary">({{ userRole }})</span>
                        </p>
                    </div>
                    <div class="flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary rounded-lg border border-primary/20">
                        <Zap class="w-4 h-4 animate-pulse" />
                        <span class="text-sm font-medium">Sistema Activo</span>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
                <!-- Total Libros -->
                <div 
                    class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-primary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1 animate-slide-up"
                    :class="{ 'animate-in': isVisible }"
                    style="animation-delay: 0.1s;"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground mb-2">
                                    Total Libros
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.total_books }}
                                </p>
                                <div class="flex items-center gap-1 mt-2 text-xs text-success">
                                    <TrendingUp class="w-3 h-3" />
                                    <span>+12% este mes</span>
                                </div>
                            </div>
                            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <BookOpen class="w-6 h-6 text-primary" />
                            </div>
                        </div>
                    </div>
                    <div class="h-1 bg-gradient-to-r from-primary to-primary/60 w-0 group-hover:w-full transition-all duration-500"></div>
                </div>

                <!-- Total Usuarios -->
                <div 
                    class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-secondary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1 animate-slide-up"
                    :class="{ 'animate-in': isVisible }"
                    style="animation-delay: 0.2s;"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground mb-2">
                                    Total Usuarios
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.total_users }}
                                </p>
                                <div class="flex items-center gap-1 mt-2 text-xs text-success">
                                    <TrendingUp class="w-3 h-3" />
                                    <span>+8% este mes</span>
                                </div>
                            </div>
                            <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <Users class="w-6 h-6 text-secondary" />
                            </div>
                        </div>
                    </div>
                    <div class="h-1 bg-gradient-to-r from-secondary to-secondary/60 w-0 group-hover:w-full transition-all duration-500"></div>
                </div>

                <!-- Total Descargas -->
                <div 
                    class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-primary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1 animate-slide-up"
                    :class="{ 'animate-in': isVisible }"
                    style="animation-delay: 0.3s;"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground mb-2">
                                    Total Descargas
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.total_downloads }}
                                </p>
                                <div class="flex items-center gap-1 mt-2 text-xs text-success">
                                    <TrendingUp class="w-3 h-3" />
                                    <span>+25% este mes</span>
                                </div>
                            </div>
                            <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <Download class="w-6 h-6 text-primary" />
                            </div>
                        </div>
                    </div>
                    <div class="h-1 bg-gradient-to-r from-primary to-primary/60 w-0 group-hover:w-full transition-all duration-500"></div>
                </div>

                <!-- Préstamos Activos -->
                <div 
                    class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-secondary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1 animate-slide-up"
                    :class="{ 'animate-in': isVisible }"
                    style="animation-delay: 0.4s;"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground mb-2">
                                    Préstamos Activos
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.active_loans }}
                                </p>
                                <div class="flex items-center gap-1 mt-2 text-xs text-muted-foreground">
                                    <Clock class="w-3 h-3" />
                                    <span>{{ stats.pending_reservations }} pendientes</span>
                                </div>
                            </div>
                            <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <BookMarked class="w-6 h-6 text-secondary" />
                            </div>
                        </div>
                    </div>
                    <div class="h-1 bg-gradient-to-r from-secondary to-secondary/60 w-0 group-hover:w-full transition-all duration-500"></div>
                </div>
            </div>

            <!-- Quick Actions & Charts Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Quick Actions -->
                <div class="lg:col-span-2">
                    <div class="bg-card shadow-lg rounded-xl border border-border p-6 animate-slide-up" :class="{ 'animate-in': isVisible }" style="animation-delay: 0.5s;">
                        <div class="flex items-center justify-between mb-6">
                            <h2 class="text-xl font-bold text-foreground">Acciones Rápidas</h2>
                            
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <a 
                                href="/admin/books" 
                                class="group relative p-6 bg-background border border-border rounded-xl hover:border-primary/50 hover:shadow-md transition-all duration-300 hover:-translate-y-1 overflow-hidden"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="flex items-center justify-between relative z-10">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                            <Plus class="w-6 h-6 text-primary" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-foreground group-hover:text-primary transition-colors duration-300">Gestionar Libros</h3>
                                            <p class="text-sm text-muted-foreground">Agregar, editar o eliminar libros</p>
                                        </div>
                                    </div>
                                    <ArrowRight class="w-5 h-5 text-muted-foreground group-hover:text-primary group-hover:translate-x-1 transition-all duration-300" />
                                </div>
                            </a>

                            <a 
                                href="/admin/users" 
                                class="group relative p-6 bg-background border border-border rounded-xl hover:border-secondary/50 hover:shadow-md transition-all duration-300 hover:-translate-y-1 overflow-hidden"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-secondary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="flex items-center justify-between relative z-10">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                            <UserPlus class="w-6 h-6 text-secondary" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-foreground group-hover:text-secondary transition-colors duration-300">Gestionar Usuarios</h3>
                                            <p class="text-sm text-muted-foreground">Administrar cuentas de usuarios</p>
                                        </div>
                                    </div>
                                    <ArrowRight class="w-5 h-5 text-muted-foreground group-hover:text-secondary group-hover:translate-x-1 transition-all duration-300" />
                                </div>
                            </a>

                            <a 
                                href="/admin/loans" 
                                class="group relative p-6 bg-background border border-border rounded-xl hover:border-primary/50 hover:shadow-md transition-all duration-300 hover:-translate-y-1 overflow-hidden"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-primary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="flex items-center justify-between relative z-10">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                            <FileText class="w-6 h-6 text-primary" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-foreground group-hover:text-primary transition-colors duration-300">Ver Préstamos</h3>
                                            <p class="text-sm text-muted-foreground">Gestionar sistema de préstamos</p>
                                        </div>
                                    </div>
                                    <ArrowRight class="w-5 h-5 text-muted-foreground group-hover:text-primary group-hover:translate-x-1 transition-all duration-300" />
                                </div>
                            </a>

                            <a 
                                href="/admin/reports" 
                                class="group relative p-6 bg-background border border-border rounded-xl hover:border-secondary/50 hover:shadow-md transition-all duration-300 hover:-translate-y-1 overflow-hidden"
                            >
                                <div class="absolute inset-0 bg-gradient-to-r from-secondary/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                                <div class="flex items-center justify-between relative z-10">
                                    <div class="flex items-center gap-4">
                                        <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                            <TrendingUp class="w-6 h-6 text-secondary" />
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-foreground group-hover:text-secondary transition-colors duration-300">Reportes</h3>
                                            <p class="text-sm text-muted-foreground">Estadísticas y análisis</p>
                                        </div>
                                    </div>
                                    <ArrowRight class="w-5 h-5 text-muted-foreground group-hover:text-secondary group-hover:translate-x-1 transition-all duration-300" />
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="space-y-8">
                    <!-- Recent Activity -->
                    <div class="bg-card shadow-lg rounded-xl border border-border p-6 animate-slide-up" :class="{ 'animate-in': isVisible }" style="animation-delay: 0.7s;">
                        <h3 class="text-lg font-semibold text-foreground mb-4">Actividad Reciente</h3>
                        <div class="space-y-3">
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-accent transition-colors duration-200">
                                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <Download class="w-4 h-4 text-primary" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-foreground">Nueva descarga</p>
                                    <p class="text-xs text-muted-foreground">Hace 5 minutos</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-accent transition-colors duration-200">
                                <div class="w-8 h-8 bg-secondary/10 rounded-lg flex items-center justify-center">
                                    <Users class="w-4 h-4 text-secondary" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-foreground">Usuario registrado</p>
                                    <p class="text-xs text-muted-foreground">Hace 15 minutos</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-2 rounded-lg hover:bg-accent transition-colors duration-200">
                                <div class="w-8 h-8 bg-primary/10 rounded-lg flex items-center justify-center">
                                    <BookOpen class="w-4 h-4 text-primary" />
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-foreground">Libro agregado</p>
                                    <p class="text-xs text-muted-foreground">Hace 1 hora</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Stats -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 animate-slide-up" :class="{ 'animate-in': isVisible }" style="animation-delay: 0.8s;">
                <!-- Library Overview -->
                <div class="bg-gradient-to-br from-primary/5 to-primary/10 rounded-xl border border-primary/20 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <Library class="w-6 h-6 text-primary" />
                        <h3 class="text-lg font-semibold text-foreground">Resumen de Biblioteca</h3>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="text-center p-4 bg-background/50 rounded-lg border border-border">
                            <div class="text-2xl font-bold text-primary">{{ Math.round(stats.total_books * 0.65) }}</div>
                            <div class="text-sm text-muted-foreground">Libros Digitales</div>
                        </div>
                        <div class="text-center p-4 bg-background/50 rounded-lg border border-border">
                            <div class="text-2xl font-bold text-secondary">{{ Math.round(stats.total_books * 0.35) }}</div>
                            <div class="text-sm text-muted-foreground">Libros Físicos</div>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div class="bg-gradient-to-br from-secondary/5 to-secondary/10 rounded-xl border border-secondary/20 p-6">
                    <div class="flex items-center gap-3 mb-4">
                        <Zap class="w-6 h-6 text-secondary" />
                        <h3 class="text-lg font-semibold text-foreground">Métricas de Rendimiento</h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-muted-foreground">Uso del Sistema</span>
                            <span class="text-sm font-medium text-foreground">87%</span>
                        </div>
                        <div class="w-full bg-border rounded-full h-2">
                            <div class="bg-primary rounded-full h-2" style="width: 87%"></div>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-muted-foreground">Satisfacción Usuarios</span>
                            <span class="text-sm font-medium text-foreground">94%</span>
                        </div>
                        <div class="w-full bg-border rounded-full h-2">
                            <div class="bg-secondary rounded-full h-2" style="width: 94%"></div>
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

/* Custom animations for the stats cards */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.animate-fade-in-up {
    animation: fadeInUp 0.6s ease-out forwards;
}

/* Gradient text for highlights */
.gradient-text {
    background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
</style>