<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { 
    BookOpen, Download, Calendar, User, Building2, FileText, 
    ChevronLeft, BookMarked, CheckCircle, XCircle, Clock,
    Info, Globe, Hash, Layers, Eye, Star, AlertCircle, ShoppingCart
} from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useCart } from '@/composables/useCart';

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
const { addToCart, removeFromCart, isInCart, isLoading: cartLoading } = useCart();

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
        .filter(c => c.contributor_type === 'author')
        .sort((a, b) => a.sequence_number - b.sequence_number);
});

const editors = computed(() => {
    if (!props.book.contributors || props.book.contributors.length === 0) {
        return [];
    }
    return props.book.contributors
        .filter(c => c.contributor_type === 'editor')
        .sort((a, b) => a.sequence_number - b.sequence_number);
});

const hasPhysicalCopies = computed(() => {
    return props.book.book_type === 'physical' || props.book.book_type === 'both';
});

const isDigital = computed(() => {
    return props.book.downloadable && (props.book.book_type === 'digital' || props.book.book_type === 'both');
});

const canReserve = computed(() => {
    // Solo se puede reservar si:
    // - Usuario autenticado
    // - Es libro físico
    // - NO hay copias disponibles (para esto existe la reserva)
    // - El usuario no tiene ya una reserva
    // - El usuario NO tiene un préstamo activo del libro
    return isAuthenticated.value && 
           hasPhysicalCopies.value && 
           props.availableCopies === 0 && 
           !props.userHasReservation &&
           !props.userHasActiveLoan;
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
    return isAuthenticated.value && 
           hasPhysicalCopies.value && 
           props.availableCopies > 0 && 
           !isInCart(props.book.id) &&
           !props.userHasActiveLoan;
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
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const response = await fetch('/reservations', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
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

        reservationSuccess.value = data.message || 'Reserva creada exitosamente';
        
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
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        // Registrar la descarga en el backend
        const response = await fetch('/downloads', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
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
            alert(data.error || 'Error al descargar el archivo. Por favor intenta de nuevo.');
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
        <div class="p-6 space-y-8">
            <!-- Back Button -->
            <Link
                href="/books"
                class="inline-flex items-center gap-2 text-muted-foreground hover:text-foreground transition-colors"
            >
                <ChevronLeft class="w-4 h-4" />
                Volver al catálogo
            </Link>

            <!-- Main Content -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Left Column: Cover Image -->
                <div class="lg:col-span-1 space-y-6">
                    <div class="sticky top-6">
                        <!-- Cover -->
                        <div class="relative aspect-[2/3] overflow-hidden rounded-xl border border-border bg-muted shadow-2xl">
                            <img
                                :src="coverImageUrl"
                                :alt="book.title"
                                class="w-full h-full object-cover"
                                @error="(e) => { const img = e.target as HTMLImageElement; if (!img.src.includes('placeholder')) img.src = '/images/book-placeholder.svg'; }"
                            />
                            <div v-if="book.featured" class="absolute top-4 right-4">
                                <div class="flex items-center gap-1 px-3 py-1.5 bg-yellow-500 text-white rounded-full shadow-lg">
                                    <Star class="w-4 h-4 fill-current" />
                                    <span class="text-xs font-medium">Destacado</span>
                                </div>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="grid grid-cols-2 gap-3">
                            <div class="p-4 bg-card rounded-lg border border-border text-center">
                                <Eye class="w-5 h-5 text-primary mx-auto mb-2" />
                                <p class="text-2xl font-bold text-foreground">{{ book.total_views || 0 }}</p>
                                <p class="text-xs text-muted-foreground">Vistas</p>
                            </div>
                            <div class="p-4 bg-card rounded-lg border border-border text-center">
                                <Download class="w-5 h-5 text-primary mx-auto mb-2" />
                                <p class="text-2xl font-bold text-foreground">{{ book.total_downloads || 0 }}</p>
                                <p class="text-xs text-muted-foreground">Descargas</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column: Book Details -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Title & Basic Info -->
                    <div>
                        <h1 class="text-4xl font-bold text-foreground mb-4">
                            {{ book.title }}
                        </h1>

                        <!-- Authors -->
                        <div v-if="authors.length > 0" class="flex items-center gap-2 text-lg text-muted-foreground mb-2">
                            <User class="w-5 h-5" />
                            <span>Por {{ authors.map(a => a.full_name).join(', ') }}</span>
                        </div>

                        <!-- Editors -->
                        <div v-if="editors.length > 0" class="flex items-center gap-2 text-sm text-muted-foreground mb-4">
                            <span>Editor(es): {{ editors.map(e => e.full_name).join(', ') }}</span>
                        </div>

                        <!-- Categories -->
                        <div v-if="book.categories && book.categories.length > 0" class="flex flex-wrap gap-2 mb-4">
                            <span
                                v-for="category in book.categories"
                                :key="category.id"
                                class="inline-flex items-center gap-1 px-3 py-1 bg-primary/10 text-primary rounded-full text-sm"
                            >
                                <Layers class="w-3 h-3" />
                                {{ category.name }}
                            </span>
                        </div>

                        <!-- Meta Info -->
                        <div class="flex flex-wrap gap-4 text-sm text-muted-foreground">
                            <span v-if="book.publisher" class="flex items-center gap-1.5">
                                <Building2 class="w-4 h-4" />
                                {{ book.publisher.name }}
                            </span>
                            <span v-if="book.publication_year" class="flex items-center gap-1.5">
                                <Calendar class="w-4 h-4" />
                                {{ book.publication_year }}
                            </span>
                            <span v-if="book.pages" class="flex items-center gap-1.5">
                                <FileText class="w-4 h-4" />
                                {{ book.pages }} páginas
                            </span>
                            <span v-if="book.language" class="flex items-center gap-1.5">
                                <Globe class="w-4 h-4" />
                                {{ book.language.name }}
                            </span>
                            <span v-if="book.isbn" class="flex items-center gap-1.5">
                                <Hash class="w-4 h-4" />
                                ISBN: {{ book.isbn }}
                            </span>
                        </div>
                    </div>

                    <!-- Availability Card -->
                    <div 
                        v-if="availabilityStatus"
                        class="p-6 rounded-xl border transition-all"
                        :class="[availabilityStatus.bgColor, availabilityStatus.borderColor]"
                    >
                        <div class="flex items-start gap-4">
                            <component 
                                :is="availabilityStatus.icon" 
                                class="w-6 h-6 flex-shrink-0 mt-1"
                                :class="availabilityStatus.color"
                            />
                            <div class="flex-1">
                                <h3 class="font-semibold mb-2" :class="availabilityStatus.color">
                                    Disponibilidad
                                </h3>
                                <p class="text-sm mb-4" :class="availabilityStatus.color">
                                    {{ availabilityStatus.text }}
                                </p>

                                <!-- Success Message -->
                                <div v-if="reservationSuccess" class="mb-4 p-3 rounded-lg bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800">
                                    <p class="text-sm text-green-800 dark:text-green-300 flex items-center gap-2">
                                        <CheckCircle class="w-4 h-4" />
                                        {{ reservationSuccess }}
                                    </p>
                                </div>

                                <!-- Error Message -->
                                <div v-if="reservationError" class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800">
                                    <p class="text-sm text-red-800 dark:text-red-300 flex items-center gap-2">
                                        <AlertCircle class="w-4 h-4" />
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
                                        class="inline-flex items-center gap-2 px-6 py-3 rounded-lg font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="canAddToCart 
                                            ? 'bg-primary text-primary-foreground hover:bg-primary/90 shadow-lg hover:shadow-xl' 
                                            : 'bg-muted text-muted-foreground cursor-not-allowed'"
                                    >
                                        <ShoppingCart class="w-5 h-5" />
                                        <span v-if="cartLoading">Agregando...</span>
                                        <span v-else-if="!isAuthenticated">Inicia sesión para obtener</span>
                                        <span v-else-if="props.userHasActiveLoan">Préstamo activo</span>
                                        <span v-else-if="props.availableCopies === 0">No disponible</span>
                                        <span v-else>Agregar al carrito</span>
                                    </button>

                                    <!-- Remove from Cart Button -->
                                    <button
                                        v-if="hasPhysicalCopies && bookInCart"
                                        @click="handleRemoveFromCart"
                                        :disabled="cartLoading"
                                        class="inline-flex items-center gap-2 px-6 py-3 rounded-lg font-medium bg-destructive text-destructive-foreground hover:bg-destructive/90 shadow-lg hover:shadow-xl transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                    >
                                        <CheckCircle class="w-5 h-5" />
                                        <span v-if="cartLoading">Quitando...</span>
                                        <span v-else>En el carrito</span>
                                    </button>

                                    <!-- Reserve Button (Solo cuando NO hay copias disponibles) -->
                                    <button
                                        v-if="hasPhysicalCopies && props.availableCopies === 0"
                                        @click="handleReserve"
                                        :disabled="!canReserve || isReserving"
                                        class="inline-flex items-center gap-2 px-6 py-3 rounded-lg font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="canReserve 
                                            ? 'bg-warning text-warning-foreground hover:bg-warning/90 shadow-lg hover:shadow-xl' 
                                            : 'bg-muted text-muted-foreground cursor-not-allowed'"
                                    >
                                        <BookMarked class="w-5 h-5" />
                                        <span v-if="props.userHasReservation">Ya tienes una reserva</span>
                                        <span v-else-if="props.userHasActiveLoan">Préstamo activo</span>
                                        <span v-else-if="isReserving">Reservando...</span>
                                        <span v-else-if="!isAuthenticated">Inicia sesión para reservar</span>
                                        <span v-else>Reservar para cuando esté disponible</span>
                                    </button>

                                    <!-- Download Button -->
                                    <button
                                        v-if="isDigital"
                                        @click="handleDownload"
                                        :disabled="!canDownload || isDownloading"
                                        class="inline-flex items-center gap-2 px-6 py-3 rounded-lg font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                                        :class="canDownload 
                                            ? 'bg-secondary text-secondary-foreground hover:bg-secondary/90 shadow-lg hover:shadow-xl' 
                                            : 'bg-muted text-muted-foreground cursor-not-allowed'"
                                    >
                                        <Download class="w-5 h-5" />
                                        <span v-if="isDownloading">Descargando...</span>
                                        <span v-else-if="!isAuthenticated">Inicia sesión para descargar</span>
                                        <span v-else>Descargar PDF</span>
                                    </button>
                                </div>

                                <!-- Info Messages -->
                                <div class="mt-4 space-y-2">
                                    <div v-if="!isAuthenticated" class="flex items-start gap-2 text-sm text-muted-foreground">
                                        <Info class="w-4 h-4 mt-0.5 flex-shrink-0" />
                                        <p>
                                            <Link href="/login" class="text-primary hover:underline">Inicia sesión</Link> 
                                            para obtener libros físicos o descargar PDFs
                                        </p>
                                    </div>
                                    <div v-if="hasPhysicalCopies && bookInCart" class="flex items-start gap-2 text-sm">
                                        <CheckCircle class="w-4 h-4 mt-0.5 flex-shrink-0 text-success" />
                                        <p class="text-success">
                                            Este libro está en tu carrito. 
                                            <Link href="/cart" class="underline font-medium">Ver carrito</Link> 
                                            para proceder con el préstamo.
                                        </p>
                                    </div>
                                    <div v-if="hasPhysicalCopies && canAddToCart" class="flex items-start gap-2 text-sm text-muted-foreground">
                                        <Info class="w-4 h-4 mt-0.5 flex-shrink-0" />
                                        <p>Agrégalo al carrito para solicitar el préstamo. El período de préstamo es de 14 días.</p>
                                    </div>
                                    <div v-if="hasPhysicalCopies && canReserve && !bookInCart" class="flex items-start gap-2 text-sm text-muted-foreground">
                                        <Clock class="w-4 h-4 mt-0.5 flex-shrink-0" />
                                        <p>También puedes reservarlo y tendrás 48 horas para recogerlo en la biblioteca</p>
                                    </div>
                                    <div v-if="props.userHasReservation" class="flex items-start gap-2 text-sm">
                                        <AlertCircle class="w-4 h-4 mt-0.5 flex-shrink-0 text-warning" />
                                        <p class="text-warning">
                                            Ya tienes una reserva pendiente de este libro. Visita 
                                            <Link href="/reservations" class="underline">Mis Reservas</Link> 
                                            para más detalles
                                        </p>
                                    </div>
                                    <div v-if="props.userHasActiveLoan" class="flex items-start gap-2 text-sm">
                                        <AlertCircle class="w-4 h-4 mt-0.5 flex-shrink-0 text-destructive" />
                                        <p class="text-destructive">
                                            Actualmente tienes un préstamo activo de este libro. Para solicitarlo nuevamente, primero debes devolverlo. 
                                            <Link href="/loans" class="underline font-medium">Ver mis préstamos</Link>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div v-if="book.details?.description" class="p-6 bg-card rounded-xl border border-border">
                        <h2 class="text-xl font-semibold text-foreground mb-3">Descripción</h2>
                        <p class="text-muted-foreground leading-relaxed whitespace-pre-line">
                            {{ book.details.description }}
                        </p>
                    </div>

                    <!-- Summary -->
                    <div v-if="book.details?.summary" class="p-6 bg-card rounded-xl border border-border">
                        <h2 class="text-xl font-semibold text-foreground mb-3">Resumen</h2>
                        <p class="text-muted-foreground leading-relaxed whitespace-pre-line">
                            {{ book.details.summary }}
                        </p>
                    </div>

                    <!-- Table of Contents -->
                    <div v-if="book.contents && book.contents.length > 0" class="p-6 bg-card rounded-xl border border-border">
                        <h2 class="text-xl font-semibold text-foreground mb-4">Contenido</h2>
                        <div class="space-y-2">
                            <div
                                v-for="content in book.contents"
                                :key="content.chapter_number"
                                class="flex items-start justify-between p-3 rounded-lg hover:bg-muted transition-colors"
                            >
                                <div class="flex-1">
                                    <span class="text-sm font-medium text-primary mr-2">
                                        Capítulo {{ content.chapter_number }}
                                    </span>
                                    <span class="text-foreground">{{ content.title }}</span>
                                </div>
                                <span class="text-sm text-muted-foreground">
                                    Pág. {{ content.page_number }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Additional Notes -->
                    <div v-if="book.details?.notes" class="p-6 bg-muted/50 rounded-xl border border-border">
                        <h2 class="text-xl font-semibold text-foreground mb-3">Notas Adicionales</h2>
                        <p class="text-muted-foreground text-sm leading-relaxed whitespace-pre-line">
                            {{ book.details.notes }}
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
