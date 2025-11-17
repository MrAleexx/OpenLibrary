<!-- 
    AppSidebar.vue
    
    Componente de barra lateral (sidebar) con navegación adaptativa según roles.
    
    Funcionalidad:
    - Muestra menú diferente según el rol del usuario
    - Usuarios regulares: Solo dashboard personal
    - Admin/Librarian: Menú completo de administración
    
    Características:
    - Colapsable a modo ícono
    - Navegación con Inertia (SPA)
    - Footer con enlaces útiles según rol
    - Avatar y menú de usuario
-->
<script setup lang="ts">
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { useCart } from '@/composables/useCart';
import { dashboard } from '@/routes';
import { type NavItem } from '@/types';
import { Link, usePage } from '@inertiajs/vue3';
import {
    BookMarked,
    BookOpen,
    Calendar,
    CalendarCheck,
    Clock,
    Folder,
    Home,
    LayoutGrid,
    Library,
    ShoppingCart,
    Users,
} from 'lucide-vue-next';
import { computed } from 'vue';
import AppLogo from './AppLogo.vue';

// Definir la interfaz para el usuario y sus roles
interface Role {
    name: string;
    [key: string]: any;
}

interface User {
    roles: Role[];
    [key: string]: any;
}

interface PageProps {
    auth: {
        user: User | null;
    };
    [key: string]: any;
}

const page = usePage<PageProps>();

// ===============================================
// GESTIÓN DE ROLES Y PERMISOS
// ===============================================

/**
 * Obtener datos del usuario autenticado desde Inertia shared props
 */
const user = computed(() => page.props.auth?.user);

/**
 * Extraer array de roles del usuario
 */
const userRoles = computed(() => user.value?.roles || []);

/**
 * Verificar si el usuario tiene privilegios de administración
 *
 * @returns {boolean} true si el usuario es admin o librarian
 */
const isAdminOrLibrarian = computed(() => {
    return userRoles.value.some(
        (role: Role) => role.name === 'admin' || role.name === 'librarian',
    );
});

// ===============================================
// CONFIGURACIÓN DE MENÚS DE NAVEGACIÓN
// ===============================================

const { count } = useCart();

/**
 * Menú de navegación principal para usuarios regulares
 * Solo tienen acceso a su dashboard personal
 */
const userNavItems = computed<NavItem[]>(() => [
    {
        title: 'Mi Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Mi Carrito',
        href: '/cart',
        icon: ShoppingCart,
        badge: count.value > 0 ? count.value.toString() : undefined,
    },
    {
        title: 'Mis Préstamos',
        href: '/loans',
        icon: Clock,
    },
    {
        title: 'Mis Reservas',
        href: '/reservations',
        icon: Calendar,
    },
]);

/**
 * Menú de navegación principal para administradores y bibliotecarios
 * Incluye todas las secciones de gestión del sistema
 */
const adminNavItems = computed<NavItem[]>(() => [
    {
        title: 'Dashboard',
        href: dashboard(),
        icon: LayoutGrid,
    },
    {
        title: 'Categorías',
        href: '/admin/categories',
        icon: Folder,
    },
    {
        title: 'Libros',
        href: '/admin/books',
        icon: BookOpen,
    },
    {
        title: 'Usuarios',
        href: '/admin/users',
        icon: Users,
    },
    {
        title: 'Préstamos',
        href: '/admin/loans',
        icon: BookMarked,
    },
    {
        title: 'Reservas',
        href: '/admin/reservations',
        icon: CalendarCheck,
    },
]);

/**
 * Computed property que retorna el menú apropiado según el rol del usuario
 */
const mainNavItems = computed(() => {
    return isAdminOrLibrarian.value ? adminNavItems.value : userNavItems.value;
});

// ===============================================
// CONFIGURACIÓN DE FOOTER DEL SIDEBAR
// ===============================================

/**
 * Enlaces del footer para usuarios regulares
 * Acceso a páginas públicas del sistema
 */
const userFooterItems: NavItem[] = [
    {
        title: 'Inicio',
        href: '/welcome',
        icon: Home,
    },
    {
        title: 'Catálogo',
        href: '/books',
        icon: Library,
    },
];

/**
 * Enlaces del footer para administradores
 * Incluye enlaces adicionales como repositorio del proyecto
 */
const adminFooterItems: NavItem[] = [
    {
        title: 'Home',
        href: '/welcome',
        icon: Home,
    },
    {
        title: 'Catálogo',
        href: '/books',
        icon: Library,
    },
    {
        title: 'Github Repo',
        href: 'https://github.com/MrAleexx/OpenLibrary/tree/dev',
        icon: Folder,
    },
];

/**
 * Computed property que retorna el footer apropiado según el rol
 */
const footerNavItems = computed(() => {
    return isAdminOrLibrarian.value ? adminFooterItems : userFooterItems;
});
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="dashboard()">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavFooter :items="footerNavItems" />
            <NavUser />
        </SidebarFooter>
        <slot />
    </Sidebar>
</template>
