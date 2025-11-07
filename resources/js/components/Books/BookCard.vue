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
import { Link, usePage } from '@inertiajs/vue3';
import { BookOpen, Download, Calendar, User, Building2, Eye, BookMarked, ShoppingCart, Check } from 'lucide-vue-next';
import { computed, ref } from 'vue';
import { useCart } from '@/composables/useCart';

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
    available_copies_count?: number;
    physical_copies_count?: number;
    publisher?: {
        name: string;
    };
    contributors?: Array<{
        full_name: string;
        role: string;
    }>;
    categories?: Array<{
        name: string;
        color: string;
    }>;
}

interface Props {
    book: Book;
}

const props = defineProps<Props>();

const { isInCart, addToCart, isLoading } = useCart();
const page = usePage();
const isAuthenticated = computed(() => !!page.props.auth?.user);
const isAddingToCart = ref(false);
const showAddedMessage = ref(false);

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
        .filter(c => c.role === 'author')
        .map(c => c.full_name);
    
    return authorsList.length > 0 ? authorsList.join(', ') : 'Autor desconocido';
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
    const hasPhysical = props.book.book_type === 'physical' || props.book.book_type === 'both';
    const hasDigital = props.book.book_type === 'digital' || props.book.book_type === 'both';
    const available = props.book.available_copies_count || 0;

    // Prioridad 1: Copias físicas disponibles
    if (hasPhysical && available > 0) {
        return {
            text: `${available} ${available === 1 ? 'copia disponible' : 'copias disponibles'}`,
            icon: BookOpen,
            color: 'text-success',
            bgColor: 'bg-success/10',
            borderColor: 'border-success/20',
        };
    } 
    
    // Prioridad 2: Sin copias físicas disponibles
    if (hasPhysical && available === 0) {
        return {
            text: 'Sin copias disponibles',
            icon: BookMarked,
            color: 'text-warning',
            bgColor: 'bg-warning/10',
            borderColor: 'border-warning/20',
        };
    } 
    
    // Prioridad 3: Versión digital disponible
    if (hasDigital) {
        return {
            text: 'PDF Disponible',
            icon: Download,
            color: 'text-primary',
            bgColor: 'bg-primary/10',
            borderColor: 'border-primary/20',
        };
    }

    // Fallback: No disponible
    return {
        text: 'No disponible',
        icon: BookMarked,
        color: 'text-muted-foreground',
        bgColor: 'bg-muted',
        borderColor: 'border-border',
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
    const hasPhysical = props.book.book_type === 'physical' || props.book.book_type === 'both';
    const available = props.book.available_copies_count || 0;
    return hasPhysical && available > 0 && isAuthenticated.value;
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
</script>

<template>
    <Link
        :href="`/books/${book.id}`"
        class="group block bg-card rounded-xl border border-border overflow-hidden hover:border-primary/50 transition-all duration-300 hover:shadow-2xl hover:-translate-y-2"
    >
        <!-- Cover Image -->
        <div class="relative aspect-[2/3] overflow-hidden bg-muted">
            <img
                :src="coverImageUrl"
                :alt="book.title"
                class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500"
                @error="(e) => { const img = e.target as HTMLImageElement; if (!img.src.includes('placeholder')) img.src = '/images/book-placeholder.svg'; }"
            />
            
            <!-- Overlay con efecto hover -->
            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                <div class="absolute bottom-4 left-4 right-4">
                    <p class="text-white text-sm font-medium flex items-center gap-2">
                        <Eye class="w-4 h-4" />
                        {{ book.total_views || 0 }} vistas
                    </p>
                </div>
            </div>

            <!-- Badge de categoría -->
            <div v-if="book.categories && book.categories.length > 0" class="absolute top-3 left-3">
                <span 
                    class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-primary text-primary-foreground shadow-lg"
                >
                    {{ book.categories[0].name }}
                </span>
            </div>
        </div>

        <!-- Content -->
        <div class="p-4 space-y-3">
            <!-- Title -->
            <h3 class="font-semibold text-foreground line-clamp-2 group-hover:text-primary transition-colors min-h-[3rem]">
                {{ book.title }}
            </h3>

            <!-- Author -->
            <p class="text-sm text-muted-foreground flex items-center gap-1.5">
                <User class="w-3.5 h-3.5" />
                <span class="line-clamp-1">{{ authors }}</span>
            </p>

            <!-- Publisher & Year -->
            <div class="flex items-center gap-3 text-xs text-muted-foreground">
                <span v-if="book.publisher" class="flex items-center gap-1">
                    <Building2 class="w-3.5 h-3.5" />
                    {{ book.publisher.name }}
                </span>
                <span v-if="book.publication_year" class="flex items-center gap-1">
                    <Calendar class="w-3.5 h-3.5" />
                    {{ book.publication_year }}
                </span>
            </div>

            <!-- Divider -->
            <div class="border-t border-border pt-3 space-y-2">
                <!-- Availability Badge -->
                <div 
                    class="flex items-center gap-2 px-3 py-2 rounded-lg border transition-colors"
                    :class="[availabilityStatus.bgColor, availabilityStatus.borderColor]"
                >
                    <component 
                        :is="availabilityStatus.icon" 
                        class="w-4 h-4 flex-shrink-0"
                        :class="availabilityStatus.color"
                    />
                    <span class="text-sm font-medium" :class="availabilityStatus.color">
                        {{ availabilityStatus.text }}
                    </span>
                </div>

                <!-- Add to Cart Button -->
                <button
                    v-if="canAddToCart"
                    @click="handleAddToCart"
                    :disabled="isInCart(book.id) || isAddingToCart"
                    class="w-full flex items-center justify-center gap-2 px-3 py-2 rounded-lg text-sm font-medium transition-all disabled:opacity-50 disabled:cursor-not-allowed"
                    :class="isInCart(book.id) 
                        ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400 border border-green-300 dark:border-green-800' 
                        : 'bg-blue-600 hover:bg-blue-700 text-white'"
                >
                    <component :is="isInCart(book.id) ? Check : ShoppingCart" class="w-4 h-4" />
                    <span v-if="isInCart(book.id)">En carrito</span>
                    <span v-else-if="isAddingToCart">Agregando...</span>
                    <span v-else>Agregar al carrito</span>
                </button>
            </div>
        </div>
    </Link>
</template>
