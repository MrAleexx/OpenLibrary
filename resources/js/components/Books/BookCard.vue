<!-- 
    BookCard.vue
    
    Componente de tarjeta para mostrar información resumida de un libro en el catálogo.
    
    Props:
    - book: Objeto con datos del libro (ver interface Book)
    
    Funcionalidad:
    - Muestra portada del libro con placeholder si no existe
    - Calcula y muestra estado de disponibilidad
    - Extrae y formatea nombres de autores
    - Link al detalle del libro con hover effects
    
    Características:
    - Responsive design
    - Animaciones hover smooth
    - Badges de disponibilidad con colores semánticos
    - Protección contra loops infinitos en carga de imágenes
-->
<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Skeleton } from '@/components/ui/skeleton';
import { useCart } from '@/composables/useCart';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookMarked,
    BookOpen,
    Building2,
    Calendar,
    Check,
    Download,
    Eye,
    ShoppingCart,
    User,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

/**
 * Interface que define la estructura de datos de un libro
 */
interface Book {
    id: number;
    title: string;
    cover_image: string | null;
    cover_url?: string | null;
    pdf_url?: string | null;
    publication_year: number;
    pages: number;
    downloadable: boolean;
    book_type: 'physical' | 'digital' | 'both';
    total_views: number;
    featured?: boolean;
    available_copies_count?: number;
    physical_copies_count?: number;
    publisher?: {
        name: string;
    };
    contributors?: Array<{
        full_name: string;
        contributor_type: string;
    }>;
    categories?: Array<{
        name: string;
        color: string;
    }>;
}

interface Props {
    book: Book;
    userHasLoaned?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    userHasLoaned: false,
});

const { isInCart, addToCart, isLoading } = useCart();
const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
const isAddingToCart = ref(false);
const showAddedMessage = ref(false);
const isImageLoaded = ref(false);
const imageRef = ref<HTMLImageElement | null>(null);

// ===============================================
// COMPUTED PROPERTIES
// ===============================================

/**
 * Extrae y formatea los nombres de autores del libro
 *
 * @returns {string} Lista de autores separados por coma, o "Autor desconocido"
 */
const authors = computed(() => {
    if (!props.book.contributors || props.book.contributors.length === 0) {
        return 'Autor desconocido';
    }

    const authorsList = props.book.contributors
        .filter((c) => c.contributor_type === 'author')
        .map((c) => c.full_name);

    return authorsList.length > 0
        ? authorsList.join(', ')
        : 'Autor desconocido';
});

/**
 * Calcula el estado de disponibilidad del libro
 *
 * Determina el mensaje y estilo visual según:
 * - Tipo de libro (físico, digital, ambos)
 * - Cantidad de copias disponibles
 *
 * @returns {Object} Configuración de badge con texto, icono y colores
 */
const availabilityStatus = computed(() => {
    const hasPhysical =
        props.book.book_type === 'physical' || props.book.book_type === 'both';
    const hasDigital =
        props.book.book_type === 'digital' || props.book.book_type === 'both';
    const available = props.book.available_copies_count || 0;

    // Prioridad 1: Copias físicas disponibles
    if (hasPhysical && available > 0) {
        return {
            text: `${available} ${available === 1 ? 'copia disponible' : 'copias disponibles'}`,
            icon: BookOpen,
            variant: 'secondary' as const,
        };
    }

    // Prioridad 2: Sin copias físicas disponibles
    if (hasPhysical && available === 0) {
        return {
            text: 'Sin copias disponibles',
            icon: BookMarked,
            variant: 'destructive' as const,
        };
    }

    // Prioridad 3: Versión digital disponible
    if (hasDigital) {
        return {
            text: 'PDF Disponible',
            icon: Download,
            variant: 'default' as const,
        };
    }

    // Fallback: No disponible
    return {
        text: 'No disponible',
        icon: BookMarked,
        variant: 'outline' as const,
    };
});

/**
 * URL de la imagen de portada del libro
 * Retorna placeholder SVG si no existe portada
 *
 * @returns {string} URL de la imagen
 */
const coverImageUrl = computed(() => {
    return props.book.cover_url || '/images/book-placeholder.svg';
});

/**
 * Determina si el libro puede ser agregado al carrito
 */
const canAddToCart = computed(() => {
    const hasPhysical =
        props.book.book_type === 'physical' || props.book.book_type === 'both';
    const available = props.book.available_copies_count || 0;
    return (
        hasPhysical &&
        available > 0 &&
        isAuthenticated.value &&
        !props.userHasLoaned
    );
});

/**
 * Handle agregar al carrito
 */
const handleAddToCart = async (e: Event) => {
    e.preventDefault();
    e.stopPropagation();

    if (!canAddToCart.value || isAddingToCart.value) return;

    isAddingToCart.value = true;
    const success = await addToCart(props.book.id);

    if (success) {
        showAddedMessage.value = true;
        setTimeout(() => {
            showAddedMessage.value = false;
        }, 2000);
    }

    isAddingToCart.value = false;
};

const emit = defineEmits(['loaded']);

const onImageLoad = () => {
    isImageLoaded.value = true;
    emit('loaded');
};

onMounted(() => {
    if (imageRef.value?.complete) {
        onImageLoad();
    }
});
</script>

<template>
    <Link :href="`/books/${book.id}`"
        class="group flex h-full flex-col overflow-hidden rounded-xl border border-border bg-card transition-all duration-300 hover:-translate-y-1 hover:border-primary/50 hover:shadow-lg">
    <!-- Cover Image -->
    <div class="relative aspect-[2/3] overflow-hidden bg-muted">
        <!-- Skeleton Loader (Internal fallback) -->
        <Skeleton v-if="!isImageLoaded" class="absolute inset-0 h-full w-full" />

        <img ref="imageRef" :src="coverImageUrl" :alt="book.title" loading="lazy" decoding="async"
            class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
            :class="{ 'opacity-0': !isImageLoaded, 'opacity-100': isImageLoaded }" @load="onImageLoad" @error="
                (e) => {
                    const img = e.target as HTMLImageElement;
                    if (!img.src.includes('placeholder'))
                        img.src = '/images/book-placeholder.svg';
                    isImageLoaded = true;
                    emit('loaded');
                }
            " />

        <!-- Overlay con efecto hover -->
        <div
            class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 transition-opacity duration-300 group-hover:opacity-100">
            <div class="absolute right-4 bottom-4 left-4">
                <p class="flex items-center gap-2 text-sm font-medium text-white">
                    <Eye class="h-4 w-4" />
                    {{ book.total_views || 0 }} vistas
                </p>
            </div>
        </div>

        <!-- Badges Superiores -->
        <div class="absolute top-3 left-3 flex flex-col gap-2">
            <!-- Badge Destacado -->
            <Badge v-if="book.featured" variant="default"
                class="bg-yellow-500 text-white hover:bg-yellow-600 border-none shadow-md">
                Destacado
            </Badge>

            <!-- Badge de categoría -->
            <Badge v-if="book.categories && book.categories.length > 0" variant="outline"
                class="bg-background/90 text-foreground backdrop-blur-sm hover:bg-background/100 shadow-sm border-none">
                {{ book.categories[0].name }}
            </Badge>
        </div>
    </div>

    <!-- Content -->
    <div class="flex flex-1 flex-col space-y-3 p-4">
        <!-- Title -->
        <h3 class="line-clamp-2 min-h-[3rem] font-semibold text-foreground transition-colors group-hover:text-primary"
            :title="book.title">
            {{ book.title }}
        </h3>

        <!-- Author -->
        <p class="flex items-center gap-1.5 text-sm text-muted-foreground">
            <User class="h-3.5 w-3.5 flex-shrink-0" />
            <span class="line-clamp-1">{{ authors }}</span>
        </p>

        <!-- Publisher & Year -->
        <div class="flex items-center gap-3 text-xs text-muted-foreground">
            <span v-if="book.publisher" class="flex items-center gap-1">
                <Building2 class="h-3.5 w-3.5" />
                <span class="line-clamp-1">{{ book.publisher.name }}</span>
            </span>
            <span v-if="book.publication_year" class="flex items-center gap-1">
                <Calendar class="h-3.5 w-3.5" />
                {{ book.publication_year }}
            </span>
        </div>

        <!-- Spacer to push bottom content -->
        <div class="flex-1"></div>

        <!-- Divider -->
        <div class="space-y-2 border-t border-border pt-3">
            <!-- User Has Loaned Badge -->
            <div v-if="userHasLoaned"
                class="flex items-center gap-2 rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 dark:border-blue-800 dark:bg-blue-900/20">
                <BookMarked class="h-4 w-4 flex-shrink-0 text-blue-600 dark:text-blue-400" />
                <span class="text-sm font-medium text-blue-600 dark:text-blue-400">
                    Préstamo activo
                </span>
            </div>

            <!-- Availability Badge -->
            <Badge :variant="availabilityStatus.variant"
                class="flex w-full items-center justify-center gap-2 py-2 text-sm font-medium">
                <component :is="availabilityStatus.icon" class="h-4 w-4 flex-shrink-0" />
                {{ availabilityStatus.text }}
            </Badge>

            <!-- Add to Cart Button -->
            <Button v-if="canAddToCart" @click="handleAddToCart" :disabled="isInCart(book.id) || isAddingToCart"
                class="w-full gap-2" :variant="isInCart(book.id) ? 'outline' : 'default'" :class="{
                    'border-green-500 bg-green-50 text-green-700 hover:bg-green-100 hover:text-green-800 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400 dark:hover:bg-green-900/40': isInCart(book.id)
                }">
                <component :is="isInCart(book.id) ? Check : ShoppingCart" class="h-4 w-4" />
                <span v-if="isInCart(book.id)">En carrito</span>
                <span v-else-if="isAddingToCart">Agregando...</span>
                <span v-else>Agregar</span>
            </Button>
        </div>
    </div>
    </Link>
</template>
