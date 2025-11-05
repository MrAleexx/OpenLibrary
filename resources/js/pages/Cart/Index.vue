<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { ShoppingCart, Trash2, AlertCircle, CheckCircle, BookOpen, Calendar, Package } from 'lucide-vue-next';
import { ref, onMounted, computed } from 'vue';
import { useCart, type CartItem } from '@/composables/useCart';

interface Props {
    remainingLoans: number;
    maxLoans: number;
    activeLoansCount: number;
}

const props = defineProps<Props>();

const { 
    count, 
    isEmpty, 
    isLoading, 
    error, 
    getCartItems, 
    removeFromCart, 
    checkout, 
    clearCart 
} = useCart();

const books = ref<CartItem[]>([]);
const showSuccess = ref(false);
const successMessage = ref('');
const isProcessingCheckout = ref(false);

// Load cart items on mount
onMounted(async () => {
    await loadCartItems();
});

const loadCartItems = async () => {
    books.value = await getCartItems();
};

const estimatedDueDate = computed(() => {
    const date = new Date();
    date.setDate(date.getDate() + 14);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
});

const formatDueDate = (dateStr: string): string => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
};

const daysUntilDue = computed(() => {
    return 14; // Always 14 days for new loans
});

const handleRemoveBook = async (bookId: number) => {
    const success = await removeFromCart(bookId);
    if (success) {
        // Reload cart items
        await loadCartItems();
    }
};

const handleCheckout = async () => {
    if (isProcessingCheckout.value) return;
    
    // Confirm before proceeding
    if (!confirm(`¿Confirmas que deseas hacer el préstamo de ${count.value} libro(s)?`)) {
        return;
    }

    isProcessingCheckout.value = true;

    const result = await checkout();

    if (result.success) {
        showSuccess.value = true;
        successMessage.value = result.message || 'Préstamos creados exitosamente';
        
        // Clear local books array
        books.value = [];

        // Redirect to dashboard after 2 seconds
        setTimeout(() => {
            router.visit('/dashboard');
        }, 2000);
    }

    isProcessingCheckout.value = false;
};

const handleClearCart = async () => {
    if (!confirm('¿Estás seguro de que deseas vaciar el carrito?')) {
        return;
    }

    await clearCart();
    books.value = [];
};

const getAuthorNames = (book: CartItem): string => {
    if (!book.contributors || book.contributors.length === 0) {
        return 'Autor desconocido';
    }
    
    if (book.contributors.length === 1) {
        return book.contributors[0].name;
    }
    
    return `${book.contributors[0].name} y ${book.contributors.length - 1} más`;
};
</script>

<template>
    <Head title="Mi Carrito de Préstamos" />

    <AppLayout>
        <div class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
            <!-- Header -->
            <div class="mb-8">
                <div class="flex items-center gap-3 mb-2">
                    <div class="p-2 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500">
                        <ShoppingCart class="w-6 h-6 text-white" />
                    </div>
                    <div>
                        <h1 class="text-3xl font-bold text-gray-900 dark:text-white">
                            Mi Carrito de Préstamos
                        </h1>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            Gestiona los libros que deseas pedir prestados
                        </p>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            <div v-if="showSuccess" class="mb-6 rounded-lg bg-green-50 dark:bg-green-900/20 p-4 border border-green-200 dark:border-green-800">
                <div class="flex items-center gap-3">
                    <CheckCircle class="w-5 h-5 text-green-600 dark:text-green-400" />
                    <div>
                        <p class="text-sm font-medium text-green-800 dark:text-green-300">
                            {{ successMessage }}
                        </p>
                        <p class="text-xs text-green-700 dark:text-green-400 mt-1">
                            Serás redirigido al dashboard...
                        </p>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div v-if="error" class="mb-6 rounded-lg bg-red-50 dark:bg-red-900/20 p-4 border border-red-200 dark:border-red-800">
                <div class="flex items-center gap-3">
                    <AlertCircle class="w-5 h-5 text-red-600 dark:text-red-400" />
                    <p class="text-sm font-medium text-red-800 dark:text-red-300">
                        {{ error }}
                    </p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <!-- Cart Count -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Libros en carrito</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                {{ count }}
                            </p>
                        </div>
                        <div class="p-3 rounded-lg bg-blue-100 dark:bg-blue-900/30">
                            <ShoppingCart class="w-6 h-6 text-blue-600 dark:text-blue-400" />
                        </div>
                    </div>
                </div>

                <!-- Active Loans -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Préstamos activos</p>
                            <p class="text-2xl font-bold text-gray-900 dark:text-white mt-1">
                                {{ activeLoansCount }} / {{ maxLoans }}
                            </p>
                        </div>
                        <div class="p-3 rounded-lg bg-purple-100 dark:bg-purple-900/30">
                            <BookOpen class="w-6 h-6 text-purple-600 dark:text-purple-400" />
                        </div>
                    </div>
                </div>

                <!-- Due Date -->
                <div class="bg-white dark:bg-gray-800 rounded-xl p-6 border border-gray-200 dark:border-gray-700">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">Fecha de devolución</p>
                            <p class="text-sm font-semibold text-gray-900 dark:text-white mt-1">
                                {{ estimatedDueDate }}
                            </p>
                        </div>
                        <div class="p-3 rounded-lg bg-green-100 dark:bg-green-900/30">
                            <Calendar class="w-6 h-6 text-green-600 dark:text-green-400" />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Books List -->
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Libros seleccionados
                        </h2>
                        <button
                            v-if="!isEmpty"
                            @click="handleClearCart"
                            class="text-sm text-red-600 dark:text-red-400 hover:text-red-700 dark:hover:text-red-300 font-medium"
                        >
                            Vaciar carrito
                        </button>
                    </div>

                    <!-- Empty State -->
                    <div v-if="isEmpty && !isLoading" 
                        class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-12 text-center">
                        <Package class="w-16 h-16 mx-auto text-gray-400 dark:text-gray-600 mb-4" />
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">
                            Tu carrito está vacío
                        </h3>
                        <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                            Explora nuestro catálogo y agrega libros para pedir prestados
                        </p>
                        <a
                            href="/books"
                            class="inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg font-medium transition-colors"
                        >
                            <BookOpen class="w-4 h-4" />
                            Explorar Catálogo
                        </a>
                    </div>

                    <!-- Loading State -->
                    <div v-if="isLoading" class="space-y-4">
                        <div v-for="i in 3" :key="i" 
                            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 animate-pulse">
                            <div class="flex gap-4">
                                <div class="w-20 h-28 bg-gray-200 dark:bg-gray-700 rounded-lg"></div>
                                <div class="flex-1 space-y-3">
                                    <div class="h-4 bg-gray-200 dark:bg-gray-700 rounded w-3/4"></div>
                                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-1/2"></div>
                                    <div class="h-3 bg-gray-200 dark:bg-gray-700 rounded w-2/3"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Books -->
                    <div v-else class="space-y-4">
                        <div
                            v-for="book in books"
                            :key="book.id"
                            class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 hover:border-blue-300 dark:hover:border-blue-700 transition-colors"
                        >
                            <div class="flex gap-4">
                                <!-- Book Cover -->
                                <div class="flex-shrink-0">
                                    <img
                                        :src="book.cover_image || '/images/default-book-cover.png'"
                                        :alt="book.title"
                                        class="w-20 h-28 object-cover rounded-lg shadow-md"
                                    />
                                </div>

                                <!-- Book Info -->
                                <div class="flex-1 min-w-0">
                                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-1 truncate">
                                        {{ book.title }}
                                    </h3>
                                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
                                        {{ getAuthorNames(book) }}
                                    </p>

                                    <div class="flex items-center gap-4 text-xs text-gray-600 dark:text-gray-400 mb-3">
                                        <div class="flex items-center gap-1">
                                            <Calendar class="w-3 h-3" />
                                            <span>Devolver: {{ formatDueDate(book.dueDate) }}</span>
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900/30 text-green-800 dark:text-green-300">
                                            14 días de préstamo
                                        </span>
                                    </div>
                                </div>

                                <!-- Remove Button -->
                                <div class="flex-shrink-0">
                                    <button
                                        @click="handleRemoveBook(book.id)"
                                        :disabled="isLoading"
                                        class="p-2 rounded-lg text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors disabled:opacity-50"
                                        title="Remover del carrito"
                                    >
                                        <Trash2 class="w-5 h-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-6 sticky top-4">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-6">
                            Resumen del Préstamo
                        </h3>

                        <div class="space-y-4 mb-6">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Total de libros</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ count }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Préstamos disponibles</span>
                                <span class="font-medium text-gray-900 dark:text-white">{{ remainingLoans }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Periodo de préstamo</span>
                                <span class="font-medium text-gray-900 dark:text-white">14 días</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400">Fecha de devolución</span>
                                <span class="font-medium text-gray-900 dark:text-white">
                                    {{ new Date(Date.now() + 14 * 24 * 60 * 60 * 1000).toLocaleDateString('es-ES', { day: 'numeric', month: 'short' }) }}
                                </span>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 dark:border-gray-700 pt-6 space-y-3">
                            <button
                                @click="handleCheckout"
                                :disabled="isEmpty || isProcessingCheckout || isLoading || remainingLoans < count"
                                class="w-full px-6 py-3 bg-blue-600 hover:bg-blue-700 disabled:bg-gray-300 dark:disabled:bg-gray-700 text-white rounded-lg font-medium transition-colors disabled:cursor-not-allowed flex items-center justify-center gap-2"
                            >
                                <CheckCircle class="w-5 h-5" v-if="!isProcessingCheckout" />
                                <span v-if="isProcessingCheckout">Procesando...</span>
                                <span v-else>Confirmar Préstamo</span>
                            </button>

                            <a
                                href="/books"
                                class="w-full px-6 py-3 bg-gray-100 hover:bg-gray-200 dark:bg-gray-700 dark:hover:bg-gray-600 text-gray-900 dark:text-white rounded-lg font-medium transition-colors flex items-center justify-center gap-2"
                            >
                                <BookOpen class="w-5 h-5" />
                                Seguir Explorando
                            </a>
                        </div>

                        <!-- Warning if exceeds limit -->
                        <div v-if="count > remainingLoans" class="mt-4 p-3 rounded-lg bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800">
                            <div class="flex items-start gap-2">
                                <AlertCircle class="w-4 h-4 text-amber-600 dark:text-amber-400 mt-0.5 flex-shrink-0" />
                                <p class="text-xs text-amber-800 dark:text-amber-300">
                                    Excedes el límite de préstamos. Remueve {{ count - remainingLoans }} libro(s).
                                </p>
                            </div>
                        </div>

                        <!-- Info Note -->
                        <div class="mt-4 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                💡 Los libros deberán ser devueltos en 14 días. Puedes renovar el préstamo si es necesario.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
