<script setup lang="ts">
import { useCart } from '@/composables/useCart';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    BookMarked,
    Building2,
    Calendar,
    CheckCircle,
    ChevronLeft,
    Clock,
    Download,
    Eye,
    FileText,
    Globe,
    Hash,
    Info,
    Layers,
    ShoppingCart,
    Star,
    User,
    XCircle,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

interface Book {
    id: number;
    title: string;
    isbn: string | null;
    cover_image: string | null;
    cover_url?: string | null;
    pdf_url?: string | null;
    publication_year: number;
    pages: number;
    downloadable: boolean;
    book_type: 'physical' | 'digital' | 'both';
    total_views: number;
    total_downloads: number;
    featured: boolean;
    language_code: string;
    publisher?: {
        id: number;
        name: string;
    };
    language?: {
        code: string;
        name: string;
    };
    contributors?: Array<{
        id: number;
        full_name: string;
        contributor_type: string;
        sequence_number: number;
    }>;
    categories?: Array<{
        id: number;
        name: string;
        color: string;
        description: string | null;
    }>;
    details?: {
        edition: string | null;
        description: string | null;
        summary: string | null;
        notes: string | null;
    };
    contents?: Array<{
        chapter_number: number;
        title: string;
        page_number: number;
    }>;
    physical_copies?: Array<{
        id: number;
        barcode: string;
        status: string;
        condition: string;
    }>;
}

interface Props {
    book: Book;
    availableCopies: number;
    userHasReservation: boolean;
    userHasActiveLoan: boolean;
}

const props = defineProps<Props>();

const page = usePage();
const {
    addToCart,
    removeFromCart,
    isInCart,
    isLoading: cartLoading,
} = useCart();

/**
 * ==============================================================
 * VERIFICACIÓN DE AUTENTICACIÓN CON INERTIA
 * ==============================================================
 *
 * IMPORTANTE: En aplicaciones Inertia.js, NO usar window.Laravel.user
 *
 * ❌ INCORRECTO:
 * const isAuth = !!(window as any).Laravel?.user;
 *
 * ✅ CORRECTO (como está abajo):
 * const page = usePage();
 * const isAuth = !!page.props.auth?.user;
 *
 * Razón: Inertia pasa datos del servidor mediante props compartidos,
 * no mediante variables globales en window.
 *
 * Datos disponibles en page.props.auth:
 * - user: Objeto con datos del usuario autenticado (id, name, email, roles)
 * - null: Si no está autenticado
 *
 * @see https://inertiajs.com/shared-data
 */
const isAuthenticated = computed(() => {
    return !!page.props.auth?.user;
});

const breadcrumbs = [
    { title: 'Inicio', href: '/' },
    { title: 'Catálogo', href: '/books' },
    { title: props.book.title, href: `/books/${props.book.id}` },
];

const authors = computed(() => {
    if (!props.book.contributors || props.book.contributors.length === 0) {
        return [];
    }
    return props.book.contributors
        .filter((c) => c.contributor_type === 'author')
        .sort((a, b) => a.sequence_number - b.sequence_number);
});

const editors = computed(() => {
    if (!props.book.contributors || props.book.contributors.length === 0) {
        return [];
    }
    return props.book.contributors
        .filter((c) => c.contributor_type === 'editor')
        .sort((a, b) => a.sequence_number - b.sequence_number);
});

const hasPhysicalCopies = computed(() => {
    return (
        props.book.book_type === 'physical' || props.book.book_type === 'both'
    );
});

const isDigital = computed(() => {
    return (
        props.book.downloadable &&
        (props.book.book_type === 'digital' || props.book.book_type === 'both')
    );
});

const canReserve = computed(() => {
    // Solo se puede reservar si:
    // - Usuario autenticado
    // - Es libro físico
    // - NO hay copias disponibles (para esto existe la reserva)
    // - El usuario no tiene ya una reserva
    // - El usuario NO tiene un préstamo activo del libro
    return (
        isAuthenticated.value &&
        hasPhysicalCopies.value &&
        props.availableCopies === 0 &&
        !props.userHasReservation &&
        !props.userHasActiveLoan
    );
});

const canDownload = computed(() => {
    return isAuthenticated.value && isDigital.value;
});

const canAddToCart = computed(() => {
    // Solo se puede agregar al carrito si:
    // - Usuario autenticado
    // - Es libro físico
    // - Hay copias disponibles
    // - NO está en el carrito
    // - El usuario NO tiene un préstamo activo del libro
    return (
        isAuthenticated.value &&
        hasPhysicalCopies.value &&
        props.availableCopies > 0 &&
        !isInCart(props.book.id) &&
        !props.userHasActiveLoan
    );
});

const bookInCart = computed(() => isInCart(props.book.id));

const coverImageUrl = computed(() => {
    return props.book.cover_url || '/images/book-placeholder.svg';
});

const availabilityStatus = computed(() => {
    if (hasPhysicalCopies.value && props.availableCopies > 0) {
        return {
            text: `${props.availableCopies} ${props.availableCopies === 1 ? 'copia disponible' : 'copias disponibles'}`,
            icon: CheckCircle,
            color: 'text-success',
            bgColor: 'bg-success/10',
            borderColor: 'border-success/20',
        };
    } else if (hasPhysicalCopies.value && props.availableCopies === 0) {
        return {
            text: 'Sin copias disponibles',
            icon: XCircle,
            color: 'text-destructive',
            bgColor: 'bg-destructive/10',
            borderColor: 'border-destructive/20',
        };
    } else if (isDigital.value) {
        return {
            text: 'Disponible en formato digital',
            icon: Download,
            color: 'text-primary',
            bgColor: 'bg-primary/10',
            borderColor: 'border-primary/20',
        };
    }
    return null;
});

const isReserving = ref(false);
const isDownloading = ref(false);
const reservationError = ref<string | null>(null);
const reservationSuccess = ref<string | null>(null);

const handleAddToCart = async () => {
    if (!canAddToCart.value || cartLoading.value) return;

    try {
        await addToCart(props.book.id);
    } catch (error) {
        console.error('Error adding to cart:', error);
    }
};

const handleRemoveFromCart = async () => {
    if (cartLoading.value) return;

    try {
        await removeFromCart(props.book.id);
    } catch (error) {
        console.error('Error removing from cart:', error);
    }
};

const handleReserve = async () => {
    if (!canReserve.value || isReserving.value) return;

    isReserving.value = true;
    reservationError.value = null;
    reservationSuccess.value = null;

    try {
        const csrfToken =
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content') || '';

        const response = await fetch('/reservations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                book_id: props.book.id,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            reservationError.value = data.error || 'Error al crear la reserva';
            return;
        }

        reservationSuccess.value =
            data.message || 'Reserva creada exitosamente';

        // Redirect to reservations page after 2 seconds
        setTimeout(() => {
            router.visit('/reservations');
        }, 2000);
    } catch (error) {
        console.error('Error creating reservation:', error);
        reservationError.value = 'Error de conexión al crear la reserva';
    } finally {
        isReserving.value = false;
    }
};

const handleDownload = async () => {
    if (!canDownload.value || !props.book.pdf_url) return;

    isDownloading.value = true;

    try {
        const csrfToken =
            document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute('content') || '';

        // Registrar la descarga en el backend
        const response = await fetch('/downloads', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
            body: JSON.stringify({
                book_id: props.book.id,
            }),
        });

        const data = await response.json();

        if (!response.ok) {
            alert(
                data.error ||
                    'Error al descargar el archivo. Por favor intenta de nuevo.',
            );
            return;
        }

        // Iniciar descarga del PDF
        const link = document.createElement('a');
        link.href = props.book.pdf_url;
        link.download = `${props.book.title}.pdf`;
        link.target = '_blank';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    } catch (error) {
        console.error('Error downloading book:', error);
        alert('Error al descargar el archivo. Por favor intenta de nuevo.');
    } finally {
        isDownloading.value = false;
    }
};
</script>

<template>
    <Head :title="book.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-6">
            <!-- Back Button -->
            <Link
                href="/books"
                class="inline-flex items-center gap-2 text-muted-foreground transition-colors hover:text-foreground"
            >
                <ChevronLeft class="h-4 w-4" />
                Volver al catálogo
            </Link>

            <!-- Main Content -->
            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Left Column: Cover Image -->
                <div class="space-y-6 lg:col-span-1">
                    <div class="sticky top-6">
                        <!-- Cover -->
                        <div
                            class="relative aspect-[2/3] overflow-hidden rounded-xl border border-border bg-muted shadow-2xl"
                        >
                            <img
                                :src="coverImageUrl"
                                :alt="book.title"
                                class="h-full w-full object-cover"
                                @error="
                                    (e) => {
                                        const img =
                                            e.target as HTMLImageElement;
                                        if (!img.src.includes('placeholder'))
                                            img.src =
                                                '/images/book-placeholder.svg';
                                    }
                                "
                            />
                            <div
                                v-if="book.featured"
                                class="absolute top-4 right-4"
                            >
                                <div
                                    class="flex items-center gap-1 rounded-full bg-yellow-500 px-3 py-1.5 text-white shadow-lg"
                                >
                                    <Star class="h-4 w-4 fill-current" />
                                    <span class="text-xs font-medium"
                                        >Destacado</span
                                    >
                                </div>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-3">
                            <div
                                class="rounded-lg border border-border bg-card p-4 text-center"
                            >
                                <Eye
                                    class="mx-auto mb-2 h-5 w-5 text-primary"
                                />
                                <p class="text-2xl font-bold text-foreground">
                                    {{ book.total_views || 0 }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Vistas
                                </p>
                            </div>
                            <div
                                class="rounded-lg border border-border bg-card p-4 text-center"
                            >
                                <Download
                                    class="mx-auto mb-2 h-5 w-5 text-primary"
                                />
                                <p class="text-2xl font-bold text-foreground">
                                    {{ book.total_downloads || 0 }}
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Descargas
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Book Details -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Title & Basic Info -->
                    <div>
                        <h1 class="mb-4 text-4xl font-bold text-foreground">
                            {{ book.title }}
                        </h1>

                        <!-- Authors -->
                        <div
                            v-if="authors.length > 0"
                            class="mb-2 flex items-center gap-2 text-lg text-muted-foreground"
                        >
                            <User class="h-5 w-5" />
                            <span
                                >Por
                                {{
                                    authors.map((a) => a.full_name).join(', ')
                                }}</span
                            >
                        </div>

                        <!-- Editors -->
                        <div
                            v-if="editors.length > 0"
                            class="mb-4 flex items-center gap-2 text-sm text-muted-foreground"
                        >
                            <span
                                >Editor(es):
                                {{
                                    editors.map((e) => e.full_name).join(', ')
                                }}</span
                            >
                        </div>

                        <!-- Categories -->
                        <div
                            v-if="book.categories && book.categories.length > 0"
                            class="mb-4 flex flex-wrap gap-2"
                        >
                            <span
                                v-for="category in book.categories"
                                :key="category.id"
                                class="inline-flex items-center gap-1 rounded-full bg-primary/10 px-3 py-1 text-sm text-primary"
                            >
                                <Layers class="h-3 w-3" />
                                {{ category.name }}
                            </span>
                        </div>

                        <!-- Meta Info -->
                        <div
                            class="flex flex-wrap gap-4 text-sm text-muted-foreground"
                        >
                            <span
                                v-if="book.publisher"
                                class="flex items-center gap-1.5"
                            >
                                <Building2 class="h-4 w-4" />
                                {{ book.publisher.name }}
                            </span>
                            <span
                                v-if="book.publication_year"
                                class="flex items-center gap-1.5"
                            >
                                <Calendar class="h-4 w-4" />
                                {{ book.publication_year }}
                            </span>
                            <span
                                v-if="book.pages"
                                class="flex items-center gap-1.5"
                            >
                                <FileText class="h-4 w-4" />
                                {{ book.pages }} páginas
                            </span>
                            <span
                                v-if="book.language"
                                class="flex items-center gap-1.5"
                            >
                                <Globe class="h-4 w-4" />
                                {{ book.language.name }}
                            </span>
                            <span
                                v-if="book.isbn"
                                class="flex items-center gap-1.5"
                            >
                                <Hash class="h-4 w-4" />
                                ISBN: {{ book.isbn }}
                            </span>
                        </div>
                    </div>

                    <!-- Availability Card -->
                    <div
                        v-if="availabilityStatus"
                        class="rounded-xl border p-6 transition-all"
                        :class="[
                            availabilityStatus.bgColor,
                            availabilityStatus.borderColor,
                        ]"
                    >
                        <div class="flex items-start gap-4">
                            <component
                                :is="availabilityStatus.icon"
                                class="mt-1 h-6 w-6 flex-shrink-0"
                                :class="availabilityStatus.color"
                            />
                            <div class="flex-1">
                                <h3
                                    class="mb-2 font-semibold"
                                    :class="availabilityStatus.color"
                                >
                                    Disponibilidad
                                </h3>
                                <p
                                    class="mb-4 text-sm"
                                    :class="availabilityStatus.color"
                                >
                                    {{ availabilityStatus.text }}
                                </p>

                                <!-- Success Message -->
                                <div
                                    v-if="reservationSuccess"
                                    class="mb-4 rounded-lg border border-green-200 bg-green-50 p-3 dark:border-green-800 dark:bg-green-900/20"
                                >
                                    <p
                                        class="flex items-center gap-2 text-sm text-green-800 dark:text-green-300"
                                    >
                                        <CheckCircle class="h-4 w-4" />
                                        {{ reservationSuccess }}
                                    </p>
                                </div>

                                <!-- Error Message -->
                                <div
                                    v-if="reservationError"
                                    class="mb-4 rounded-lg border border-red-200 bg-red-50 p-3 dark:border-red-800 dark:bg-red-900/20"
                                >
                                    <p
                                        class="flex items-center gap-2 text-sm text-red-800 dark:text-red-300"
                                    >
                                        <AlertCircle class="h-4 w-4" />
                                        {{ reservationError }}
                                    </p>
                                </div>

                                <!-- Action Buttons -->
                                <div class="flex flex-wrap gap-3">
                                    <!-- Add to Cart Button (Primary action for physical books) -->
                                    <button
                                        v-if="hasPhysicalCopies && !bookInCart"
                                        @click="handleAddToCart"
                                        :disabled="!canAddToCart || cartLoading"
                                        class="inline-flex items-center gap-2 rounded-lg px-6 py-3 font-medium transition-all disabled:cursor-not-allowed disabled:opacity-50"
                                        :class="
                                            canAddToCart
                                                ? 'bg-primary text-primary-foreground shadow-lg hover:bg-primary/90 hover:shadow-xl'
                                                : 'cursor-not-allowed bg-muted text-muted-foreground'
                                        "
                                    >
                                        <ShoppingCart class="h-5 w-5" />
                                        <span v-if="cartLoading"
                                            >Agregando...</span
                                        >
                                        <span v-else-if="!isAuthenticated"
                                            >Inicia sesión para obtener</span
                                        >
                                        <span
                                            v-else-if="props.userHasActiveLoan"
                                            >Préstamo activo</span
                                        >
                                        <span
                                            v-else-if="
                                                props.availableCopies === 0
                                            "
                                            >No disponible</span
                                        >
                                        <span v-else>Agregar al carrito</span>
                                    </button>

                                    <!-- Remove from Cart Button -->
                                    <button
                                        v-if="hasPhysicalCopies && bookInCart"
                                        @click="handleRemoveFromCart"
                                        :disabled="cartLoading"
                                        class="inline-flex items-center gap-2 rounded-lg bg-destructive px-6 py-3 font-medium text-destructive-foreground shadow-lg transition-all hover:bg-destructive/90 hover:shadow-xl disabled:cursor-not-allowed disabled:opacity-50"
                                    >
                                        <CheckCircle class="h-5 w-5" />
                                        <span v-if="cartLoading"
                                            >Quitando...</span
                                        >
                                        <span v-else>En el carrito</span>
                                    </button>

                                    <!-- Reserve Button (Solo cuando NO hay copias disponibles) -->
                                    <button
                                        v-if="
                                            hasPhysicalCopies &&
                                            props.availableCopies === 0
                                        "
                                        @click="handleReserve"
                                        :disabled="!canReserve || isReserving"
                                        class="inline-flex items-center gap-2 rounded-lg px-6 py-3 font-medium transition-all disabled:cursor-not-allowed disabled:opacity-50"
                                        :class="
                                            canReserve
                                                ? 'bg-warning text-warning-foreground hover:bg-warning/90 shadow-lg hover:shadow-xl'
                                                : 'cursor-not-allowed bg-muted text-muted-foreground'
                                        "
                                    >
                                        <BookMarked class="h-5 w-5" />
                                        <span v-if="props.userHasReservation"
                                            >Ya tienes una reserva</span
                                        >
                                        <span
                                            v-else-if="props.userHasActiveLoan"
                                            >Préstamo activo</span
                                        >
                                        <span v-else-if="isReserving"
                                            >Reservando...</span
                                        >
                                        <span v-else-if="!isAuthenticated"
                                            >Inicia sesión para reservar</span
                                        >
                                        <span v-else
                                            >Reservar para cuando esté
                                            disponible</span
                                        >
                                    </button>

                                    <!-- Download Button -->
                                    <button
                                        v-if="isDigital"
                                        @click="handleDownload"
                                        :disabled="
                                            !canDownload || isDownloading
                                        "
                                        class="inline-flex items-center gap-2 rounded-lg px-6 py-3 font-medium transition-all disabled:cursor-not-allowed disabled:opacity-50"
                                        :class="
                                            canDownload
                                                ? 'bg-secondary text-secondary-foreground shadow-lg hover:bg-secondary/90 hover:shadow-xl'
                                                : 'cursor-not-allowed bg-muted text-muted-foreground'
                                        "
                                    >
                                        <Download class="h-5 w-5" />
                                        <span v-if="isDownloading"
                                            >Descargando...</span
                                        >
                                        <span v-else-if="!isAuthenticated"
                                            >Inicia sesión para descargar</span
                                        >
                                        <span v-else>Descargar PDF</span>
                                    </button>
                                </div>

                                <!-- Info Messages -->
                                <div class="mt-4 space-y-2">
                                    <div
                                        v-if="!isAuthenticated"
                                        class="flex items-start gap-2 text-sm text-muted-foreground"
                                    >
                                        <Info
                                            class="mt-0.5 h-4 w-4 flex-shrink-0"
                                        />
                                        <p>
                                            <Link
                                                href="/login"
                                                class="text-primary hover:underline"
                                                >Inicia sesión</Link
                                            >
                                            para obtener libros físicos o
                                            descargar PDFs
                                        </p>
                                    </div>
                                    <div
                                        v-if="hasPhysicalCopies && bookInCart"
                                        class="flex items-start gap-2 text-sm"
                                    >
                                        <CheckCircle
                                            class="text-success mt-0.5 h-4 w-4 flex-shrink-0"
                                        />
                                        <p class="text-success">
                                            Este libro está en tu carrito.
                                            <Link
                                                href="/cart"
                                                class="font-medium underline"
                                                >Ver carrito</Link
                                            >
                                            para proceder con el préstamo.
                                        </p>
                                    </div>
                                    <div
                                        v-if="hasPhysicalCopies && canAddToCart"
                                        class="flex items-start gap-2 text-sm text-muted-foreground"
                                    >
                                        <Info
                                            class="mt-0.5 h-4 w-4 flex-shrink-0"
                                        />
                                        <p>
                                            Agrégalo al carrito para solicitar
                                            el préstamo. El período de préstamo
                                            es de 14 días.
                                        </p>
                                    </div>
                                    <div
                                        v-if="
                                            hasPhysicalCopies &&
                                            canReserve &&
                                            !bookInCart
                                        "
                                        class="flex items-start gap-2 text-sm text-muted-foreground"
                                    >
                                        <Clock
                                            class="mt-0.5 h-4 w-4 flex-shrink-0"
                                        />
                                        <p>
                                            También puedes reservarlo y tendrás
                                            48 horas para recogerlo en la
                                            biblioteca
                                        </p>
                                    </div>
                                    <div
                                        v-if="props.userHasReservation"
                                        class="flex items-start gap-2 text-sm"
                                    >
                                        <AlertCircle
                                            class="text-warning mt-0.5 h-4 w-4 flex-shrink-0"
                                        />
                                        <p class="text-warning">
                                            Ya tienes una reserva pendiente de
                                            este libro. Visita
                                            <Link
                                                href="/reservations"
                                                class="underline"
                                                >Mis Reservas</Link
                                            >
                                            para más detalles
                                        </p>
                                    </div>
                                    <div
                                        v-if="props.userHasActiveLoan"
                                        class="flex items-start gap-2 text-sm"
                                    >
                                        <AlertCircle
                                            class="mt-0.5 h-4 w-4 flex-shrink-0 text-destructive"
                                        />
                                        <p class="text-destructive">
                                            Actualmente tienes un préstamo
                                            activo de este libro. Para
                                            solicitarlo nuevamente, primero
                                            debes devolverlo.
                                            <Link
                                                href="/loans"
                                                class="font-medium underline"
                                                >Ver mis préstamos</Link
                                            >
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div
                        v-if="book.details?.description"
                        class="rounded-xl border border-border bg-card p-6"
                    >
                        <h2 class="mb-3 text-xl font-semibold text-foreground">
                            Descripción
                        </h2>
                        <p
                            class="leading-relaxed whitespace-pre-line text-muted-foreground"
                        >
                            {{ book.details.description }}
                        </p>
                    </div>

                    <!-- Summary -->
                    <div
                        v-if="book.details?.summary"
                        class="rounded-xl border border-border bg-card p-6"
                    >
                        <h2 class="mb-3 text-xl font-semibold text-foreground">
                            Resumen
                        </h2>
                        <p
                            class="leading-relaxed whitespace-pre-line text-muted-foreground"
                        >
                            {{ book.details.summary }}
                        </p>
                    </div>

                    <!-- Table of Contents -->
                    <div
                        v-if="book.contents && book.contents.length > 0"
                        class="rounded-xl border border-border bg-card p-6"
                    >
                        <h2 class="mb-4 text-xl font-semibold text-foreground">
                            Contenido
                        </h2>
                        <div class="space-y-2">
                            <div
                                v-for="content in book.contents"
                                :key="content.chapter_number"
                                class="flex items-start justify-between rounded-lg p-3 transition-colors hover:bg-muted"
                            >
                                <div class="flex-1">
                                    <span
                                        class="mr-2 text-sm font-medium text-primary"
                                    >
                                        Capítulo {{ content.chapter_number }}
                                    </span>
                                    <span class="text-foreground">{{
                                        content.title
                                    }}</span>
                                </div>
                                <span class="text-sm text-muted-foreground">
                                    Pág. {{ content.page_number }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div
                        v-if="book.details?.notes"
                        class="rounded-xl border border-border bg-muted/50 p-6"
                    >
                        <h2 class="mb-3 text-xl font-semibold text-foreground">
                            Notas Adicionales
                        </h2>
                        <p
                            class="text-sm leading-relaxed whitespace-pre-line text-muted-foreground"
                        >
                            {{ book.details.notes }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
