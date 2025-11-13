<!-- resources/js/pages/Reservations/Index.vue -->
<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    BookMarked, Calendar, Clock, AlertCircle, CheckCircle, XCircle,
    Ban, Package, Info, Trash2, Eye, ArrowRight
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

/**
 * Interface para las reservas de libros
 */
interface Reservation {
    id: number;
    book_id: number;
    user_id: number;
    reservation_date: string;
    pickup_deadline: string;
    pickup_date: string | null;
    cancellation_date: string | null;
    status: 'pending' | 'ready' | 'collected' | 'expired' | 'cancelled';
    notes: string | null;
    book: {
        id: number;
        title: string;
        cover_image: string | null;
        cover_url?: string | null;
        publisher?: {
            name: string;
        };
        contributors?: Array<{
            full_name: string;
            contributor_type: string;
        }>;
    };
}

interface Props {
    reservations: {
        data: Reservation[];
        current_page: number;
        last_page: number;
        total: number;
    };
    stats: {
        total: number;
        pending: number;
        ready: number;
        collected: number;
        expired: number;
        cancelled: number;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Inicio', href: '/' },
    { title: 'Mis Reservas', href: '/reservations' },
];

const cancellingId = ref<number | null>(null);

// ===============================================
// COMPUTED PROPERTIES
// ===============================================

/**
 * Reservas activas (pending + ready)
 */
const activeReservations = computed(() => {
    return props.reservations.data.filter(r => 
        r.status === 'pending' || r.status === 'ready'
    );
});

/**
 * Historial de reservas (collected + expired + cancelled)
 */
const reservationHistory = computed(() => {
    return props.reservations.data.filter(r => 
        r.status === 'collected' || r.status === 'expired' || r.status === 'cancelled'
    );
});

/**
 * Verificar si hay reservas listas para recoger
 */
const hasReadyReservations = computed(() => {
    return activeReservations.value.some(r => r.status === 'ready');
});

// ===============================================
// HELPER FUNCTIONS
// ===============================================

/**
 * Formatear fecha a texto legible
 */
const formatDate = (dateString: string): string => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', { 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric' 
    });
};

/**
 * Calcular días restantes hasta el deadline
 */
const getDaysRemaining = (deadline: string): number => {
    const today = new Date();
    const deadlineDate = new Date(deadline);
    const diffTime = deadlineDate.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};

/**
 * Obtener badge de estado según el tipo de reserva
 */
const getStatusBadge = (reservation: Reservation) => {
    const daysRemaining = getDaysRemaining(reservation.pickup_deadline);
    
    switch (reservation.status) {
        case 'pending':
            return {
                text: 'Pendiente',
                icon: Clock,
                class: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 border border-yellow-300 dark:border-yellow-800'
            };
        case 'ready':
            if (daysRemaining <= 1) {
                return {
                    text: '¡Recoge hoy!',
                    icon: AlertCircle,
                    class: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-300 dark:border-red-800 animate-pulse'
                };
            } else if (daysRemaining <= 2) {
                return {
                    text: 'Listo - Urgente',
                    icon: AlertCircle,
                    class: 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 border border-orange-300 dark:border-orange-800'
                };
            }
            return {
                text: 'Listo para recoger',
                icon: Package,
                class: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-300 dark:border-green-800'
            };
        case 'collected':
            return {
                text: 'Recogido',
                icon: CheckCircle,
                class: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-300 dark:border-blue-800'
            };
        case 'expired':
            return {
                text: 'Expirada',
                icon: XCircle,
                class: 'bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-400 border border-gray-300 dark:border-gray-800'
            };
        case 'cancelled':
            return {
                text: 'Cancelada',
                icon: Ban,
                class: 'bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-400 border border-gray-300 dark:border-gray-800'
            };
        default:
            return {
                text: 'Desconocido',
                icon: Info,
                class: 'bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-400 border border-gray-300 dark:border-gray-800'
            };
    }
};

/**
 * Obtener autores del libro
 */
const getAuthors = (book: Reservation['book']): string => {
    if (!book.contributors || book.contributors.length === 0) {
        return 'Autor desconocido';
    }
    const authors = book.contributors
        .filter(c => c.contributor_type === 'author')
        .map(c => c.full_name);
    return authors.length > 0 ? authors.join(', ') : 'Autor desconocido';
};

/**
 * Obtener URL de portada o placeholder
 */
const getCoverUrl = (book: Reservation['book']): string => {
    return book.cover_url || '/images/book-placeholder.svg';
};

// ===============================================
// ACTIONS
// ===============================================

/**
 * Cancelar una reserva
 */
const cancelReservation = async (reservationId: number) => {
    if (!confirm('¿Estás seguro de que deseas cancelar esta reserva?')) {
        return;
    }

    cancellingId.value = reservationId;

    router.delete(`/reservations/${reservationId}`, {
        preserveScroll: true,
        onSuccess: () => {
            cancellingId.value = null;
        },
        onError: (errors) => {
            console.error('Error al cancelar reserva:', errors);
            cancellingId.value = null;
            alert('Ocurrió un error al cancelar la reserva. Por favor, intenta de nuevo.');
        }
    });
};
</script>

<template>
    <AppLayout title="Mis Reservas" :breadcrumbs="breadcrumbs">
        <Head title="Mis Reservas" />

        <div class="container mx-auto px-4 py-8 max-w-7xl">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-3 bg-primary/10 rounded-xl">
                        <BookMarked class="w-8 h-8 text-primary" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">Mis Reservas</h1>
                        <p class="text-muted-foreground">Gestiona tus reservas de libros</p>
                    </div>
                </div>
            </div>

            <!-- Alert para reservas listas -->
            <div 
                v-if="hasReadyReservations" 
                class="mb-6 p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg"
            >
                <div class="flex items-start gap-3">
                    <Package class="w-5 h-5 text-green-600 dark:text-green-400 mt-0.5 shrink-0" />
                    <div>
                        <h3 class="font-semibold text-green-900 dark:text-green-100 mb-1">
                            ¡Tienes libros listos para recoger!
                        </h3>
                        <p class="text-sm text-green-700 dark:text-green-300">
                            Recuerda pasar por la biblioteca antes de la fecha límite para recoger tus libros reservados.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4 mb-8">
                <!-- Total -->
                <div class="bg-card p-4 rounded-xl border border-border shadow-sm">
                    <p class="text-xs text-muted-foreground mb-1">Total</p>
                    <p class="text-2xl font-bold text-foreground">{{ stats.total }}</p>
                </div>

                <!-- Pendientes -->
                <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-xl border border-yellow-200 dark:border-yellow-800 shadow-sm">
                    <p class="text-xs text-yellow-700 dark:text-yellow-400 mb-1">Pendientes</p>
                    <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ stats.pending }}</p>
                </div>

                <!-- Listas -->
                <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-xl border border-green-200 dark:border-green-800 shadow-sm">
                    <p class="text-xs text-green-700 dark:text-green-400 mb-1">Listas</p>
                    <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ stats.ready }}</p>
                </div>

                <!-- Recogidas -->
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-200 dark:border-blue-800 shadow-sm">
                    <p class="text-xs text-blue-700 dark:text-blue-400 mb-1">Recogidas</p>
                    <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ stats.collected }}</p>
                </div>

                <!-- Expiradas -->
                <div class="bg-gray-50 dark:bg-gray-900/20 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                    <p class="text-xs text-gray-700 dark:text-gray-400 mb-1">Expiradas</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats.expired }}</p>
                </div>

                <!-- Canceladas -->
                <div class="bg-gray-50 dark:bg-gray-900/20 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                    <p class="text-xs text-gray-700 dark:text-gray-400 mb-1">Canceladas</p>
                    <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats.cancelled }}</p>
                </div>
            </div>

            <!-- Reservas Activas -->
            <div v-if="activeReservations.length > 0" class="mb-8">
                <h2 class="text-xl font-bold text-foreground mb-4 flex items-center gap-2">
                    <Clock class="w-5 h-5 text-primary" />
                    Reservas Activas
                    <span class="text-sm font-normal text-muted-foreground">({{ activeReservations.length }})</span>
                </h2>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div 
                        v-for="reservation in activeReservations" 
                        :key="reservation.id"
                        class="bg-card rounded-xl border border-border p-4 hover:shadow-lg transition-shadow"
                    >
                        <div class="flex gap-4">
                            <!-- Portada -->
                            <div class="shrink-0">
                                <img 
                                    :src="getCoverUrl(reservation.book)" 
                                    :alt="reservation.book.title"
                                    class="w-20 h-28 object-cover rounded-lg shadow-md"
                                    @error="(e) => { const img = e.target as HTMLImageElement; if (!img.src.includes('placeholder')) img.src = '/images/book-placeholder.svg'; }"
                                />
                            </div>

                            <!-- Información -->
                            <div class="flex-1 min-w-0">
                                <!-- Título y Estado -->
                                <div class="mb-2">
                                    <Link 
                                        :href="`/books/${reservation.book.id}`"
                                        class="font-semibold text-foreground hover:text-primary transition-colors line-clamp-2"
                                    >
                                        {{ reservation.book.title }}
                                    </Link>
                                    <p class="text-sm text-muted-foreground">{{ getAuthors(reservation.book) }}</p>
                                </div>

                                <!-- Badge de Estado -->
                                <div class="mb-3">
                                    <span 
                                        :class="getStatusBadge(reservation).class"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium"
                                    >
                                        <component :is="getStatusBadge(reservation).icon" class="w-3.5 h-3.5" />
                                        {{ getStatusBadge(reservation).text }}
                                    </span>
                                </div>

                                <!-- Fechas -->
                                <div class="space-y-1 text-sm mb-3">
                                    <div class="flex items-center gap-2 text-muted-foreground">
                                        <Calendar class="w-4 h-4" />
                                        <span>Reservado: {{ formatDate(reservation.reservation_date) }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Clock class="w-4 h-4" />
                                        <span 
                                            :class="getDaysRemaining(reservation.pickup_deadline) <= 1 
                                                ? 'text-red-600 dark:text-red-400 font-semibold' 
                                                : getDaysRemaining(reservation.pickup_deadline) <= 2
                                                ? 'text-orange-600 dark:text-orange-400 font-semibold'
                                                : 'text-muted-foreground'"
                                        >
                                            Recoger antes: {{ formatDate(reservation.pickup_deadline) }}
                                            <span v-if="reservation.status === 'ready'">
                                                ({{ getDaysRemaining(reservation.pickup_deadline) }} días)
                                            </span>
                                        </span>
                                    </div>
                                </div>

                                <!-- Acciones -->
                                <div class="flex gap-2">
                                    <Link
                                        :href="`/books/${reservation.book.id}`"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-primary hover:text-primary/80 border border-primary/20 rounded-lg hover:bg-primary/5 transition-colors"
                                    >
                                        <Eye class="w-4 h-4" />
                                        Ver libro
                                    </Link>

                                    <button
                                        v-if="reservation.status === 'pending' || (reservation.status === 'ready' && getDaysRemaining(reservation.pickup_deadline) > 0)"
                                        @click="cancelReservation(reservation.id)"
                                        :disabled="cancellingId === reservation.id"
                                        class="inline-flex items-center gap-1 px-3 py-1.5 text-sm font-medium text-destructive hover:text-destructive/80 border border-destructive/20 rounded-lg hover:bg-destructive/5 transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <Trash2 class="w-4 h-4" />
                                        {{ cancellingId === reservation.id ? 'Cancelando...' : 'Cancelar' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State - Sin reservas activas -->
            <div 
                v-else 
                class="bg-card rounded-xl border border-border p-12 text-center mb-8"
            >
                <BookMarked class="w-16 h-16 mx-auto mb-4 text-muted-foreground opacity-50" />
                <h3 class="text-xl font-semibold text-foreground mb-2">No tienes reservas activas</h3>
                <p class="text-muted-foreground mb-6">
                    Cuando reserves un libro, aparecerá aquí
                </p>
                <Link
                    href="/books"
                    class="inline-flex items-center gap-2 px-6 py-3 bg-primary text-primary-foreground rounded-lg hover:bg-primary/90 transition-colors font-medium"
                >
                    Explorar catálogo
                    <ArrowRight class="w-4 h-4" />
                </Link>
            </div>

            <!-- Historial de Reservas -->
            <div v-if="reservationHistory.length > 0">
                <h2 class="text-xl font-bold text-foreground mb-4 flex items-center gap-2">
                    <Calendar class="w-5 h-5 text-muted-foreground" />
                    Historial de Reservas
                    <span class="text-sm font-normal text-muted-foreground">({{ reservationHistory.length }})</span>
                </h2>

                <div class="bg-card rounded-xl border border-border overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-muted/50 border-b border-border">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Libro</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Reservado</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Deadline</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Estado</th>
                                    <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Acción</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <tr 
                                    v-for="reservation in reservationHistory" 
                                    :key="reservation.id"
                                    class="hover:bg-muted/30 transition-colors"
                                >
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-3">
                                            <img 
                                                :src="getCoverUrl(reservation.book)" 
                                                :alt="reservation.book.title"
                                                class="w-10 h-14 object-cover rounded shadow-sm"
                                                @error="(e) => { const img = e.target as HTMLImageElement; if (!img.src.includes('placeholder')) img.src = '/images/book-placeholder.svg'; }"
                                            />
                                            <div>
                                                <p class="font-medium text-foreground text-sm line-clamp-1">{{ reservation.book.title }}</p>
                                                <p class="text-xs text-muted-foreground">{{ getAuthors(reservation.book) }}</p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3 text-sm text-muted-foreground">
                                        {{ formatDate(reservation.reservation_date) }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-muted-foreground">
                                        {{ formatDate(reservation.pickup_deadline) }}
                                    </td>
                                    <td class="px-4 py-3">
                                        <span 
                                            :class="getStatusBadge(reservation).class"
                                            class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium"
                                        >
                                            <component :is="getStatusBadge(reservation).icon" class="w-3 h-3" />
                                            {{ getStatusBadge(reservation).text }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <Link
                                            :href="`/books/${reservation.book.id}`"
                                            class="inline-flex items-center gap-1 text-sm text-primary hover:text-primary/80 font-medium"
                                        >
                                            <Eye class="w-4 h-4" />
                                            Ver
                                        </Link>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Información adicional -->
            <div class="mt-8 p-4 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg">
                <div class="flex items-start gap-3">
                    <Info class="w-5 h-5 text-blue-600 dark:text-blue-400 mt-0.5 shrink-0" />
                    <div class="text-sm text-blue-900 dark:text-blue-100">
                        <p class="font-semibold mb-2">Información sobre reservas:</p>
                        <ul class="list-disc list-inside space-y-1 text-blue-700 dark:text-blue-300">
                            <li>Las reservas tienen un plazo de 7 días para recoger el libro</li>
                            <li>Si no recoges el libro a tiempo, la reserva expirará automáticamente</li>
                            <li>Puedes cancelar una reserva en cualquier momento antes de que expire</li>
                            <li>Máximo 5 reservas activas simultáneas por usuario</li>
                            <li>Recibirás una notificación cuando tu libro esté listo para recoger</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
