<!-- resources/js/pages/Loans/Index.vue -->
<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import {
    AlertCircle,
    BookOpen,
    Calendar,
    CheckCircle,
    Clock,
    XCircle,
} from 'lucide-vue-next';
import { computed } from 'vue';

interface PhysicalCopy {
    id: number;
    barcode: string;
    book: {
        id: number;
        title: string;
        cover_image?: string;
    };
}

interface BookLoan {
    id: number;
    physical_copy: PhysicalCopy;
    loan_date: string | null;
    due_date: string | null;
    actual_return_date: string | null;
    status:
        | 'pending_pickup'
        | 'ready_for_pickup'
        | 'active'
        | 'overdue'
        | 'returned'
        | 'cancelled';
    renewal_count: number;
    notes: string | null;
    created_at: string;
    updated_at?: string;
}

interface Props {
    activeLoans: BookLoan[];
    loanHistory: BookLoan[];
    stats: {
        total_loans: number;
        active_loans: number;
        overdue_loans: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Mis Préstamos', href: '#' },
];

// Computed
const hasActiveLoans = computed(() => props.activeLoans.length > 0);
const hasOverdueLoans = computed(() => props.stats.overdue_loans > 0);
const readyForPickupLoans = computed(() =>
    props.activeLoans.filter((loan) => loan.status === 'ready_for_pickup'),
);
const hasReadyForPickup = computed(() => readyForPickupLoans.value.length > 0);

// Helpers
function formatDate(date: string): string {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

function getDaysRemaining(dueDate: string | null): number | null {
    if (!dueDate) return null;

    const due = new Date(dueDate);
    const today = new Date();
    const diffTime = due.getTime() - today.getTime();
    return Math.ceil(diffTime / (1000 * 60 * 60 * 24));
}

function getLoanStatusBadge(loan: BookLoan) {
    // Estados pendientes (sin fecha de préstamo aún)
    if (loan.status === 'pending_pickup') {
        return {
            variant: 'outline' as const,
            class: 'bg-yellow-500/10 text-yellow-600 border-yellow-200 dark:bg-yellow-500/20 dark:text-yellow-400 dark:border-yellow-800',
            label: 'Preparando tu libro',
            icon: Clock,
        };
    } else if (loan.status === 'ready_for_pickup') {
        return {
            variant: 'outline' as const,
            class: 'bg-cyan-500/10 text-cyan-600 border-cyan-200 dark:bg-cyan-500/20 dark:text-cyan-400 dark:border-cyan-800 animate-pulse',
            label: '¡Tu libro está listo!',
            icon: CheckCircle,
        };
    } else if (loan.status === 'returned') {
        return {
            variant: 'outline' as const,
            class: 'bg-green-500/10 text-green-600 border-green-200 dark:bg-green-500/20 dark:text-green-400 dark:border-green-800',
            label: 'Devuelto',
            icon: CheckCircle,
        };
    } else if (loan.status === 'cancelled') {
        return {
            variant: 'outline' as const,
            class: 'bg-gray-500/10 text-gray-600 border-gray-200 dark:bg-gray-500/20 dark:text-gray-400 dark:border-gray-800',
            label: 'Cancelado',
            icon: XCircle,
        };
    } else if (loan.status === 'overdue') {
        return {
            variant: 'destructive' as const,
            class: 'bg-red-500/10 text-red-600 border-red-200 dark:bg-red-500/20 dark:text-red-400 dark:border-red-800',
            label: 'Vencido',
            icon: XCircle,
        };
    } else {
        // Status: active
        if (!loan.due_date) {
            return {
                variant: 'outline' as const,
                class: 'bg-blue-500/10 text-blue-600 border-blue-200 dark:bg-blue-500/20 dark:text-blue-400 dark:border-blue-800',
                label: 'Activo',
                icon: Clock,
            };
        }

        const daysLeft = getDaysRemaining(loan.due_date);
        if (daysLeft !== null && daysLeft <= 3) {
            return {
                variant: 'outline' as const,
                class: 'bg-orange-500/10 text-orange-600 border-orange-200 dark:bg-orange-500/20 dark:text-orange-400 dark:border-orange-800',
                label: 'Por vencer',
                icon: AlertCircle,
            };
        }
        return {
            variant: 'outline' as const,
            class: 'bg-blue-500/10 text-blue-600 border-blue-200 dark:bg-blue-500/20 dark:text-blue-400 dark:border-blue-800',
            label: 'Activo',
            icon: Clock,
        };
    }
}

function getBookCover(loan: BookLoan): string {
    return (
        loan.physical_copy.book.cover_image || '/images/default-book-cover.jpg'
    );
}
</script>

<template>
    <AppLayout>
        <Head title="Mis Préstamos" />

        <div class="container mx-auto px-4 py-6 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-3xl font-bold tracking-tight">Mis Préstamos</h1>
                <p class="mt-2 text-muted-foreground">
                    Gestiona y revisa el estado de tus préstamos de libros
                </p>
            </div>

            <!-- Stats Cards -->
            <div class="mb-6 grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Total Préstamos</CardTitle
                        >
                        <BookOpen class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.total_loans }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Préstamos históricos
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Préstamos Activos</CardTitle
                        >
                        <Clock class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.active_loans }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Libros en tu poder
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between space-y-0 pb-2"
                    >
                        <CardTitle class="text-sm font-medium"
                            >Préstamos Vencidos</CardTitle
                        >
                        <AlertCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div
                            class="text-2xl font-bold"
                            :class="hasOverdueLoans ? 'text-red-600' : ''"
                        >
                            {{ stats.overdue_loans }}
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Requieren devolución urgente
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Ready for Pickup Alert -->
            <Alert
                v-if="hasReadyForPickup"
                class="mb-6 border-cyan-200 bg-cyan-50 dark:border-cyan-800 dark:bg-cyan-950"
            >
                <CheckCircle class="h-4 w-4 text-cyan-600" />
                <AlertDescription class="text-cyan-900 dark:text-cyan-100">
                    <span class="font-semibold"
                        >¡Tienes {{ readyForPickupLoans.length }} libro(s)
                        esperándote!</span
                    >
                    <span class="ml-1"
                        >Tu(s) libro(s) ha(n) sido apartado(s) y está(n)
                        listo(s) para recoger en biblioteca.</span
                    >
                </AlertDescription>
            </Alert>

            <!-- Overdue Alert -->
            <Alert v-if="hasOverdueLoans" variant="destructive" class="mb-6">
                <AlertCircle class="h-4 w-4" />
                <AlertDescription>
                    Tienes {{ stats.overdue_loans }} préstamo(s) vencido(s). Por
                    favor, devuelve los libros lo antes posible.
                </AlertDescription>
            </Alert>

            <!-- Active Loans -->
            <Card class="mb-6">
                <CardHeader>
                    <CardTitle>Préstamos Activos</CardTitle>
                    <CardDescription>
                        Libros que actualmente tienes en préstamo
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="hasActiveLoans" class="space-y-4">
                        <div
                            v-for="loan in activeLoans"
                            :key="loan.id"
                            class="flex flex-col gap-4 rounded-lg border p-4 transition-colors hover:bg-accent/50 sm:flex-row"
                        >
                            <!-- Book Cover -->
                            <div class="flex-shrink-0">
                                <img
                                    :src="getBookCover(loan)"
                                    :alt="loan.physical_copy.book.title"
                                    class="h-28 w-20 rounded-md object-cover shadow-sm"
                                />
                            </div>

                            <!-- Loan Info -->
                            <div class="min-w-0 flex-1">
                                <div
                                    class="mb-2 flex items-start justify-between gap-2"
                                >
                                    <div class="flex-1">
                                        <Link
                                            :href="`/books/${loan.physical_copy.book.id}`"
                                            class="line-clamp-2 text-lg font-semibold hover:underline"
                                        >
                                            {{ loan.physical_copy.book.title }}
                                        </Link>
                                        <p
                                            class="text-sm text-muted-foreground"
                                        >
                                            Código:
                                            {{ loan.physical_copy.barcode }}
                                        </p>
                                    </div>
                                    <Badge
                                        :class="getLoanStatusBadge(loan).class"
                                    >
                                        <component
                                            :is="getLoanStatusBadge(loan).icon"
                                            class="mr-1 h-3 w-3"
                                        />
                                        {{ getLoanStatusBadge(loan).label }}
                                    </Badge>
                                </div>

                                <!-- Dates -->
                                <div
                                    class="grid grid-cols-1 gap-2 text-sm sm:grid-cols-2"
                                >
                                    <!-- Fecha de préstamo (solo si existe) -->
                                    <div
                                        v-if="loan.loan_date"
                                        class="flex items-center gap-2 text-muted-foreground"
                                    >
                                        <Calendar class="h-4 w-4" />
                                        <span
                                            >Prestado:
                                            {{
                                                formatDate(loan.loan_date)
                                            }}</span
                                        >
                                    </div>
                                    <div
                                        v-else
                                        class="flex items-center gap-2 text-muted-foreground"
                                    >
                                        <Clock class="h-4 w-4" />
                                        <span
                                            >Solicitado:
                                            {{
                                                formatDate(loan.created_at)
                                            }}</span
                                        >
                                    </div>

                                    <!-- Fecha de vencimiento (solo si existe) -->
                                    <div
                                        v-if="loan.due_date"
                                        class="flex items-center gap-2"
                                    >
                                        <Calendar
                                            class="h-4 w-4"
                                            :class="
                                                loan.status === 'overdue'
                                                    ? 'text-red-600'
                                                    : 'text-muted-foreground'
                                            "
                                        />
                                        <span
                                            :class="
                                                loan.status === 'overdue'
                                                    ? 'font-semibold text-red-600'
                                                    : 'text-muted-foreground'
                                            "
                                        >
                                            Vence:
                                            {{ formatDate(loan.due_date) }}
                                            <span
                                                v-if="
                                                    loan.status !== 'overdue' &&
                                                    getDaysRemaining(
                                                        loan.due_date,
                                                    ) !== null
                                                "
                                                class="ml-1"
                                            >
                                                ({{
                                                    getDaysRemaining(
                                                        loan.due_date,
                                                    )
                                                }}
                                                días)
                                            </span>
                                        </span>
                                    </div>
                                    <div
                                        v-else-if="
                                            loan.status === 'pending_pickup'
                                        "
                                        class="flex items-center gap-2 text-amber-600"
                                    >
                                        <AlertCircle class="h-4 w-4" />
                                        <span class="font-medium"
                                            >En preparación - Te avisaremos
                                            cuando esté listo</span
                                        >
                                    </div>
                                    <div
                                        v-else-if="
                                            loan.status === 'ready_for_pickup'
                                        "
                                        class="flex animate-pulse items-center gap-2 text-cyan-600"
                                    >
                                        <CheckCircle class="h-4 w-4" />
                                        <span class="font-bold"
                                            >¡Tu libro te está esperando!
                                            Recógelo en biblioteca</span
                                        >
                                    </div>
                                </div>

                                <!-- Renewals -->
                                <div
                                    v-if="loan.renewal_count > 0"
                                    class="mt-2 text-sm text-muted-foreground"
                                >
                                    Renovaciones: {{ loan.renewal_count }}
                                </div>

                                <!-- Notes -->
                                <div
                                    v-if="loan.notes"
                                    class="mt-2 text-sm text-muted-foreground italic"
                                >
                                    {{ loan.notes }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div v-else class="py-12 text-center">
                        <BookOpen
                            class="mx-auto h-12 w-12 text-muted-foreground/50"
                        />
                        <h3 class="mt-4 text-lg font-semibold">
                            No tienes préstamos activos
                        </h3>
                        <p class="mt-2 text-sm text-muted-foreground">
                            Explora nuestro catálogo y solicita libros para leer
                        </p>
                        <Button as-child class="mt-4">
                            <Link href="/books"> Ver Catálogo </Link>
                        </Button>
                    </div>
                </CardContent>
            </Card>

            <!-- Loan History -->
            <Card>
                <CardHeader>
                    <CardTitle>Historial de Préstamos</CardTitle>
                    <CardDescription>
                        Todos tus préstamos anteriores
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="loanHistory.length > 0" class="space-y-3">
                        <div
                            v-for="loan in loanHistory"
                            :key="loan.id"
                            class="flex items-start gap-4 rounded-lg border p-3 transition-colors hover:bg-accent/30"
                        >
                            <!-- Book Cover Small -->
                            <div class="flex-shrink-0">
                                <img
                                    :src="getBookCover(loan)"
                                    :alt="loan.physical_copy.book.title"
                                    class="h-16 w-12 rounded object-cover shadow-sm"
                                />
                            </div>

                            <!-- Info -->
                            <div class="min-w-0 flex-1">
                                <Link
                                    :href="`/books/${loan.physical_copy.book.id}`"
                                    class="line-clamp-1 font-medium hover:underline"
                                >
                                    {{ loan.physical_copy.book.title }}
                                </Link>
                                <div
                                    class="mt-1 flex flex-wrap gap-x-4 gap-y-1 text-sm text-muted-foreground"
                                >
                                    <!-- Mostrar fechas según disponibilidad -->
                                    <span
                                        v-if="
                                            loan.loan_date &&
                                            loan.actual_return_date
                                        "
                                    >
                                        {{ formatDate(loan.loan_date) }} -
                                        {{
                                            formatDate(loan.actual_return_date)
                                        }}
                                    </span>
                                    <span
                                        v-else-if="
                                            loan.loan_date && loan.due_date
                                        "
                                    >
                                        {{ formatDate(loan.loan_date) }} -
                                        {{ formatDate(loan.due_date) }}
                                    </span>
                                    <span v-else>
                                        {{ formatDate(loan.created_at) }}
                                    </span>

                                    <Badge
                                        :class="getLoanStatusBadge(loan).class"
                                        class="h-5"
                                    >
                                        {{ getLoanStatusBadge(loan).label }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty History -->
                    <div v-else class="py-8 text-center text-muted-foreground">
                        <p>No tienes historial de préstamos</p>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
