<!-- resources/js/pages/admin/Dashboard.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {
    ArrowRight,
    BookMarked,
    BookOpen,
    Clock,
    Download,
    FileText,
    Library,
    Plus,
    Shield,
    TrendingUp,
    UserPlus,
    Users,
    Zap,
} from 'lucide-vue-next';
import { onMounted, ref } from 'vue';

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
        <div class="space-y-8 p-6">
            <!-- Header -->
            <div
                class="animate-slide-up mb-8"
                :class="{ 'animate-in': isVisible }"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">
                            Dashboard de Administración
                        </h1>
                        <p
                            class="mt-2 flex items-center gap-2 text-muted-foreground"
                        >
                            <Shield class="h-4 w-4 text-primary" />
                            Bienvenido al panel de control de la biblioteca
                            <span class="font-semibold text-primary capitalize"
                                >({{ userRole }})</span
                            >
                        </p>
                    </div>
                    <div
                        class="flex items-center gap-2 rounded-lg border border-primary/20 bg-primary/10 px-4 py-2 text-primary"
                    >
                        <Zap class="h-4 w-4 animate-pulse" />
                        <span class="text-sm font-medium">Sistema Activo</span>
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div
                class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
            >
                <!-- Total Libros -->
                <div
                    class="group animate-slide-up overflow-hidden rounded-xl border border-border bg-card shadow-lg transition-all duration-500 hover:-translate-y-1 hover:border-primary/30 hover:shadow-xl"
                    :class="{ 'animate-in': isVisible }"
                    style="animation-delay: 0.1s"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="mb-2 text-sm font-medium text-muted-foreground"
                                >
                                    Total Libros
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.total_books }}
                                </p>
                                <div
                                    class="text-success mt-2 flex items-center gap-1 text-xs"
                                >
                                    <TrendingUp class="h-3 w-3" />
                                    <span>+12% este mes</span>
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <BookOpen class="h-6 w-6 text-primary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 w-0 bg-gradient-to-r from-primary to-primary/60 transition-all duration-500 group-hover:w-full"
                    ></div>
                </div>

                <!-- Total Usuarios -->
                <div
                    class="group animate-slide-up overflow-hidden rounded-xl border border-border bg-card shadow-lg transition-all duration-500 hover:-translate-y-1 hover:border-secondary/30 hover:shadow-xl"
                    :class="{ 'animate-in': isVisible }"
                    style="animation-delay: 0.2s"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="mb-2 text-sm font-medium text-muted-foreground"
                                >
                                    Total Usuarios
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.total_users }}
                                </p>
                                <div
                                    class="text-success mt-2 flex items-center gap-1 text-xs"
                                >
                                    <TrendingUp class="h-3 w-3" />
                                    <span>+8% este mes</span>
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <Users class="h-6 w-6 text-secondary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 w-0 bg-gradient-to-r from-secondary to-secondary/60 transition-all duration-500 group-hover:w-full"
                    ></div>
                </div>

                <!-- Total Descargas -->
                <div
                    class="group animate-slide-up overflow-hidden rounded-xl border border-border bg-card shadow-lg transition-all duration-500 hover:-translate-y-1 hover:border-primary/30 hover:shadow-xl"
                    :class="{ 'animate-in': isVisible }"
                    style="animation-delay: 0.3s"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="mb-2 text-sm font-medium text-muted-foreground"
                                >
                                    Total Descargas
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.total_downloads }}
                                </p>
                                <div
                                    class="text-success mt-2 flex items-center gap-1 text-xs"
                                >
                                    <TrendingUp class="h-3 w-3" />
                                    <span>+25% este mes</span>
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <Download class="h-6 w-6 text-primary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 w-0 bg-gradient-to-r from-primary to-primary/60 transition-all duration-500 group-hover:w-full"
                    ></div>
                </div>

                <!-- Préstamos Activos -->
                <div
                    class="group animate-slide-up overflow-hidden rounded-xl border border-border bg-card shadow-lg transition-all duration-500 hover:-translate-y-1 hover:border-secondary/30 hover:shadow-xl"
                    :class="{ 'animate-in': isVisible }"
                    style="animation-delay: 0.4s"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="mb-2 text-sm font-medium text-muted-foreground"
                                >
                                    Préstamos Activos
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.active_loans }}
                                </p>
                                <div
                                    class="mt-2 flex items-center gap-1 text-xs text-muted-foreground"
                                >
                                    <Clock class="h-3 w-3" />
                                    <span
                                        >{{
                                            stats.pending_reservations
                                        }}
                                        pendientes</span
                                    >
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <BookMarked class="h-6 w-6 text-secondary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 w-0 bg-gradient-to-r from-secondary to-secondary/60 transition-all duration-500 group-hover:w-full"
                    ></div>
                </div>
            </div>

            <!-- Quick Actions & Charts Grid -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Quick Actions -->
                <div class="lg:col-span-2">
                    <div
                        class="animate-slide-up rounded-xl border border-border bg-card p-6 shadow-lg"
                        :class="{ 'animate-in': isVisible }"
                        style="animation-delay: 0.5s"
                    >
                        <div class="mb-6 flex items-center justify-between">
                            <h2 class="text-xl font-bold text-foreground">
                                Acciones Rápidas
                            </h2>
                        </div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <a
                                href="/admin/books"
                                class="group relative overflow-hidden rounded-xl border border-border bg-background p-6 transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-md"
                            >
                                <div
                                    class="absolute inset-0 bg-gradient-to-r from-primary/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                ></div>
                                <div
                                    class="relative z-10 flex items-center justify-between"
                                >
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                                        >
                                            <Plus
                                                class="h-6 w-6 text-primary"
                                            />
                                        </div>
                                        <div>
                                            <h3
                                                class="font-semibold text-foreground transition-colors duration-300 group-hover:text-primary"
                                            >
                                                Gestionar Libros
                                            </h3>
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                Agregar, editar o eliminar
                                                libros
                                            </p>
                                        </div>
                                    </div>
                                    <ArrowRight
                                        class="h-5 w-5 text-muted-foreground transition-all duration-300 group-hover:translate-x-1 group-hover:text-primary"
                                    />
                                </div>
                            </a>

                            <a
                                href="/admin/users"
                                class="group relative overflow-hidden rounded-xl border border-border bg-background p-6 transition-all duration-300 hover:-translate-y-1 hover:border-secondary/50 hover:shadow-md"
                            >
                                <div
                                    class="absolute inset-0 bg-gradient-to-r from-secondary/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                ></div>
                                <div
                                    class="relative z-10 flex items-center justify-between"
                                >
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 transition-transform duration-300 group-hover:scale-110"
                                        >
                                            <UserPlus
                                                class="h-6 w-6 text-secondary"
                                            />
                                        </div>
                                        <div>
                                            <h3
                                                class="font-semibold text-foreground transition-colors duration-300 group-hover:text-secondary"
                                            >
                                                Gestionar Usuarios
                                            </h3>
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                Administrar cuentas de usuarios
                                            </p>
                                        </div>
                                    </div>
                                    <ArrowRight
                                        class="h-5 w-5 text-muted-foreground transition-all duration-300 group-hover:translate-x-1 group-hover:text-secondary"
                                    />
                                </div>
                            </a>

                            <a
                                href="/admin/loans"
                                class="group relative overflow-hidden rounded-xl border border-border bg-background p-6 transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-md"
                            >
                                <div
                                    class="absolute inset-0 bg-gradient-to-r from-primary/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                ></div>
                                <div
                                    class="relative z-10 flex items-center justify-between"
                                >
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                                        >
                                            <FileText
                                                class="h-6 w-6 text-primary"
                                            />
                                        </div>
                                        <div>
                                            <h3
                                                class="font-semibold text-foreground transition-colors duration-300 group-hover:text-primary"
                                            >
                                                Ver Préstamos
                                            </h3>
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                Gestionar sistema de préstamos
                                            </p>
                                        </div>
                                    </div>
                                    <ArrowRight
                                        class="h-5 w-5 text-muted-foreground transition-all duration-300 group-hover:translate-x-1 group-hover:text-primary"
                                    />
                                </div>
                            </a>

                            <a
                                href="/admin/reports"
                                class="group relative overflow-hidden rounded-xl border border-border bg-background p-6 transition-all duration-300 hover:-translate-y-1 hover:border-secondary/50 hover:shadow-md"
                            >
                                <div
                                    class="absolute inset-0 bg-gradient-to-r from-secondary/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                                ></div>
                                <div
                                    class="relative z-10 flex items-center justify-between"
                                >
                                    <div class="flex items-center gap-4">
                                        <div
                                            class="flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 transition-transform duration-300 group-hover:scale-110"
                                        >
                                            <TrendingUp
                                                class="h-6 w-6 text-secondary"
                                            />
                                        </div>
                                        <div>
                                            <h3
                                                class="font-semibold text-foreground transition-colors duration-300 group-hover:text-secondary"
                                            >
                                                Reportes
                                            </h3>
                                            <p
                                                class="text-sm text-muted-foreground"
                                            >
                                                Estadísticas y análisis
                                            </p>
                                        </div>
                                    </div>
                                    <ArrowRight
                                        class="h-5 w-5 text-muted-foreground transition-all duration-300 group-hover:translate-x-1 group-hover:text-secondary"
                                    />
                                </div>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- System Status -->
                <div class="space-y-8">
                    <!-- Recent Activity -->
                    <div
                        class="animate-slide-up rounded-xl border border-border bg-card p-6 shadow-lg"
                        :class="{ 'animate-in': isVisible }"
                        style="animation-delay: 0.7s"
                    >
                        <h3 class="mb-4 text-lg font-semibold text-foreground">
                            Actividad Reciente
                        </h3>
                        <div class="space-y-3">
                            <div
                                class="flex items-center gap-3 rounded-lg p-2 transition-colors duration-200 hover:bg-accent"
                            >
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10"
                                >
                                    <Download class="h-4 w-4 text-primary" />
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="text-sm font-medium text-foreground"
                                    >
                                        Nueva descarga
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Hace 5 minutos
                                    </p>
                                </div>
                            </div>
                            <div
                                class="flex items-center gap-3 rounded-lg p-2 transition-colors duration-200 hover:bg-accent"
                            >
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-secondary/10"
                                >
                                    <Users class="h-4 w-4 text-secondary" />
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="text-sm font-medium text-foreground"
                                    >
                                        Usuario registrado
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Hace 15 minutos
                                    </p>
                                </div>
                            </div>
                            <div
                                class="flex items-center gap-3 rounded-lg p-2 transition-colors duration-200 hover:bg-accent"
                            >
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10"
                                >
                                    <BookOpen class="h-4 w-4 text-primary" />
                                </div>
                                <div class="flex-1">
                                    <p
                                        class="text-sm font-medium text-foreground"
                                    >
                                        Libro agregado
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Hace 1 hora
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Stats -->
            <div
                class="animate-slide-up grid grid-cols-1 gap-8 md:grid-cols-2"
                :class="{ 'animate-in': isVisible }"
                style="animation-delay: 0.8s"
            >
                <!-- Library Overview -->
                <div
                    class="rounded-xl border border-primary/20 bg-gradient-to-br from-primary/5 to-primary/10 p-6"
                >
                    <div class="mb-4 flex items-center gap-3">
                        <Library class="h-6 w-6 text-primary" />
                        <h3 class="text-lg font-semibold text-foreground">
                            Resumen de Biblioteca
                        </h3>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div
                            class="rounded-lg border border-border bg-background/50 p-4 text-center"
                        >
                            <div class="text-2xl font-bold text-primary">
                                {{ Math.round(stats.total_books * 0.65) }}
                            </div>
                            <div class="text-sm text-muted-foreground">
                                Libros Digitales
                            </div>
                        </div>
                        <div
                            class="rounded-lg border border-border bg-background/50 p-4 text-center"
                        >
                            <div class="text-2xl font-bold text-secondary">
                                {{ Math.round(stats.total_books * 0.35) }}
                            </div>
                            <div class="text-sm text-muted-foreground">
                                Libros Físicos
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Performance Metrics -->
                <div
                    class="rounded-xl border border-secondary/20 bg-gradient-to-br from-secondary/5 to-secondary/10 p-6"
                >
                    <div class="mb-4 flex items-center gap-3">
                        <Zap class="h-6 w-6 text-secondary" />
                        <h3 class="text-lg font-semibold text-foreground">
                            Métricas de Rendimiento
                        </h3>
                    </div>
                    <div class="space-y-3">
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground"
                                >Uso del Sistema</span
                            >
                            <span class="text-sm font-medium text-foreground"
                                >87%</span
                            >
                        </div>
                        <div class="h-2 w-full rounded-full bg-border">
                            <div
                                class="h-2 rounded-full bg-primary"
                                style="width: 87%"
                            ></div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm text-muted-foreground"
                                >Satisfacción Usuarios</span
                            >
                            <span class="text-sm font-medium text-foreground"
                                >94%</span
                            >
                        </div>
                        <div class="h-2 w-full rounded-full bg-border">
                            <div
                                class="h-2 rounded-full bg-secondary"
                                style="width: 94%"
                            ></div>
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
    background: linear-gradient(
        135deg,
        var(--primary) 0%,
        var(--secondary) 100%
    );
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}
</style>
