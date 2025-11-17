<script setup lang="ts">
import { useCart, type CartItem } from '@/composables/useCart';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import {
    AlertCircle,
    BookOpen,
    Calendar,
    CheckCircle,
    Package,
    ShoppingCart,
    Trash2,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

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
    clearCart,
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
        day: 'numeric',
    });
});

const formatDueDate = (dateStr: string): string => {
    const date = new Date(dateStr);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
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
    if (
        !confirm(
            `¿Confirmas que deseas hacer el préstamo de ${count.value} libro(s)?`,
        )
    ) {
        return;
    }

    isProcessingCheckout.value = true;

    const result = await checkout();

    if (result.success) {
        showSuccess.value = true;
        successMessage.value =
            result.message || 'Préstamos creados exitosamente';

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
                <div class="mb-2 flex items-center gap-3">
                    <div
                        class="rounded-xl bg-gradient-to-br from-blue-500 to-cyan-500 p-2"
                    >
                        <ShoppingCart class="h-6 w-6 text-white" />
                    </div>
                    <div>
                        <h1
                            class="text-3xl font-bold text-gray-900 dark:text-white"
                        >
                            Mi Carrito de Préstamos
                        </h1>
                        <p
                            class="mt-1 text-sm text-gray-600 dark:text-gray-400"
                        >
                            Gestiona los libros que deseas pedir prestados
                        </p>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            <div
                v-if="showSuccess"
                class="mb-6 rounded-lg border border-green-200 bg-green-50 p-4 dark:border-green-800 dark:bg-green-900/20"
            >
                <div class="flex items-center gap-3">
                    <CheckCircle
                        class="h-5 w-5 text-green-600 dark:text-green-400"
                    />
                    <div>
                        <p
                            class="text-sm font-medium text-green-800 dark:text-green-300"
                        >
                            {{ successMessage }}
                        </p>
                        <p
                            class="mt-1 text-xs text-green-700 dark:text-green-400"
                        >
                            Serás redirigido al dashboard...
                        </p>
                    </div>
                </div>
            </div>

            <!-- Error Message -->
            <div
                v-if="error"
                class="mb-6 rounded-lg border border-red-200 bg-red-50 p-4 dark:border-red-800 dark:bg-red-900/20"
            >
                <div class="flex items-center gap-3">
                    <AlertCircle
                        class="h-5 w-5 text-red-600 dark:text-red-400"
                    />
                    <p
                        class="text-sm font-medium text-red-800 dark:text-red-300"
                    >
                        {{ error }}
                    </p>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="mb-8 grid grid-cols-1 gap-4 md:grid-cols-3">
                <!-- Cart Count -->
                <div
                    class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Libros en carrito
                            </p>
                            <p
                                class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"
                            >
                                {{ count }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-blue-100 p-3 dark:bg-blue-900/30"
                        >
                            <ShoppingCart
                                class="h-6 w-6 text-blue-600 dark:text-blue-400"
                            />
                        </div>
                    </div>
                </div>

                <!-- Active Loans -->
                <div
                    class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Préstamos activos
                            </p>
                            <p
                                class="mt-1 text-2xl font-bold text-gray-900 dark:text-white"
                            >
                                {{ activeLoansCount }} / {{ maxLoans }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-purple-100 p-3 dark:bg-purple-900/30"
                        >
                            <BookOpen
                                class="h-6 w-6 text-purple-600 dark:text-purple-400"
                            />
                        </div>
                    </div>
                </div>

                <!-- Due Date -->
                <div
                    class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                >
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                Fecha de devolución
                            </p>
                            <p
                                class="mt-1 text-sm font-semibold text-gray-900 dark:text-white"
                            >
                                {{ estimatedDueDate }}
                            </p>
                        </div>
                        <div
                            class="rounded-lg bg-green-100 p-3 dark:bg-green-900/30"
                        >
                            <Calendar
                                class="h-6 w-6 text-green-600 dark:text-green-400"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Books List -->
                <div class="space-y-4 lg:col-span-2">
                    <div class="mb-4 flex items-center justify-between">
                        <h2
                            class="text-lg font-semibold text-gray-900 dark:text-white"
                        >
                            Libros seleccionados
                        </h2>
                        <button
                            v-if="!isEmpty"
                            @click="handleClearCart"
                            class="text-sm font-medium text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300"
                        >
                            Vaciar carrito
                        </button>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="isEmpty && !isLoading"
                        class="rounded-xl border border-gray-200 bg-white p-12 text-center dark:border-gray-700 dark:bg-gray-800"
                    >
                        <Package
                            class="mx-auto mb-4 h-16 w-16 text-gray-400 dark:text-gray-600"
                        />
                        <h3
                            class="mb-2 text-lg font-semibold text-gray-900 dark:text-white"
                        >
                            Tu carrito está vacío
                        </h3>
                        <p
                            class="mb-6 text-sm text-gray-600 dark:text-gray-400"
                        >
                            Explora nuestro catálogo y agrega libros para pedir
                            prestados
                        </p>
                        <a
                            href="/books"
                            class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-6 py-3 font-medium text-white transition-colors hover:bg-blue-700"
                        >
                            <BookOpen class="h-4 w-4" />
                            Explorar Catálogo
                        </a>
                    </div>

                    <!-- Loading State -->
                    <div v-if="isLoading" class="space-y-4">
                        <div
                            v-for="i in 3"
                            :key="i"
                            class="animate-pulse rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div class="flex gap-4">
                                <div
                                    class="h-28 w-20 rounded-lg bg-gray-200 dark:bg-gray-700"
                                ></div>
                                <div class="flex-1 space-y-3">
                                    <div
                                        class="h-4 w-3/4 rounded bg-gray-200 dark:bg-gray-700"
                                    ></div>
                                    <div
                                        class="h-3 w-1/2 rounded bg-gray-200 dark:bg-gray-700"
                                    ></div>
                                    <div
                                        class="h-3 w-2/3 rounded bg-gray-200 dark:bg-gray-700"
                                    ></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Books -->
                    <div v-else class="space-y-4">
                        <div
                            v-for="book in books"
                            :key="book.id"
                            class="rounded-xl border border-gray-200 bg-white p-6 transition-colors hover:border-blue-300 dark:border-gray-700 dark:bg-gray-800 dark:hover:border-blue-700"
                        >
                            <div class="flex gap-4">
                                <!-- Book Cover -->
                                <div class="flex-shrink-0">
                                    <img
                                        :src="
                                            book.cover_image ||
                                            '/images/default-book-cover.png'
                                        "
                                        :alt="book.title"
                                        class="h-28 w-20 rounded-lg object-cover shadow-md"
                                    />
                                </div>

                                <!-- Book Info -->
                                <div class="min-w-0 flex-1">
                                    <h3
                                        class="mb-1 truncate text-lg font-semibold text-gray-900 dark:text-white"
                                    >
                                        {{ book.title }}
                                    </h3>
                                    <p
                                        class="mb-2 text-sm text-gray-600 dark:text-gray-400"
                                    >
                                        {{ getAuthorNames(book) }}
                                    </p>

                                    <div
                                        class="mb-3 flex items-center gap-4 text-xs text-gray-600 dark:text-gray-400"
                                    >
                                        <div class="flex items-center gap-1">
                                            <Calendar class="h-3 w-3" />
                                            <span
                                                >Devolver:
                                                {{
                                                    formatDueDate(book.dueDate)
                                                }}</span
                                            >
                                        </div>
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <span
                                            class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/30 dark:text-green-300"
                                        >
                                            14 días de préstamo
                                        </span>
                                    </div>
                                </div>

                                <!-- Remove Button -->
                                <div class="flex-shrink-0">
                                    <button
                                        @click="handleRemoveBook(book.id)"
                                        :disabled="isLoading"
                                        class="rounded-lg p-2 text-red-600 transition-colors hover:bg-red-50 disabled:opacity-50 dark:text-red-400 dark:hover:bg-red-900/20"
                                        title="Remover del carrito"
                                    >
                                        <Trash2 class="h-5 w-5" />
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Summary Sidebar -->
                <div class="lg:col-span-1">
                    <div
                        class="sticky top-4 rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-700 dark:bg-gray-800"
                    >
                        <h3
                            class="mb-6 text-lg font-semibold text-gray-900 dark:text-white"
                        >
                            Resumen del Préstamo
                        </h3>

                        <div class="mb-6 space-y-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400"
                                    >Total de libros</span
                                >
                                <span
                                    class="font-medium text-gray-900 dark:text-white"
                                    >{{ count }}</span
                                >
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400"
                                    >Préstamos disponibles</span
                                >
                                <span
                                    class="font-medium text-gray-900 dark:text-white"
                                    >{{ remainingLoans }}</span
                                >
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400"
                                    >Periodo de préstamo</span
                                >
                                <span
                                    class="font-medium text-gray-900 dark:text-white"
                                    >14 días</span
                                >
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-600 dark:text-gray-400"
                                    >Fecha de devolución</span
                                >
                                <span
                                    class="font-medium text-gray-900 dark:text-white"
                                >
                                    {{
                                        new Date(
                                            Date.now() +
                                                14 * 24 * 60 * 60 * 1000,
                                        ).toLocaleDateString('es-ES', {
                                            day: 'numeric',
                                            month: 'short',
                                        })
                                    }}
                                </span>
                            </div>
                        </div>

                        <div
                            class="space-y-3 border-t border-gray-200 pt-6 dark:border-gray-700"
                        >
                            <button
                                @click="handleCheckout"
                                :disabled="
                                    isEmpty ||
                                    isProcessingCheckout ||
                                    isLoading ||
                                    remainingLoans < count
                                "
                                class="flex w-full items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-3 font-medium text-white transition-colors hover:bg-blue-700 disabled:cursor-not-allowed disabled:bg-gray-300 dark:disabled:bg-gray-700"
                            >
                                <CheckCircle
                                    class="h-5 w-5"
                                    v-if="!isProcessingCheckout"
                                />
                                <span v-if="isProcessingCheckout"
                                    >Procesando...</span
                                >
                                <span v-else>Confirmar Préstamo</span>
                            </button>

                            <a
                                href="/books"
                                class="flex w-full items-center justify-center gap-2 rounded-lg bg-gray-100 px-6 py-3 font-medium text-gray-900 transition-colors hover:bg-gray-200 dark:bg-gray-700 dark:text-white dark:hover:bg-gray-600"
                            >
                                <BookOpen class="h-5 w-5" />
                                Seguir Explorando
                            </a>
                        </div>

                        <!-- Warning if exceeds limit -->
                        <div
                            v-if="count > remainingLoans"
                            class="mt-4 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-800 dark:bg-amber-900/20"
                        >
                            <div class="flex items-start gap-2">
                                <AlertCircle
                                    class="mt-0.5 h-4 w-4 flex-shrink-0 text-amber-600 dark:text-amber-400"
                                />
                                <p
                                    class="text-xs text-amber-800 dark:text-amber-300"
                                >
                                    Excedes el límite de préstamos. Remueve
                                    {{ count - remainingLoans }} libro(s).
                                </p>
                            </div>
                        </div>

                        <!-- Info Note -->
                        <div
                            class="mt-4 rounded-lg border border-blue-200 bg-blue-50 p-3 dark:border-blue-800 dark:bg-blue-900/20"
                        >
                            <p class="text-xs text-blue-800 dark:text-blue-300">
                                💡 Los libros deberán ser devueltos en 14 días.
                                Puedes renovar el préstamo si es necesario.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
