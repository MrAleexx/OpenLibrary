<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    AlertTriangle,
    BookMarked,
    BookOpen,
    CheckCircle,
    Clock,
    Filter,
    Package,
    RefreshCw,
    Search,
    User,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { usePolling } from '@/composables/useLoanPolling';

interface PhysicalCopy {
    id: number;
    copy_number: string;
    status: string;
    book: {
        id: number;
        title: string;
        cover_image: string | null;
        cover_url?: string | null;
    };
}

interface LoanUser {
    id: number;
    name: string;
    last_name: string;
    email: string;
    dni: string;
}

interface Loan {
    id: number;
    user_id: number;
    physical_copy_id: number;
    loan_date: string | null;
    due_date: string | null;
    return_date: string | null;
    status:
    | 'pending_pickup'
    | 'ready_for_pickup'
    | 'active'
    | 'overdue'
    | 'returned'
    | 'returned_pending'
    | 'cancelled';
    renewal_count: number;
    user: LoanUser;
    physical_copy: PhysicalCopy;
}

interface PaginatedLoans {
    data: Loan[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
}

interface Stats {
    total: number;
    pending_pickup: number;
    ready_for_pickup: number;
    active: number;
    overdue: number;
    returned: number;
    returned_pending: number;
    cancelled: number;
    overdue_soon: number;
}

interface Props {
    loans: PaginatedLoans;
    stats: Stats;
    filters: {
        status: string | null;
        search: string | null;
        user_id: number | null;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Admin', href: '/admin/dashboard' },
    { title: 'Préstamos', href: '/admin/loans' },
];

const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const processingId = ref<number | null>(null);

// ===============================================
// COMPUTED
// ===============================================

const hasFilters = computed(() => {
    return (
        props.filters.status || props.filters.search || props.filters.user_id
    );
});

// ===============================================
// HELPER FUNCTIONS
// ===============================================

/**
 * Formatear fecha
 */
const formatDate = (dateString: string | null): string => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
    });
};

/**
 * Calcular días hasta vencimiento
 */
const getDaysUntilDue = (dueDate: string | null): number => {
    if (!dueDate) return 0;
    const today = new Date();
    const due = new Date(dueDate);
    const diffTime = due.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};

/**
 * Obtener badge de estado
 */
const getStatusBadge = (loan: Loan) => {
    const daysRemaining = getDaysUntilDue(loan.due_date);

    switch (loan.status) {
        case 'pending_pickup':
            return {
                text: 'Pendiente Preparación',
                icon: Clock,
                class: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 border border-yellow-300 dark:border-yellow-800',
            };
        case 'ready_for_pickup':
            return {
                text: 'Listo para Recoger',
                icon: Package,
                class: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-300 dark:border-blue-800',
            };
        case 'active':
            if (daysRemaining <= 0) {
                return {
                    text: 'Vencido',
                    icon: AlertTriangle,
                    class: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-300 dark:border-red-800',
                };
            } else if (daysRemaining <= 3) {
                return {
                    text: `Por Vencer (${daysRemaining}d)`,
                    icon: AlertTriangle,
                    class: 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 border border-orange-300 dark:border-orange-800',
                };
            }
            return {
                text: 'Activo',
                icon: BookOpen,
                class: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-300 dark:border-green-800',
            };
        case 'overdue':
            return {
                text: 'Vencido',
                icon: XCircle,
                class: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-300 dark:border-red-800 animate-pulse',
            };
        case 'returned':
            return {
                text: 'Devuelto',
                icon: CheckCircle,
                class: 'bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-400 border border-gray-300 dark:border-gray-800',
            };
        case 'returned_pending':
            return {
                text: 'Verificando',
                icon: Clock,
                class: 'bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-400 border border-purple-300 dark:border-purple-800 animate-pulse',
            };
        case 'cancelled':
            return {
                text: 'Cancelado',
                icon: XCircle,
                class: 'bg-slate-100 dark:bg-slate-900/30 text-slate-700 dark:text-slate-400 border border-slate-300 dark:border-slate-800',
            };
        default:
            return {
                text: 'Desconocido',
                icon: AlertTriangle,
                class: 'bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-400 border border-gray-300 dark:border-gray-800',
            };
    }
};

/**
 * Obtener acciones disponibles según estado
 */
const getAvailableActions = (loan: Loan) => {
    switch (loan.status) {
        case 'pending_pickup':
            return [
                {
                    label: 'Marcar como Listo',
                    action: () => markAsReady(loan.id),
                    color: 'bg-green-600 hover:bg-green-700',
                },
                {
                    label: 'Cancelar',
                    action: () => cancelLoan(loan.id),
                    color: 'bg-red-600 hover:bg-red-700',
                },
            ];
        case 'ready_for_pickup':
            return [
                {
                    label: 'Confirmar Entrega',
                    action: () => confirmHandover(loan.id),
                    color: 'bg-blue-600 hover:bg-blue-700',
                },
                {
                    label: 'Cancelar',
                    action: () => cancelLoan(loan.id),
                    color: 'bg-red-600 hover:bg-red-700',
                },
            ];
        case 'active':
        case 'overdue':
            return [
                {
                    label: 'Marcar como Devuelto',
                    action: () => markAsReturned(loan.id),
                    color: 'bg-green-600 hover:bg-green-700',
                },
            ];
        case 'returned_pending':
            return [
                {
                    label: 'Confirmar Devolución',
                    action: () => verifyReturn(loan.id),
                    color: 'bg-green-600 hover:bg-green-700',
                },
                {
                    label: 'Rechazar',
                    action: () => rejectReturn(loan.id),
                    color: 'bg-red-600 hover:bg-red-700',
                },
            ];
        default:
            return [];
    }
};

// ===============================================
// ACTIONS
// ===============================================

/**
 * Marcar como listo para recoger
 */
const markAsReady = (loanId: number) => {
    if (!confirm('¿Marcar este préstamo como listo para recoger?')) return;

    processingId.value = loanId;
    router.post(
        `/admin/loans/${loanId}/mark-ready`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                processingId.value = null;
            },
        },
    );
};

/**
 * Confirmar entrega al usuario (activar préstamo)
 */
const confirmHandover = (loanId: number) => {
    if (!confirm('¿Confirmar que el usuario recogió el libro?')) return;

    processingId.value = loanId;
    router.post(
        `/admin/loans/${loanId}/activate`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                processingId.value = null;
            },
        },
    );
};

/**
 * Marcar como devuelto
 */
const markAsReturned = (loanId: number) => {
    if (!confirm('¿Marcar este préstamo como devuelto?')) return;

    processingId.value = loanId;
    router.post(
        `/admin/loans/${loanId}/mark-returned`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                processingId.value = null;
            },
        },
    );
};

const verifyReturn = (loanId: number) => {
    if (!confirm('¿Confirmar que el libro ha sido devuelto correctamente?')) return;

    processingId.value = loanId;
    router.post(
        `/admin/loans/${loanId}/verify-return`,
        {},
        {
            preserveScroll: true,
            onFinish: () => {
                processingId.value = null;
            },
        },
    );
};

/**
 * Rechazar devolución
 */
const rejectReturn = (loanId: number) => {
    const reason = prompt('Ingrese la razón del rechazo:');
    if (!reason || reason.trim() === '') return;

    processingId.value = loanId;
    router.post(
        `/admin/loans/${loanId}/reject-return`,
        { reason: reason.trim() },
        {
            preserveScroll: true,
            onFinish: () => {
                processingId.value = null;
            },
        },
    );
};

/**
 * Cancelar préstamo
 */
const cancelLoan = (loanId: number) => {
    const reason = prompt('Ingrese la razón de la cancelación:');
    if (!reason || reason.trim() === '') return;

    processingId.value = loanId;
    router.post(
        `/admin/loans/${loanId}/cancel`,
        { reason: reason.trim() },
        {
            preserveScroll: true,
            onFinish: () => {
                processingId.value = null;
            },
        },
    );
};

/**
 * Aplicar filtros
 */
const applyFilters = () => {
    router.get(
        '/admin/loans',
        {
            status: selectedStatus.value || undefined,
            search: searchQuery.value || undefined,
        },
        {
            preserveState: true,
            preserveScroll: true,
        },
    );
};

/**
 * Limpiar filtros
 */
const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = '';
    router.get('/admin/loans');
};

// Activar polling
usePolling(3000, ['loans', 'stats']);
</script>

<template>
    <AppLayout title="Gestión de Préstamos" :breadcrumbs="breadcrumbs">

        <Head title="Gestión de Préstamos" />

        <div class="container mx-auto max-w-7xl px-4 py-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="mb-6 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="rounded-xl bg-primary/10 p-3">
                            <BookMarked class="h-8 w-8 text-primary" />
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-foreground">
                                Gestión de Préstamos
                            </h1>
                            <p class="text-muted-foreground">
                                Administra todos los préstamos del sistema
                            </p>
                        </div>
                    </div>
                    <button @click="applyFilters"
                        class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-primary-foreground transition-colors hover:bg-primary/90">
                        <RefreshCw class="h-4 w-4" />
                        Actualizar
                    </button>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-2 gap-4 md:grid-cols-4 lg:grid-cols-8">
                    <div class="rounded-xl border border-border bg-card p-4 shadow-sm">
                        <p class="mb-1 text-xs text-muted-foreground">Total</p>
                        <p class="text-2xl font-bold text-foreground">
                            {{ stats.total }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-yellow-200 bg-yellow-50 p-4 shadow-sm dark:border-yellow-800 dark:bg-yellow-900/20">
                        <p class="mb-1 text-xs text-yellow-700 dark:text-yellow-400">
                            Pendientes
                        </p>
                        <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">
                            {{ stats.pending_pickup }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-blue-200 bg-blue-50 p-4 shadow-sm dark:border-blue-800 dark:bg-blue-900/20">
                        <p class="mb-1 text-xs text-blue-700 dark:text-blue-400">
                            Listos
                        </p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">
                            {{ stats.ready_for_pickup }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-green-200 bg-green-50 p-4 shadow-sm dark:border-green-800 dark:bg-green-900/20">
                        <p class="mb-1 text-xs text-green-700 dark:text-green-400">
                            Activos
                        </p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">
                            {{ stats.active }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-red-200 bg-red-50 p-4 shadow-sm dark:border-red-800 dark:bg-red-900/20">
                        <p class="mb-1 text-xs text-red-700 dark:text-red-400">
                            Vencidos
                        </p>
                        <p class="text-2xl font-bold text-red-900 dark:text-red-100">
                            {{ stats.overdue }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-orange-200 bg-orange-50 p-4 shadow-sm dark:border-orange-800 dark:bg-orange-900/20">
                        <p class="mb-1 text-xs text-orange-700 dark:text-orange-400">
                            Por Vencer
                        </p>
                        <p class="text-2xl font-bold text-orange-900 dark:text-orange-100">
                            {{ stats.overdue_soon }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-gray-200 bg-gray-50 p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900/20">
                        <p class="mb-1 text-xs text-gray-700 dark:text-gray-400">
                            Devueltos
                        </p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">
                            {{ stats.returned }}
                        </p>
                    </div>
                    <div
                        class="rounded-xl border border-slate-200 bg-slate-50 p-4 shadow-sm dark:border-slate-800 dark:bg-slate-900/20">
                        <p class="mb-1 text-xs text-slate-700 dark:text-slate-400">
                            Cancelados
                        </p>
                        <p class="text-2xl font-bold text-slate-900 dark:text-slate-100">
                            {{ stats.cancelled || 0 }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="mb-6 rounded-xl border border-border bg-card p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-2">
                    <Filter class="h-5 w-5 text-muted-foreground" />
                    <h2 class="text-lg font-semibold text-foreground">
                        Filtros
                    </h2>
                </div>
                <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <!-- Búsqueda -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-foreground">
                            <Search class="mr-1 inline h-4 w-4" />
                            Buscar
                        </label>
                        <input v-model="searchQuery" type="text" placeholder="Usuario, DNI, libro..."
                            class="w-full rounded-lg border border-border bg-background px-4 py-2 text-foreground focus:border-transparent focus:ring-2 focus:ring-primary"
                            @keyup.enter="applyFilters" />
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="mb-2 block text-sm font-medium text-foreground">Estado</label>
                        <select v-model="selectedStatus"
                            class="w-full rounded-lg border border-border bg-background px-4 py-2 text-foreground focus:border-transparent focus:ring-2 focus:ring-primary"
                            @change="applyFilters">
                            <option value="">Todos</option>
                            <option value="pending_pickup">
                                Pendiente Preparación
                            </option>
                            <option value="ready_for_pickup">
                                Listo para Recoger
                            </option>
                            <option value="active">Activo</option>
                            <option value="overdue">Vencido</option>
                            <option value="returned">Devuelto</option>
                            <option value="returned_pending">Verificando Devolución</option>
                            <option value="cancelled">Cancelado</option>
                        </select>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-end gap-2">
                        <button @click="applyFilters"
                            class="rounded-lg bg-primary px-6 py-2 font-medium text-primary-foreground transition-colors hover:bg-primary/90">
                            Aplicar
                        </button>
                        <button v-if="hasFilters" @click="clearFilters"
                            class="rounded-lg bg-muted px-6 py-2 font-medium text-muted-foreground transition-colors hover:bg-muted/80">
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de Préstamos -->
            <div class="overflow-hidden rounded-xl border border-border bg-card shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b border-border bg-muted/50">
                            <tr>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Usuario
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Libro
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Estado
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Fecha Préstamo
                                </th>
                                <th
                                    class="px-4 py-3 text-left text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Vencimiento
                                </th>
                                <th
                                    class="px-4 py-3 text-right text-xs font-medium tracking-wider text-muted-foreground uppercase">
                                    Acciones
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr v-for="loan in loans.data" :key="loan.id" class="transition-colors hover:bg-muted/30">
                                <!-- Usuario -->
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-primary/10">
                                            <User class="h-5 w-5 text-primary" />
                                        </div>
                                        <div>
                                            <p class="text-sm font-medium text-foreground">
                                                {{ loan.user.name }}
                                                {{ loan.user.last_name }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                DNI: {{ loan.user.dni }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Libro -->
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <img :src="loan.physical_copy.book
                                            .cover_url ||
                                            '/images/book-placeholder.svg'
                                            " :alt="loan.physical_copy.book.title"
                                            class="h-14 w-10 rounded object-cover shadow-sm" @error="
                                                (e) => {
                                                    const img =
                                                        e.target as HTMLImageElement;
                                                    if (
                                                        !img.src.includes(
                                                            'placeholder',
                                                        )
                                                    )
                                                        img.src =
                                                            '/images/book-placeholder.svg';
                                                }
                                            " />
                                        <div class="max-w-xs">
                                            <p class="line-clamp-2 text-sm font-medium text-foreground">
                                                {{
                                                    loan.physical_copy.book
                                                        .title
                                                }}
                                            </p>
                                            <p class="text-xs text-muted-foreground">
                                                Copia #{{
                                                    loan.physical_copy
                                                        .copy_number
                                                }}
                                            </p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Estado -->
                                <td class="px-4 py-4">
                                    <span :class="getStatusBadge(loan).class"
                                        class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-medium">
                                        <component :is="getStatusBadge(loan).icon" class="h-3.5 w-3.5" />
                                        {{ getStatusBadge(loan).text }}
                                    </span>
                                </td>

                                <!-- Fecha Préstamo -->
                                <td class="px-4 py-4 text-sm text-muted-foreground">
                                    {{ formatDate(loan.loan_date) }}
                                </td>

                                <!-- Vencimiento -->
                                <td class="px-4 py-4 text-sm">
                                    <span v-if="loan.due_date" :class="getDaysUntilDue(loan.due_date) <=
                                        3 && loan.status === 'active'
                                        ? 'font-semibold text-red-600 dark:text-red-400'
                                        : 'text-muted-foreground'
                                        ">
                                        {{ formatDate(loan.due_date) }}
                                        <span v-if="loan.status === 'active'" class="block text-xs">
                                            ({{
                                                getDaysUntilDue(loan.due_date)
                                            }}
                                            días)
                                        </span>
                                    </span>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>

                                <!-- Acciones -->
                                <td class="px-4 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button v-for="(
actionItem, index
                                            ) in getAvailableActions(loan)" :key="index" @click="actionItem.action"
                                            :disabled="processingId === loan.id" :class="actionItem.color"
                                            class="rounded-lg px-3 py-1.5 text-xs font-medium text-white transition-colors disabled:cursor-not-allowed disabled:opacity-50">
                                            {{
                                                processingId === loan.id
                                                    ? 'Procesando...'
                                                    : actionItem.label
                                            }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="loans.data.length === 0" class="p-12 text-center">
                    <BookOpen class="mx-auto mb-4 h-16 w-16 text-muted-foreground opacity-50" />
                    <h3 class="mb-2 text-xl font-semibold text-foreground">
                        No se encontraron préstamos
                    </h3>
                    <p class="text-muted-foreground">
                        {{
                            hasFilters
                                ? 'Intenta ajustar los filtros'
                                : 'Aún no hay préstamos en el sistema'
                        }}
                    </p>
                </div>

                <!-- Paginación -->
                <div v-if="loans.last_page > 1" class="border-t border-border px-6 py-4">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">
                            Mostrando {{ loans.data.length }} de
                            {{ loans.total }} préstamos
                        </p>
                        <div class="flex gap-2">
                            <Link v-for="page in loans.last_page" :key="page" :href="`/admin/loans?page=${page}`"
                                :class="page === loans.current_page
                                    ? 'bg-primary text-primary-foreground'
                                    : 'bg-muted text-muted-foreground hover:bg-muted/80'
                                    " class="rounded-lg px-3 py-1 text-sm font-medium transition-colors"
                                preserve-state>
                            {{ page }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
