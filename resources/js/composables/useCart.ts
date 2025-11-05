import { ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';

export interface Book {
    id: number;
    title: string;
    cover_image: string;
    cover_url?: string | null;
    pdf_url?: string | null;
    contributors: Array<{ name: string }>;
    publisher?: {
        name: string;
    };
    categories?: Array<{ name: string }>;
    is_active: boolean;
}

export interface CartItem extends Book {
    dueDate: string;
}

const CART_STORAGE_KEY = 'openlibrary_cart';
const MAX_CART_ITEMS = 5;

// Reactive state shared across all component instances
const cartItems = ref<number[]>([]);
const isLoading = ref(false);
const error = ref<string | null>(null);

// Initialize cart from localStorage (will be synced with backend later)
const initializeCart = () => {
    if (typeof window === 'undefined') return;
    
    try {
        const stored = localStorage.getItem(CART_STORAGE_KEY);
        if (stored) {
            const parsed = JSON.parse(stored);
            cartItems.value = Array.isArray(parsed) ? parsed : [];
        }
    } catch (e) {
        console.error('Error loading cart from localStorage:', e);
        cartItems.value = [];
    }
};

// Sync cart with backend session
const syncCartWithBackend = async () => {
    if (typeof window === 'undefined') return;
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        
        const response = await fetch('/cart/items', {
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            credentials: 'same-origin',
        });

        if (response.ok) {
            const data = await response.json();
            const backendBookIds = data.books.map((book: any) => book.id);
            
            // Update local cart with backend data (source of truth)
            cartItems.value = backendBookIds;
            saveCart();
        }
    } catch (e) {
        console.error('Error syncing cart with backend:', e);
    }
};

// Save cart to localStorage
const saveCart = () => {
    if (typeof window === 'undefined') return;
    
    try {
        localStorage.setItem(CART_STORAGE_KEY, JSON.stringify(cartItems.value));
        
        // Dispatch custom event for other components to listen
        window.dispatchEvent(new CustomEvent('cart-updated', {
            detail: { count: cartItems.value.length }
        }));
    } catch (e) {
        console.error('Error saving cart to localStorage:', e);
    }
};

// Track if we've already synced with backend to avoid redundant calls
let hasInitialSync = false;

export function useCart() {
    // Initialize on first use and sync with backend only once
    if (!hasInitialSync) {
        initializeCart();
        // Sync with backend after initialization
        syncCartWithBackend();
        hasInitialSync = true;
    }

    // Computed properties
    const count = computed(() => cartItems.value.length);
    const isEmpty = computed(() => cartItems.value.length === 0);
    const isFull = computed(() => cartItems.value.length >= MAX_CART_ITEMS);
    const remainingSlots = computed(() => Math.max(0, MAX_CART_ITEMS - cartItems.value.length));

    /**
     * Check if a book is in the cart
     */
    const isInCart = (bookId: number): boolean => {
        return cartItems.value.includes(bookId);
    };

    /**
     * Add a book to the cart
     */
    const addToCart = async (bookId: number): Promise<boolean> => {
        isLoading.value = true;
        error.value = null;

        try {
            // Call backend to validate and add to session (backend is source of truth)
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            const response = await fetch(`/cart/add/${bookId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const data = await response.json();

            if (!response.ok) {
                // Detectar sesión expirada (419 CSRF token mismatch)
                if (response.status === 419) {
                    window.location.href = '/login';
                    return false;
                }
                error.value = data.error || 'Error al agregar libro al carrito';
                return false;
            }

            // Sync with backend after successful add
            await syncCartWithBackend();

            return true;

        } catch (e) {
            console.error('Error adding to cart:', e);
            error.value = 'Error de conexión al agregar libro';
            return false;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Remove a book from the cart
     */
    const removeFromCart = async (bookId: number): Promise<boolean> => {
        if (!isInCart(bookId)) {
            return false;
        }

        isLoading.value = true;
        error.value = null;

        try {
            // Call backend
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            const response = await fetch(`/cart/remove/${bookId}`, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const data = await response.json();

            if (!response.ok) {
                // Detectar sesión expirada
                if (response.status === 419) {
                    window.location.href = '/login';
                    return false;
                }
                error.value = data.error || 'Error al remover libro del carrito';
                return false;
            }

            // Sync with backend after successful removal
            await syncCartWithBackend();

            return true;

        } catch (e) {
            console.error('Error removing from cart:', e);
            error.value = 'Error de conexión al remover libro';
            return false;
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Get cart items with full book details
     */
    const getCartItems = async (): Promise<CartItem[]> => {
        if (isEmpty.value) {
            return [];
        }

        isLoading.value = true;
        error.value = null;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            const response = await fetch('/cart/items', {
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            const data = await response.json();

            if (!response.ok) {
                error.value = data.error || 'Error al obtener items del carrito';
                return [];
            }

            // Calculate estimated due dates (14 days from now)
            const dueDate = new Date();
            dueDate.setDate(dueDate.getDate() + 14);
            const dueDateStr = dueDate.toISOString().split('T')[0];

            return data.books.map((book: Book) => ({
                ...book,
                dueDate: dueDateStr,
            }));

        } catch (e) {
            console.error('Error getting cart items:', e);
            error.value = 'Error de conexión al obtener items';
            return [];
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Checkout - create loans from cart
     */
    const checkout = async (): Promise<{ success: boolean; message?: string; error?: string }> => {
        if (isEmpty.value) {
            error.value = 'Tu carrito está vacío';
            return { success: false, error: error.value };
        }

        isLoading.value = true;
        error.value = null;

        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            const response = await fetch('/cart/checkout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
                body: JSON.stringify({
                    book_ids: cartItems.value,
                }),
            });

            const data = await response.json();

            if (!response.ok) {
                // Detectar sesión expirada
                if (response.status === 419) {
                    window.location.href = '/login';
                    return { success: false, error: 'Sesión expirada' };
                }
                error.value = data.error || 'Error al procesar el checkout';
                return { success: false, error: error.value || undefined };
            }

            // Clear cart on success
            clearCart();

            return {
                success: true,
                message: data.message || 'Préstamos creados exitosamente',
            };

        } catch (e) {
            console.error('Error during checkout:', e);
            error.value = 'Error de conexión durante el checkout';
            return { success: false, error: error.value };
        } finally {
            isLoading.value = false;
        }
    };

    /**
     * Clear the entire cart
     */
    const clearCart = async (): Promise<boolean> => {
        try {
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            
            // Call backend to clear session
            const response = await fetch('/cart/clear', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                credentials: 'same-origin',
            });

            if (!response.ok) {
                console.error('Error clearing cart in backend');
            }

            // Clear local state regardless of backend response
            cartItems.value = [];
            saveCart();
            
            return true;

        } catch (e) {
            console.error('Error clearing cart:', e);
            // Still clear local cart even if backend fails
            cartItems.value = [];
            saveCart();
            return false;
        }
    };

    /**
     * Sync with server session on mount
     */
    const syncWithServer = async () => {
        // This can be called when app loads to ensure cart is in sync
        const items = await getCartItems();
        const serverBookIds = items.map(item => item.id);
        
        // Update local cart with server data
        cartItems.value = serverBookIds;
        saveCart();
    };

    return {
        // State
        cartItems: computed(() => cartItems.value),
        count,
        isEmpty,
        isFull,
        remainingSlots,
        isLoading: computed(() => isLoading.value),
        error: computed(() => error.value),

        // Methods
        isInCart,
        addToCart,
        removeFromCart,
        getCartItems,
        checkout,
        clearCart,
        syncWithServer,
    };
}
