<script setup lang="ts">
import { Head, router, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    Calendar, Search, Filter, Clock, CheckCircle, 
    XCircle, AlertCircle, User, BookMarked, RefreshCw,
    Package, ArrowRight, Ban
} from 'lucide-vue-next';
import { ref, computed } from 'vue';

interface Book {
    id: number;
    title: string;
    cover_image: string | null;
    cover_url?: string | null;
}

interface PhysicalCopy {
    id: number;
    copy_number: string;
    status: string;
    book: Book;
}

interface ReservationUser {
    id: number;
    name: string;
    last_name: string;
    email: string;
    dni: string;
}

interface Reservation {
    id: number;
    user_id: number;
    physical_copy_id: number | null;
    book_id: number | null;
    reservation_date: string;
    expiration_date: string;
    pickup_date: string | null;
    cancellation_date: string | null;
    status: 'pending' | 'ready' | 'collected' | 'expired' | 'cancelled';
    user: ReservationUser;
    physical_copy?: PhysicalCopy;
    book?: Book;
    queue_position?: number;
    total_in_queue?: number;
}

interface PaginatedReservations {
    data: Reservation[];
    current_page: number;
    last_page: number;
    total: number;
    per_page: number;
}

interface Stats {
    total: number;
    pending: number;
    ready: number;
    collected: number;
    expired: number;
    cancelled: number;
}

interface Props {
    reservations: PaginatedReservations;
    stats: Stats;
    filters: {
        status: string | null;
        search: string | null;
    };
}

const props = defineProps<Props>();

const breadcrumbs = [
    { title: 'Admin', href: '/admin/dashboard' },
    { title: 'Reservas', href: '/admin/reservations' },
];

const searchQuery = ref(props.filters.search || '');
const selectedStatus = ref(props.filters.status || '');
const processingId = ref<number | null>(null);

// ===============================================
// COMPUTED
// ===============================================

const hasFilters = computed(() => {
    return props.filters.status || props.filters.search;
});

// ===============================================
// HELPER FUNCTIONS
// ===============================================

// ===============================================
// HELPER FUNCTIONS
// ===============================================

/**
 * Obtener el libro de una reserva (puede venir de physical_copy o directamente)
 */
const getBook = (reservation: Reservation): Book | null => {
    if (reservation.physical_copy?.book) {
        return reservation.physical_copy.book;
    }
    if (reservation.book) {
        return reservation.book;
    }
    return null;
};

/**
 * Obtener título del libro
 */
const getBookTitle = (reservation: Reservation): string => {
    const book = getBook(reservation);
    return book?.title || 'Sin título';
};

/**
 * Obtener cover URL del libro
 */
const getBookCoverUrl = (reservation: Reservation): string => {
    const book = getBook(reservation);
    return book?.cover_url || '/images/book-placeholder.svg';
};

/**
 * Obtener número de copia (si existe)
 */
const getCopyNumber = (reservation: Reservation): string | null => {
    return reservation.physical_copy?.copy_number || null;
};

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
        hour: '2-digit',
        minute: '2-digit'
    });
};

/**
 * Formatear fecha corta
 */
const formatDateShort = (dateString: string | null): string => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('es-ES', { 
        month: 'short', 
        day: 'numeric'
    });
};

/**
 * Calcular días restantes
 */
const getDaysRemaining = (expirationDate: string): number => {
    const today = new Date();
    const expiry = new Date(expirationDate);
    const diffTime = expiry.getTime() - today.getTime();
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};

/**
 * Obtener badge de estado
 */
const getStatusBadge = (reservation: Reservation) => {
    const daysRemaining = getDaysRemaining(reservation.expiration_date);
    
    switch (reservation.status) {
        case 'pending':
            // En cola esperando que se devuelva una copia
            const queueInfo = reservation.queue_position && reservation.total_in_queue 
                ? ` - Posición ${reservation.queue_position}/${reservation.total_in_queue}`
                : '';
            return {
                text: `En Cola de Espera${queueInfo}`,
                icon: Clock,
                class: 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-400 border border-yellow-300 dark:border-yellow-800'
            };
        case 'ready':
            // Libro apartado - usuario ya fue notificado - esperando que llegue
            if (daysRemaining <= 1) {
                return {
                    text: `Usuario Notificado - Expira Hoy`,
                    icon: AlertCircle,
                    class: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-300 dark:border-red-800 animate-pulse'
                };
            } else if (daysRemaining <= 2) {
                return {
                    text: `Usuario Notificado - ${daysRemaining}d restantes`,
                    icon: Package,
                    class: 'bg-orange-100 dark:bg-orange-900/30 text-orange-700 dark:text-orange-400 border border-orange-300 dark:border-orange-800'
                };
            }
            return {
                text: `Usuario Notificado - ${daysRemaining}d restantes`,
                icon: Package,
                class: 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-300 dark:border-green-800'
            };
        case 'collected':
            return {
                text: 'Libro Entregado',
                icon: CheckCircle,
                class: 'bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 border border-blue-300 dark:border-blue-800'
            };
        case 'expired':
            return {
                text: 'Expirado - No llegó a recoger',
                icon: XCircle,
                class: 'bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-400 border border-gray-300 dark:border-gray-800'
            };
        case 'cancelled':
            return {
                text: 'Cancelado',
                icon: Ban,
                class: 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-400 border border-red-300 dark:border-red-800'
            };
        default:
            return {
                text: 'Desconocido',
                icon: AlertCircle,
                class: 'bg-gray-100 dark:bg-gray-900/30 text-gray-700 dark:text-gray-400 border border-gray-300 dark:border-gray-800'
            };
    }
};

/**
 * Obtener acciones disponibles según estado
 */
const getAvailableActions = (reservation: Reservation) => {
    switch (reservation.status) {
        case 'pending':
            // NO mostrar "Activar" - las reservas pending se activan automáticamente
            // cuando otro usuario devuelve el libro (sistema FIFO)
            return [
                {
                    label: 'Cancelar Reserva',
                    action: () => cancelReservation(reservation.id),
                    color: 'bg-red-600 hover:bg-red-700',
                    icon: XCircle
                }
            ];
        case 'ready':
            // El libro está apartado - el usuario ya fue notificado
            // Cuando el usuario llegue físicamente, el admin registra la entrega
            return [
                {
                    label: 'Registrar Entrega',
                    action: () => convertToLoan(reservation.id),
                    color: 'bg-green-600 hover:bg-green-700',
                    icon: Package
                },
                {
                    label: 'Cancelar Reserva',
                    action: () => cancelReservation(reservation.id),
                    color: 'bg-red-600 hover:bg-red-700',
                    icon: XCircle
                }
            ];
        default:
            return [];
    }
};

// ===============================================
// ACTIONS
// ===============================================

/**
 * Marcar como listo (DESHABILITADO - Solo automático vía FIFO)
 */
const markAsReady = (reservationId: number) => {
    // Esta función ya no se usa porque eliminamos el botón "Activar"
    // Las reservas pending se activan automáticamente cuando se devuelve un libro
    console.warn('markAsReady está deshabilitado. Use sistema FIFO automático.');
};

/**
 * Registrar entrega física del libro al usuario
 */
const convertToLoan = (reservationId: number) => {
    if (!confirm('¿El usuario está presente para recoger su libro? Se registrará la entrega y se creará el préstamo.')) return;
    
    processingId.value = reservationId;
    router.post(`/admin/reservations/${reservationId}/convert-to-loan`, {}, {
        preserveScroll: true,
        onFinish: () => {
            processingId.value = null;
        }
    });
};

/**
 * Cancelar reserva
 */
const cancelReservation = (reservationId: number) => {
    if (!confirm('¿Cancelar esta reserva? Si estaba lista, la copia volverá a estar disponible y se notificará a la siguiente persona en cola.')) return;
    
    processingId.value = reservationId;
    router.post(`/admin/reservations/${reservationId}/cancel`, {}, {
        preserveScroll: true,
        onFinish: () => {
            processingId.value = null;
        }
    });
};

/**
 * Aplicar filtros
 */
const applyFilters = () => {
    router.get('/admin/reservations', {
        status: selectedStatus.value || undefined,
        search: searchQuery.value || undefined,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

/**
 * Limpiar filtros
 */
const clearFilters = () => {
    searchQuery.value = '';
    selectedStatus.value = '';
    router.get('/admin/reservations');
};
</script>

<template>
    <AppLayout title="Gestión de Reservas" :breadcrumbs="breadcrumbs">
        <Head title="Gestión de Reservas" />

        <div class="container mx-auto px-4 py-8 max-w-7xl">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center gap-3">
                        <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-xl">
                            <Calendar class="w-8 h-8 text-purple-600 dark:text-purple-400" />
                        </div>
                        <div>
                            <h1 class="text-3xl font-bold text-foreground">Gestión de Reservas</h1>
                            <p class="text-muted-foreground">Administra la cola de reservas del sistema</p>
                        </div>
                    </div>
                    <button
                        @click="applyFilters"
                        class="inline-flex items-center gap-2 px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors"
                    >
                        <RefreshCw class="w-4 h-4" />
                        Actualizar
                    </button>
                </div>

                <!-- Estadísticas -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                    <div class="bg-card p-4 rounded-xl border border-border shadow-sm">
                        <p class="text-xs text-muted-foreground mb-1">Total</p>
                        <p class="text-2xl font-bold text-foreground">{{ stats.total }}</p>
                    </div>
                    <div class="bg-yellow-50 dark:bg-yellow-900/20 p-4 rounded-xl border border-yellow-200 dark:border-yellow-800 shadow-sm">
                        <p class="text-xs text-yellow-700 dark:text-yellow-400 mb-1">En Cola</p>
                        <p class="text-2xl font-bold text-yellow-900 dark:text-yellow-100">{{ stats.pending }}</p>
                    </div>
                    <div class="bg-green-50 dark:bg-green-900/20 p-4 rounded-xl border border-green-200 dark:border-green-800 shadow-sm">
                        <p class="text-xs text-green-700 dark:text-green-400 mb-1">Listas</p>
                        <p class="text-2xl font-bold text-green-900 dark:text-green-100">{{ stats.ready }}</p>
                    </div>
                    <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-200 dark:border-blue-800 shadow-sm">
                        <p class="text-xs text-blue-700 dark:text-blue-400 mb-1">Recogidas</p>
                        <p class="text-2xl font-bold text-blue-900 dark:text-blue-100">{{ stats.collected }}</p>
                    </div>
                    <div class="bg-gray-50 dark:bg-gray-900/20 p-4 rounded-xl border border-gray-200 dark:border-gray-800 shadow-sm">
                        <p class="text-xs text-gray-700 dark:text-gray-400 mb-1">Expiradas</p>
                        <p class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ stats.expired }}</p>
                    </div>
                    <div class="bg-red-50 dark:bg-red-900/20 p-4 rounded-xl border border-red-200 dark:border-red-800 shadow-sm">
                        <p class="text-xs text-red-700 dark:text-red-400 mb-1">Canceladas</p>
                        <p class="text-2xl font-bold text-red-900 dark:text-red-100">{{ stats.cancelled }}</p>
                    </div>
                </div>
            </div>

            <!-- Alerta de Reservas Listas -->
            <div 
                v-if="stats.ready > 0"
                class="bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800 rounded-xl p-4 mb-6"
            >
                <div class="flex items-center gap-3">
                    <AlertCircle class="w-5 h-5 text-orange-600 dark:text-orange-400 shrink-0" />
                    <div>
                        <p class="font-semibold text-orange-900 dark:text-orange-100">
                            {{ stats.ready }} {{ stats.ready === 1 ? 'usuario notificado' : 'usuarios notificados' }}
                        </p>
                        <p class="text-sm text-orange-700 dark:text-orange-300">
                            Estos usuarios fueron notificados de que su libro está apartado. Cuando lleguen a la biblioteca, registra la entrega haciendo clic en "Registrar Entrega".
                        </p>
                    </div>
                </div>
            </div>

            <!-- Filtros -->
            <div class="bg-card rounded-xl border border-border p-6 mb-6 shadow-sm">
                <div class="flex items-center gap-2 mb-4">
                    <Filter class="w-5 h-5 text-muted-foreground" />
                    <h2 class="text-lg font-semibold text-foreground">Filtros</h2>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Búsqueda -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">
                            <Search class="w-4 h-4 inline mr-1" />
                            Buscar
                        </label>
                        <input
                            v-model="searchQuery"
                            type="text"
                            placeholder="Usuario, DNI, libro..."
                            class="w-full px-4 py-2 border border-border rounded-lg bg-background text-foreground focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            @keyup.enter="applyFilters"
                        />
                    </div>

                    <!-- Estado -->
                    <div>
                        <label class="block text-sm font-medium text-foreground mb-2">Estado</label>
                        <select
                            v-model="selectedStatus"
                            class="w-full px-4 py-2 border border-border rounded-lg bg-background text-foreground focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                            @change="applyFilters"
                        >
                            <option value="">Todos</option>
                            <option value="pending">En Cola</option>
                            <option value="ready">Listas</option>
                            <option value="collected">Recogidas</option>
                            <option value="expired">Expiradas</option>
                            <option value="cancelled">Canceladas</option>
                        </select>
                    </div>

                    <!-- Botones -->
                    <div class="flex items-end gap-2">
                        <button
                            @click="applyFilters"
                            class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors font-medium"
                        >
                            Aplicar
                        </button>
                        <button
                            v-if="hasFilters"
                            @click="clearFilters"
                            class="px-6 py-2 bg-muted text-muted-foreground rounded-lg hover:bg-muted/80 transition-colors font-medium"
                        >
                            Limpiar
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tabla de Reservas -->
            <div class="bg-card rounded-xl border border-border overflow-hidden shadow-sm">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-muted/50 border-b border-border">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Posición</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Usuario</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Libro</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Estado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Reservado</th>
                                <th class="px-4 py-3 text-left text-xs font-medium text-muted-foreground uppercase tracking-wider">Expira</th>
                                <th class="px-4 py-3 text-right text-xs font-medium text-muted-foreground uppercase tracking-wider">Acciones</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr 
                                v-for="reservation in reservations.data" 
                                :key="reservation.id"
                                class="hover:bg-muted/30 transition-colors"
                            >
                                <!-- Posición en Cola -->
                                <td class="px-4 py-4">
                                    <div v-if="reservation.status === 'pending' && reservation.queue_position" class="text-center">
                                        <div class="inline-flex items-center justify-center w-10 h-10 bg-yellow-100 dark:bg-yellow-900/30 rounded-full">
                                            <span class="font-bold text-yellow-700 dark:text-yellow-400">#{{ reservation.queue_position }}</span>
                                        </div>
                                        <p class="text-xs text-muted-foreground mt-1">de {{ reservation.total_in_queue }}</p>
                                    </div>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>

                                <!-- Usuario -->
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-purple-100 dark:bg-purple-900/30 rounded-full flex items-center justify-center shrink-0">
                                            <User class="w-5 h-5 text-purple-600 dark:text-purple-400" />
                                        </div>
                                        <div>
                                            <p class="font-medium text-foreground text-sm">{{ reservation.user.name }} {{ reservation.user.last_name }}</p>
                                            <p class="text-xs text-muted-foreground">DNI: {{ reservation.user.dni }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Libro -->
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        <img 
                                            :src="getBookCoverUrl(reservation)" 
                                            :alt="getBookTitle(reservation)"
                                            class="w-10 h-14 object-cover rounded shadow-sm"
                                            @error="(e) => { const img = e.target as HTMLImageElement; if (!img.src.includes('placeholder')) img.src = '/images/book-placeholder.svg'; }"
                                        />
                                        <div class="max-w-xs">
                                            <p class="font-medium text-foreground text-sm line-clamp-2">{{ getBookTitle(reservation) }}</p>
                                            <p v-if="getCopyNumber(reservation)" class="text-xs text-muted-foreground">Copia #{{ getCopyNumber(reservation) }}</p>
                                        </div>
                                    </div>
                                </td>

                                <!-- Estado -->
                                <td class="px-4 py-4">
                                    <span 
                                        :class="getStatusBadge(reservation).class"
                                        class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium whitespace-nowrap"
                                    >
                                        <component :is="getStatusBadge(reservation).icon" class="w-3.5 h-3.5" />
                                        {{ getStatusBadge(reservation).text }}
                                    </span>
                                </td>

                                <!-- Fecha Reserva -->
                                <td class="px-4 py-4 text-sm text-muted-foreground">
                                    {{ formatDateShort(reservation.reservation_date) }}
                                </td>

                                <!-- Fecha Expiración -->
                                <td class="px-4 py-4 text-sm">
                                    <span 
                                        v-if="reservation.status === 'ready'"
                                        :class="getDaysRemaining(reservation.expiration_date) <= 1 
                                            ? 'text-red-600 dark:text-red-400 font-semibold' 
                                            : getDaysRemaining(reservation.expiration_date) <= 2
                                            ? 'text-orange-600 dark:text-orange-400'
                                            : 'text-muted-foreground'"
                                    >
                                        {{ formatDateShort(reservation.expiration_date) }}
                                    </span>
                                    <span v-else class="text-muted-foreground">-</span>
                                </td>

                                <!-- Acciones -->
                                <td class="px-4 py-4 text-right">
                                    <div class="flex justify-end gap-2">
                                        <button
                                            v-for="(actionItem, index) in getAvailableActions(reservation)"
                                            :key="index"
                                            @click="actionItem.action"
                                            :disabled="processingId === reservation.id"
                                            :class="actionItem.color"
                                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-white rounded-lg text-xs font-medium transition-colors disabled:opacity-50 disabled:cursor-not-allowed"
                                        >
                                            <component :is="actionItem.icon" class="w-3.5 h-3.5" />
                                            {{ processingId === reservation.id ? 'Procesando...' : actionItem.label }}
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty State -->
                <div v-if="reservations.data.length === 0" class="p-12 text-center">
                    <Calendar class="w-16 h-16 mx-auto mb-4 text-muted-foreground opacity-50" />
                    <h3 class="text-xl font-semibold text-foreground mb-2">No se encontraron reservas</h3>
                    <p class="text-muted-foreground">
                        {{ hasFilters ? 'Intenta ajustar los filtros' : 'Aún no hay reservas en el sistema' }}
                    </p>
                </div>

                <!-- Paginación -->
                <div v-if="reservations.last_page > 1" class="px-6 py-4 border-t border-border">
                    <div class="flex items-center justify-between">
                        <p class="text-sm text-muted-foreground">
                            Mostrando {{ reservations.data.length }} de {{ reservations.total }} reservas
                        </p>
                        <div class="flex gap-2">
                            <Link
                                v-for="page in reservations.last_page"
                                :key="page"
                                :href="`/admin/reservations?page=${page}`"
                                :class="page === reservations.current_page 
                                    ? 'bg-purple-600 text-white' 
                                    : 'bg-muted text-muted-foreground hover:bg-muted/80'"
                                class="px-3 py-1 rounded-lg text-sm font-medium transition-colors"
                                preserve-state
                            >
                                {{ page }}
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
