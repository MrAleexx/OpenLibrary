<!-- resources/js/pages/user/Dashboard.vue -->
<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowRight,
    BookMarked,
    BookOpen,
    Calendar,
    CheckCircle,
    Download,
    Library,
    RotateCcw,
    Search,
    Shield,
    TrendingUp,
    User,
    Zap,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

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
    return props.recentDownloads.filter((download) => {
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
    return props.activeLoans.filter((loan) => {
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
        <div class="space-y-8 p-6">
            <!-- Header -->
            <div
                class="animate-slide-up mb-8"
                :class="{ 'animate-in': isVisible }"
            >
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">
                            Mi Biblioteca Digital
                        </h1>
                        <p
                            class="mt-2 flex items-center gap-2 text-muted-foreground"
                        >
                            <BookOpen class="h-4 w-4 text-primary" />
                            Bienvenido a tu espacio personal de lectura
                            <span
                                v-if="!emailVerified"
                                class="text-warning ml-2 flex items-center gap-1 text-sm"
                            >
                                <AlertCircle class="h-4 w-4" />
                                (Email no verificado)
                            </span>
                            <span
                                v-else
                                class="text-success ml-2 flex items-center gap-1 text-sm"
                            >
                                <CheckCircle class="h-4 w-4" />
                                Cuenta verificada
                            </span>
                        </p>
                    </div>
                    <div
                        class="flex items-center gap-2 rounded-lg border border-primary/20 bg-primary/10 px-4 py-2 text-primary"
                    >
                        <Zap class="h-4 w-4" />
                        <span class="text-sm font-medium"
                            >Bienvenido, {{ $page.props.auth.user.name }}</span
                        >
                    </div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div
                class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3"
            >
                <!-- Descargas Hoy -->
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
                                    Descargas Hoy
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ downloadsToday }}/5
                                </p>
                                <div
                                    class="mt-2 flex items-center gap-1 text-xs"
                                    :class="
                                        remainingDownloads > 2
                                            ? 'text-success'
                                            : 'text-warning'
                                    "
                                >
                                    <TrendingUp class="h-3 w-3" />
                                    <span
                                        >{{ remainingDownloads }} restantes
                                        hoy</span
                                    >
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
                        class="h-1 bg-gradient-to-r from-primary to-primary/60"
                        :style="{ width: `${(downloadsToday / 5) * 100}%` }"
                    ></div>
                </div>

                <!-- Préstamos Activos -->
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
                                    Préstamos Activos
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ activeLoans.length }}
                                </p>
                                <div
                                    v-if="upcomingDueLoans.length > 0"
                                    class="text-warning mt-2 flex items-center gap-1 text-xs"
                                >
                                    <AlertCircle class="h-3 w-3" />
                                    <span
                                        >{{ upcomingDueLoans.length }} por
                                        vencer</span
                                    >
                                </div>
                                <div
                                    v-else
                                    class="text-success mt-2 flex items-center gap-1 text-xs"
                                >
                                    <CheckCircle class="h-3 w-3" />
                                    <span>Al día</span>
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
                        class="h-1 bg-gradient-to-r from-secondary to-secondary/60"
                        :style="{
                            width: `${(activeLoans.length / $page.props.auth.user.max_concurrent_loans) * 100}%`,
                        }"
                    ></div>
                </div>

                <!-- Límite Préstamos -->
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
                                    Límite Préstamos
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ activeLoans.length }}/{{
                                        $page.props.auth.user
                                            .max_concurrent_loans
                                    }}
                                </p>
                                <div
                                    class="mt-2 flex items-center gap-1 text-xs text-muted-foreground"
                                >
                                    <Shield class="h-3 w-3" />
                                    <span
                                        >{{
                                            $page.props.auth.user
                                                .max_concurrent_loans -
                                            activeLoans.length
                                        }}
                                        disponibles</span
                                    >
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <Library class="h-6 w-6 text-primary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 bg-gradient-to-r from-primary to-primary/60"
                        :style="{
                            width: `${(activeLoans.length / $page.props.auth.user.max_concurrent_loans) * 100}%`,
                        }"
                    ></div>
                </div>
            </div>

            <!-- Two Column Layout -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                <!-- Descargas Recientes -->
                <div
                    class="animate-slide-up rounded-xl border border-border bg-card p-6 shadow-lg"
                    :class="{ 'animate-in': isVisible }"
                    style="animation-delay: 0.4s"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-foreground">
                            Descargas Recientes
                        </h2>
                        <div
                            class="h-2 w-2 animate-pulse rounded-full bg-primary"
                        ></div>
                    </div>
                    <div v-if="recentDownloads.length > 0" class="space-y-4">
                        <div
                            v-for="download in recentDownloads.slice(0, 5)"
                            :key="download.id"
                            class="group flex items-center justify-between rounded-xl border border-border bg-background p-4 transition-all duration-300 hover:-translate-y-1 hover:border-primary/30 hover:shadow-md"
                        >
                            <div class="flex flex-1 items-center gap-4">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                                >
                                    <Download class="h-5 w-5 text-primary" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3
                                        class="truncate font-semibold text-foreground"
                                    >
                                        {{ download.book.title }}
                                    </h3>
                                    <p
                                        class="truncate text-sm text-muted-foreground"
                                        v-if="download.book.author"
                                    >
                                        {{ download.book.author }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Descargado el
                                        {{
                                            new Date(
                                                download.downloaded_at,
                                            ).toLocaleDateString('es-ES', {
                                                day: 'numeric',
                                                month: 'long',
                                                year: 'numeric',
                                            })
                                        }}
                                    </p>
                                </div>
                            </div>
                            <button
                                class="flex items-center gap-1 text-sm font-medium text-primary transition-all duration-300 group-hover:gap-2 hover:text-primary/80"
                            >
                                <Download class="h-4 w-4" />
                                <span class="hidden sm:inline">Descargar</span>
                            </button>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center">
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted"
                        >
                            <Download class="h-8 w-8 text-muted-foreground" />
                        </div>
                        <p class="mb-4 text-muted-foreground">
                            Aún no has descargado ningún libro
                        </p>
                        <a
                            href="/books"
                            class="group inline-flex items-center gap-2 font-medium text-primary transition-colors duration-300 hover:text-primary/80"
                        >
                            Explorar catálogo
                            <ArrowRight
                                class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                            />
                        </a>
                    </div>
                    <div
                        v-if="recentDownloads.length > 0"
                        class="mt-4 border-t border-border pt-4"
                    >
                        <a
                            href="/downloads"
                            class="group flex items-center justify-center gap-2 text-sm font-medium text-primary transition-colors duration-300 hover:text-primary/80"
                        >
                            Ver todas las descargas
                            <ArrowRight
                                class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                            />
                        </a>
                    </div>
                </div>

                <!-- Préstamos Activos -->
                <div
                    class="animate-slide-up rounded-xl border border-border bg-card p-6 shadow-lg"
                    :class="{ 'animate-in': isVisible }"
                    style="animation-delay: 0.5s"
                >
                    <div class="mb-6 flex items-center justify-between">
                        <h2 class="text-xl font-bold text-foreground">
                            Mis Préstamos Activos
                        </h2>
                        <div
                            class="h-2 w-2 animate-pulse rounded-full bg-secondary"
                        ></div>
                    </div>
                    <div v-if="activeLoans.length > 0" class="space-y-4">
                        <div
                            v-for="loan in activeLoans"
                            :key="loan.id"
                            class="group flex items-center justify-between rounded-xl border border-border bg-background p-4 transition-all duration-300 hover:-translate-y-1 hover:border-secondary/30 hover:shadow-md"
                            :class="{
                                'border-warning/30 hover:border-warning/50':
                                    upcomingDueLoans.some(
                                        (l) => l.id === loan.id,
                                    ),
                            }"
                        >
                            <div class="flex flex-1 items-center gap-4">
                                <div
                                    class="flex h-10 w-10 items-center justify-center rounded-lg bg-secondary/10 transition-transform duration-300 group-hover:scale-110"
                                    :class="{
                                        'bg-warning/10': upcomingDueLoans.some(
                                            (l) => l.id === loan.id,
                                        ),
                                    }"
                                >
                                    <BookMarked
                                        class="h-5 w-5 text-secondary"
                                        :class="{
                                            'text-warning':
                                                upcomingDueLoans.some(
                                                    (l) => l.id === loan.id,
                                                ),
                                        }"
                                    />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h3
                                        class="truncate font-semibold text-foreground"
                                    >
                                        {{ loan.physical_copy.book.title }}
                                    </h3>
                                    <p
                                        class="truncate text-sm text-muted-foreground"
                                        v-if="loan.physical_copy.book.author"
                                    >
                                        {{ loan.physical_copy.book.author }}
                                    </p>
                                    <div class="mt-1 flex items-center gap-2">
                                        <Calendar
                                            class="h-3 w-3 text-muted-foreground"
                                        />
                                        <p
                                            class="text-xs"
                                            :class="
                                                upcomingDueLoans.some(
                                                    (l) => l.id === loan.id,
                                                )
                                                    ? 'text-warning font-medium'
                                                    : 'text-muted-foreground'
                                            "
                                        >
                                            Vence el
                                            {{
                                                new Date(
                                                    loan.due_date,
                                                ).toLocaleDateString('es-ES', {
                                                    day: 'numeric',
                                                    month: 'long',
                                                    year: 'numeric',
                                                })
                                            }}
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <button
                                class="flex items-center gap-1 text-sm font-medium text-secondary transition-all duration-300 group-hover:gap-2 hover:text-secondary/80"
                                :class="{
                                    'text-warning hover:text-warning/80':
                                        upcomingDueLoans.some(
                                            (l) => l.id === loan.id,
                                        ),
                                }"
                            >
                                <RotateCcw class="h-4 w-4" />
                                <span class="hidden sm:inline">Renovar</span>
                            </button>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center">
                        <div
                            class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-muted"
                        >
                            <BookMarked class="h-8 w-8 text-muted-foreground" />
                        </div>
                        <p class="mb-4 text-muted-foreground">
                            No tienes préstamos activos
                        </p>
                        <a
                            href="/books"
                            class="group inline-flex items-center gap-2 font-medium text-secondary transition-colors duration-300 hover:text-secondary/80"
                        >
                            Buscar libros físicos
                            <ArrowRight
                                class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                            />
                        </a>
                    </div>
                    <div
                        v-if="activeLoans.length > 0"
                        class="mt-4 border-t border-border pt-4"
                    >
                        <a
                            href="/loans"
                            class="group flex items-center justify-center gap-2 text-sm font-medium text-secondary transition-colors duration-300 hover:text-secondary/80"
                        >
                            Ver todos mis préstamos
                            <ArrowRight
                                class="h-4 w-4 transition-transform duration-300 group-hover:translate-x-1"
                            />
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div
                class="animate-slide-up rounded-xl border border-border bg-card p-6 shadow-lg"
                :class="{ 'animate-in': isVisible }"
                style="animation-delay: 0.6s"
            >
                <h2 class="mb-6 text-xl font-bold text-foreground">
                    Acciones Rápidas
                </h2>
                <div
                    class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-5"
                >
                    <a
                        href="/books"
                        class="group relative overflow-hidden rounded-xl border border-border bg-background p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-md"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        ></div>
                        <div class="relative z-10">
                            <div
                                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <Search class="h-6 w-6 text-primary" />
                            </div>
                            <h3
                                class="font-semibold text-foreground transition-colors duration-300 group-hover:text-primary"
                            >
                                Explorar Catálogo
                            </h3>
                        </div>
                    </a>

                    <a
                        href="/profile"
                        class="group relative overflow-hidden rounded-xl border border-border bg-background p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:border-secondary/50 hover:shadow-md"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-secondary/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        ></div>
                        <div class="relative z-10">
                            <div
                                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <User class="h-6 w-6 text-secondary" />
                            </div>
                            <h3
                                class="font-semibold text-foreground transition-colors duration-300 group-hover:text-secondary"
                            >
                                Mi Perfil
                            </h3>
                        </div>
                    </a>

                    <a
                        href="/downloads"
                        class="group relative overflow-hidden rounded-xl border border-border bg-background p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-md"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        ></div>
                        <div class="relative z-10">
                            <div
                                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <Download class="h-6 w-6 text-primary" />
                            </div>
                            <h3
                                class="font-semibold text-foreground transition-colors duration-300 group-hover:text-primary"
                            >
                                Mis Descargas
                            </h3>
                        </div>
                    </a>

                    <a
                        href="/loans"
                        class="group relative overflow-hidden rounded-xl border border-border bg-background p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:border-secondary/50 hover:shadow-md"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-secondary/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        ></div>
                        <div class="relative z-10">
                            <div
                                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <BookMarked class="h-6 w-6 text-secondary" />
                            </div>
                            <h3
                                class="font-semibold text-foreground transition-colors duration-300 group-hover:text-secondary"
                            >
                                Mis Préstamos
                            </h3>
                        </div>
                    </a>

                    <a
                        href="/reservations"
                        class="group relative overflow-hidden rounded-xl border border-border bg-background p-6 text-center transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-md"
                    >
                        <div
                            class="absolute inset-0 bg-gradient-to-br from-primary/5 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100"
                        ></div>
                        <div class="relative z-10">
                            <div
                                class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <Calendar class="h-6 w-6 text-primary" />
                            </div>
                            <h3
                                class="font-semibold text-foreground transition-colors duration-300 group-hover:text-primary"
                            >
                                Mis Reservas
                            </h3>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Reading Progress -->
            <div
                class="animate-slide-up grid grid-cols-1 gap-8 lg:grid-cols-2"
                :class="{ 'animate-in': isVisible }"
                style="animation-delay: 0.7s"
            >
                <!-- Reading Recommendations -->
                <div
                    class="rounded-xl border border-primary/20 bg-gradient-to-br from-primary/5 to-primary/10 p-6"
                >
                    <div class="mb-4 flex items-center gap-3">
                        <TrendingUp class="h-6 w-6 text-primary" />
                        <h3 class="text-lg font-semibold text-foreground">
                            Recomendaciones para ti
                        </h3>
                    </div>
                    <div class="space-y-3">
                        <div
                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-border bg-background/50 p-3 transition-colors duration-300 hover:border-primary/30"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                            >
                                <BookOpen class="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <p class="font-medium text-foreground">
                                    Libros similares a tus descargas
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    Basado en tu historial de lectura
                                </p>
                            </div>
                        </div>
                        <div
                            class="flex cursor-pointer items-center gap-3 rounded-lg border border-border bg-background/50 p-3 transition-colors duration-300 hover:border-primary/30"
                        >
                            <div
                                class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                            >
                                <Zap class="h-5 w-5 text-primary" />
                            </div>
                            <div>
                                <p class="font-medium text-foreground">
                                    Novedades en tu categoría
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    Libros agregados recientemente
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reading Stats -->
                <div
                    class="rounded-xl border border-secondary/20 bg-gradient-to-br from-secondary/5 to-secondary/10 p-6"
                >
                    <div class="mb-4 flex items-center gap-3">
                        <Library class="h-6 w-6 text-secondary" />
                        <h3 class="text-lg font-semibold text-foreground">
                            Tu Progreso
                        </h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm text-muted-foreground"
                                    >Libros leídos este mes</span
                                >
                                <span
                                    class="text-sm font-medium text-foreground"
                                    >3/8</span
                                >
                            </div>
                            <div class="h-2 w-full rounded-full bg-border">
                                <div
                                    class="h-2 rounded-full bg-primary"
                                    style="width: 37.5%"
                                ></div>
                            </div>
                        </div>
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <span class="text-sm text-muted-foreground"
                                    >Objetivo anual</span
                                >
                                <span
                                    class="text-sm font-medium text-foreground"
                                    >15/50</span
                                >
                            </div>
                            <div class="h-2 w-full rounded-full bg-border">
                                <div
                                    class="h-2 rounded-full bg-secondary"
                                    style="width: 30%"
                                ></div>
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
