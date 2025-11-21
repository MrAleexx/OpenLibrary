<!-- resources/js/pages/admin/users/Index.vue -->
<script setup lang="ts">
import FilterBar from '@/components/FilterBar.vue'; // Importar el FilterBar
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import {
    BookOpen,
    Calendar,
    Clock,
    Download,
    Edit,
    Eye,
    FileDown,
    Hash,
    IdCard,
    Library,
    Mail,
    MoreHorizontal,
    RefreshCw,
    Shield,
    TrendingUp,
    User,
    UserCheck,
    UserPlus,
    Users,
    UserX,
    X,
    Zap,
} from 'lucide-vue-next';
import { computed, ref, watch } from 'vue';

// Props with default values
const props = withDefaults(
    defineProps<{
        users: {
            data: Array<{
                id: number;
                name: string;
                last_name: string;
                email: string;
                dni: string;
                institutional_id?: string;
                user_type: string;
                is_active: boolean;
                is_temp_password: boolean;
                membership_expires_at?: string;
                downloads_count: number;
                loans_count: number;
                reservations_count: number;
                created_at: string;
            }>;
            total: number;
            links: Array<{
                url: string | null;
                label: string;
                active: boolean;
            }>;
        };
        filters: {
            search?: string;
            user_type?: string;
            status?: string;
            membership_status?: string;
        };
        stats: {
            total_users: number;
            active_users: number;
            students: number;
            teachers: number;
        };
    }>(),
    {
        users: () => ({
            data: [],
            total: 0,
            links: [],
        }),
        filters: () => ({}),
        stats: () => ({
            total_users: 0,
            active_users: 0,
            students: 0,
            teachers: 0,
        }),
    },
);

// Breadcrumbs
const breadcrumbs = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Usuarios', href: '/admin/users' },
];

// Refs usando el nuevo formato del FilterBar
const filters = ref({
    search: props.filters.search || '',
    user_type: props.filters.user_type || '',
    status: props.filters.status || '',
    membership_status: props.filters.membership_status || '',
});

// Configuración del FilterBar para usuarios
const filterConfig = {
    module: 'users' as const,
};

// Computed

const activeFiltersCount = computed(() => {
    let count = 0;
    if (filters.value.search) count++;
    if (filters.value.user_type) count++;
    if (filters.value.status) count++;
    if (filters.value.membership_status) count++;
    return count;
});

// Watchers con el nuevo formato
watch(
    filters,
    (newFilters) => {
        if (router) {
            router.get(
                '/admin/users',
                {
                    search: newFilters.search,
                    user_type: newFilters.user_type,
                    status: newFilters.status,
                    membership_status: newFilters.membership_status,
                },
                {
                    preserveState: true,
                    replace: true,
                    preserveScroll: true,
                    onError: () => {
                        // Manejar errores silenciosamente para evitar problemas durante el desmontaje
                    },
                },
            );
        }
    },
    { deep: true },
);

// Methods
function clearFilters() {
    filters.value = {
        search: '',
        user_type: '',
        status: '',
        membership_status: '',
    };
}

function toggleActive(user: any) {
    router.patch(`/admin/users/${user.id}/toggle-active`);
}

function resetPassword(user: any) {
    if (
        confirm(
            `¿Estás seguro de que quieres resetear la contraseña de ${user.name}?`,
        )
    ) {
        router.patch(
            `/admin/users/${user.id}/reset-password`,
            {},
            {
                onSuccess: (page) => {
                    if (page.props.temp_password) {
                        // Mostrar modal con contraseña temporal
                        alert(
                            `Contraseña temporal generada: ${page.props.temp_password}\n\nEsta contraseña expirará en 7 días.`,
                        );
                    }
                },
            },
        );
    }
}

// User type labels and colors usando colores CSS personalizados
const userTypeLabels: any = {
    student: 'Estudiante',
    teacher: 'Docente',
    external: 'Externo',
    librarian: 'Bibliotecario',
    admin: 'Administrador',
};

const userTypeColors: any = {
    student:
        'bg-blue-500/10 text-blue-600 border-blue-200 dark:bg-blue-500/20 dark:text-blue-400 dark:border-blue-800',
    teacher:
        'bg-emerald-500/10 text-emerald-600 border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-400 dark:border-emerald-800',
    external:
        'bg-orange-500/10 text-orange-600 border-orange-200 dark:bg-orange-500/20 dark:text-orange-400 dark:border-orange-800',
    librarian:
        'bg-purple-500/10 text-purple-600 border-purple-200 dark:bg-purple-500/20 dark:text-purple-400 dark:border-purple-800',
    admin: 'bg-red-500/10 text-red-600 border-red-200 dark:bg-red-500/20 dark:text-red-400 dark:border-red-800', // ✅ NUEVO
};

const userTypeIcons: any = {
    student: Users,
    teacher: Shield,
    external: User,
    librarian: IdCard,
    admin: Shield,
};

// Format date
function formatDate(date: string) {
    return new Date(date).toLocaleDateString('es-ES');
}

// Check if membership is expired
function isMembershipExpired(user: any) {
    if (!user.membership_expires_at) return false;
    return new Date(user.membership_expires_at) < new Date();
}
</script>

<template>
    <Head>
        <title>Gestión de Usuarios</title>
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-6">
            <!-- Header Mejorado - Consistente con Books -->
            <div class="mb-8">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-foreground">
                            Gestión de Usuarios
                        </h1>
                        <p
                            class="mt-2 flex items-center gap-2 text-muted-foreground"
                        >
                            <Users class="h-4 w-4 text-primary" />
                            Administra los usuarios del sistema bibliotecario
                        </p>
                    </div>
                    <div class="flex items-center gap-4">
                        <Button
                            as-child
                            class="bg-primary text-primary-foreground hover:bg-primary/90"
                        >
                            <Link
                                href="/admin/users/create"
                                class="flex items-center gap-2"
                            >
                                <UserPlus class="h-4 w-4" />
                                Nuevo Usuario
                            </Link>
                        </Button>
                        <div
                            class="flex items-center gap-2 rounded-lg border border-primary/20 bg-primary/10 px-4 py-2 text-primary"
                        >
                            <Zap class="h-4 w-4 animate-pulse" />
                            <span class="text-sm font-medium"
                                >{{ users.data.length }} Usuarios</span
                            >
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards - Consistente con Books -->
            <div
                class="mb-8 grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4"
            >
                <!-- Total Usuarios -->
                <div
                    class="group overflow-hidden rounded-xl border border-border bg-card shadow-lg transition-all duration-500 hover:-translate-y-1 hover:border-primary/30 hover:shadow-xl"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="mb-2 text-sm font-medium text-muted-foreground"
                                >
                                    Total Usuarios
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.total_users }}
                                </p>
                                <div
                                    class="text-success mt-2 flex items-center gap-1 text-xs"
                                >
                                    <TrendingUp class="h-3 w-3" />
                                    <span>+8% este mes</span>
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <Users class="h-6 w-6 text-primary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 w-0 bg-gradient-to-r from-primary to-primary/60 transition-all duration-500 group-hover:w-full"
                    ></div>
                </div>

                <!-- Usuarios Activos -->
                <div
                    class="group overflow-hidden rounded-xl border border-border bg-card shadow-lg transition-all duration-500 hover:-translate-y-1 hover:border-secondary/30 hover:shadow-xl"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="mb-2 text-sm font-medium text-muted-foreground"
                                >
                                    Usuarios Activos
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.active_users }}
                                </p>
                                <div
                                    class="text-success mt-2 flex items-center gap-1 text-xs"
                                >
                                    <TrendingUp class="h-3 w-3" />
                                    <span>+5% este mes</span>
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <UserCheck class="h-6 w-6 text-secondary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 w-0 bg-gradient-to-r from-secondary to-secondary/60 transition-all duration-500 group-hover:w-full"
                    ></div>
                </div>

                <!-- Estudiantes -->
                <div
                    class="group overflow-hidden rounded-xl border border-border bg-card shadow-lg transition-all duration-500 hover:-translate-y-1 hover:border-primary/30 hover:shadow-xl"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="mb-2 text-sm font-medium text-muted-foreground"
                                >
                                    Estudiantes
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.students }}
                                </p>
                                <div
                                    class="mt-2 flex items-center gap-1 text-xs text-muted-foreground"
                                >
                                    <Library class="h-3 w-3" />
                                    <span>Principal grupo</span>
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <Users class="h-6 w-6 text-primary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 w-0 bg-gradient-to-r from-primary to-primary/60 transition-all duration-500 group-hover:w-full"
                    ></div>
                </div>

                <!-- Docentes -->
                <div
                    class="group overflow-hidden rounded-xl border border-border bg-card shadow-lg transition-all duration-500 hover:-translate-y-1 hover:border-secondary/30 hover:shadow-xl"
                >
                    <div class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="mb-2 text-sm font-medium text-muted-foreground"
                                >
                                    Docentes
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.teachers }}
                                </p>
                                <div
                                    class="mt-2 flex items-center gap-1 text-xs text-muted-foreground"
                                >
                                    <Shield class="h-3 w-3" />
                                    <span>Personal académico</span>
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <Shield class="h-6 w-6 text-secondary" />
                            </div>
                        </div>
                    </div>
                    <div
                        class="h-1 w-0 bg-gradient-to-r from-secondary to-secondary/60 transition-all duration-500 group-hover:w-full"
                    ></div>
                </div>
            </div>

            <!-- FilterBar Component -->
            <FilterBar v-model="filters" :config="filterConfig" />

            <!-- Results Count - Consistente con Books -->
            <div class="text-sm text-muted-foreground">
                Mostrando {{ users.data.length }} de {{ users.total }} usuarios
            </div>

            <!-- Users Table - Versión Lista/Table -->
            <Card class="rounded-xl border border-border bg-card shadow-lg">
                <CardHeader>
                    <CardTitle class="text-foreground"
                        >Lista de Usuarios</CardTitle
                    >
                    <CardDescription>
                        {{ users.total }} usuarios encontrados
                    </CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b border-border bg-muted/50">
                                    <th
                                        class="px-6 py-4 text-left font-semibold text-foreground"
                                    >
                                        Usuario
                                    </th>
                                    <th
                                        class="px-6 py-4 text-left font-semibold text-foreground"
                                    >
                                        Tipo
                                    </th>
                                    <th
                                        class="px-6 py-4 text-left font-semibold text-foreground"
                                    >
                                        Contacto
                                    </th>
                                    <th
                                        class="px-6 py-4 text-left font-semibold text-foreground"
                                    >
                                        Estado
                                    </th>
                                    <th
                                        class="px-6 py-4 text-left font-semibold text-foreground"
                                    >
                                        Estadísticas
                                    </th>
                                    <th
                                        class="px-6 py-4 text-left font-semibold text-foreground"
                                    >
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="user in users.data"
                                    :key="user.id"
                                    class="group border-b border-border transition-colors hover:bg-accent/50"
                                >
                                    <!-- Información del Usuario -->
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div
                                                class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10"
                                            >
                                                <User
                                                    class="h-5 w-5 text-primary"
                                                />
                                            </div>
                                            <div>
                                                <p
                                                    class="font-semibold text-foreground transition-colors group-hover:text-primary"
                                                >
                                                    {{ user.name }}
                                                    {{ user.last_name }}
                                                </p>
                                                <div
                                                    class="mt-1 flex items-center gap-2 text-xs text-muted-foreground"
                                                >
                                                    <IdCard class="h-3 w-3" />
                                                    <span
                                                        >DNI:
                                                        {{ user.dni }}</span
                                                    >
                                                    <span
                                                        v-if="
                                                            user.institutional_id
                                                        "
                                                        class="flex items-center gap-1"
                                                    >
                                                        <Hash class="h-3 w-3" />
                                                        {{
                                                            user.institutional_id
                                                        }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Tipo de Usuario -->
                                    <td class="px-6 py-4">
                                        <Badge
                                            :class="
                                                userTypeColors[user.user_type]
                                            "
                                            class="border font-medium"
                                        >
                                            <component
                                                :is="
                                                    userTypeIcons[
                                                        user.user_type
                                                    ]
                                                "
                                                class="mr-1 h-3 w-3"
                                            />
                                            {{ userTypeLabels[user.user_type] }}
                                        </Badge>
                                    </td>

                                    <!-- Contacto -->
                                    <td class="px-6 py-4">
                                        <div class="space-y-1">
                                            <div
                                                class="flex items-center gap-2 text-sm text-muted-foreground"
                                            >
                                                <Mail class="h-3 w-3" />
                                                <span
                                                    class="max-w-[200px] truncate"
                                                    >{{ user.email }}</span
                                                >
                                            </div>
                                            <div
                                                class="text-xs text-muted-foreground"
                                            >
                                                Registro:
                                                {{
                                                    formatDate(user.created_at)
                                                }}
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Estado -->
                                    <td class="px-6 py-4">
                                        <div class="flex flex-wrap gap-1">
                                            <Badge
                                                v-if="!user.is_active"
                                                variant="destructive"
                                                class="text-xs"
                                            >
                                                <UserX class="mr-1 h-3 w-3" />
                                                Inactivo
                                            </Badge>
                                            <Badge
                                                v-else
                                                variant="outline"
                                                class="border-green-200 bg-green-500/10 text-xs text-green-600"
                                            >
                                                <UserCheck
                                                    class="mr-1 h-3 w-3"
                                                />
                                                Activo
                                            </Badge>
                                            <Badge
                                                v-if="isMembershipExpired(user)"
                                                variant="destructive"
                                                class="text-xs"
                                            >
                                                <Clock class="mr-1 h-3 w-3" />
                                                Expirado
                                            </Badge>
                                            <Badge
                                                v-if="user.is_temp_password"
                                                variant="outline"
                                                class="border-amber-200 bg-amber-500/10 text-xs text-amber-600"
                                            >
                                                <Clock class="mr-1 h-3 w-3" />
                                                Temporal
                                            </Badge>
                                        </div>
                                    </td>

                                    <!-- Estadísticas -->
                                    <td class="px-6 py-4">
                                        <div
                                            class="flex items-center gap-4 text-sm text-muted-foreground"
                                        >
                                            <div class="text-center">
                                                <Download
                                                    class="mx-auto mb-1 h-4 w-4 text-blue-500"
                                                />
                                                <span
                                                    class="font-semibold text-foreground"
                                                    >{{
                                                        user.downloads_count
                                                    }}</span
                                                >
                                                <p class="text-xs">Descargas</p>
                                            </div>
                                            <div class="text-center">
                                                <BookOpen
                                                    class="mx-auto mb-1 h-4 w-4 text-emerald-500"
                                                />
                                                <span
                                                    class="font-semibold text-foreground"
                                                    >{{
                                                        user.loans_count
                                                    }}</span
                                                >
                                                <p class="text-xs">Préstamos</p>
                                            </div>
                                            <div class="text-center">
                                                <Calendar
                                                    class="mx-auto mb-1 h-4 w-4 text-purple-500"
                                                />
                                                <span
                                                    class="font-semibold text-foreground"
                                                    >{{
                                                        user.reservations_count
                                                    }}</span
                                                >
                                                <p class="text-xs">Reservas</p>
                                            </div>
                                        </div>
                                    </td>

                                    <!-- Acciones -->
                                    <td class="px-6 py-4">
                                        <DropdownMenu>
                                            <DropdownMenuTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="sm"
                                                    class="h-8 w-8 p-0 opacity-50 transition-opacity duration-200 group-hover:opacity-100"
                                                >
                                                    <MoreHorizontal
                                                        class="h-4 w-4"
                                                    />
                                                </Button>
                                            </DropdownMenuTrigger>
                                            <DropdownMenuContent
                                                align="end"
                                                class="w-48"
                                            >
                                                <DropdownMenuItem as-child>
                                                    <Link
                                                        :href="`/admin/users/${user.id}`"
                                                        class="flex cursor-pointer items-center text-foreground"
                                                    >
                                                        <Eye
                                                            class="mr-2 h-4 w-4 text-blue-500"
                                                        />
                                                        Ver detalles
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem as-child>
                                                    <Link
                                                        :href="`/admin/users/${user.id}/edit`"
                                                        class="flex cursor-pointer items-center text-foreground"
                                                    >
                                                        <Edit
                                                            class="mr-2 h-4 w-4 text-emerald-500"
                                                        />
                                                        Editar usuario
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem
                                                    @click="toggleActive(user)"
                                                    class="cursor-pointer"
                                                >
                                                    <component
                                                        :is="
                                                            user.is_active
                                                                ? UserX
                                                                : UserCheck
                                                        "
                                                        class="mr-2 h-4 w-4 text-orange-500"
                                                    />
                                                    {{
                                                        user.is_active
                                                            ? 'Desactivar'
                                                            : 'Activar'
                                                    }}
                                                </DropdownMenuItem>
                                                <DropdownMenuItem
                                                    @click="resetPassword(user)"
                                                    class="cursor-pointer"
                                                >
                                                    <RefreshCw
                                                        class="mr-2 h-4 w-4 text-purple-500"
                                                    />
                                                    Resetear Contraseña
                                                </DropdownMenuItem>
                                                <DropdownMenuSeparator />
                                                <DropdownMenuItem as-child>
                                                    <Link
                                                        :href="`/admin/users/${user.id}/download-history`"
                                                        class="flex cursor-pointer items-center text-foreground"
                                                    >
                                                        <Download
                                                            class="mr-2 h-4 w-4 text-blue-500"
                                                        />
                                                        Historial Descargas
                                                    </Link>
                                                </DropdownMenuItem>
                                                <DropdownMenuItem as-child>
                                                    <Link
                                                        :href="`/admin/users/${user.id}/loan-history`"
                                                        class="flex cursor-pointer items-center text-foreground"
                                                    >
                                                        <BookOpen
                                                            class="mr-2 h-4 w-4 text-emerald-500"
                                                        />
                                                        Historial Préstamos
                                                    </Link>
                                                </DropdownMenuItem>
                                            </DropdownMenuContent>
                                        </DropdownMenu>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-if="users.data.length === 0"
                        class="m-6 rounded-xl border-2 border-dashed border-border py-16 text-center"
                    >
                        <div class="mx-auto max-w-md">
                            <div
                                class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-primary/10 p-4"
                            >
                                <Users class="h-10 w-10 text-primary" />
                            </div>
                            <h3 class="mb-3 text-2xl font-bold text-foreground">
                                No se encontraron usuarios
                            </h3>
                            <p class="mb-6 text-lg text-muted-foreground">
                                {{
                                    activeFiltersCount > 0
                                        ? 'Intenta ajustar tus filtros de búsqueda'
                                        : 'Comienza importando usuarios o creando uno manualmente'
                                }}
                            </p>
                            <div class="flex justify-center gap-3">
                                <Button
                                    as-child
                                    variant="outline"
                                    class="border-primary/20 text-primary hover:bg-primary hover:text-primary-foreground"
                                >
                                    <Link
                                        href="/admin/users/import"
                                        class="flex items-center gap-2"
                                    >
                                        <FileDown class="h-4 w-4" />
                                        Importar Usuarios
                                    </Link>
                                </Button>
                                <Button
                                    as-child
                                    v-if="activeFiltersCount === 0"
                                    class="inline-flex items-center gap-2 rounded-lg bg-primary px-6 py-3 text-primary-foreground transition-colors hover:bg-primary/90"
                                >
                                    <Link
                                        href="/admin/users/create"
                                        class="flex items-center gap-2"
                                    >
                                        <UserPlus class="h-5 w-5" />
                                        Crear Usuario
                                    </Link>
                                </Button>
                                <Button
                                    v-else
                                    variant="outline"
                                    @click="clearFilters"
                                    class="border-primary/20 text-primary hover:bg-primary hover:text-primary-foreground"
                                >
                                    <X class="mr-2 h-4 w-4" />
                                    Limpiar Filtros
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Pagination - Consistente con Books -->
            <div v-if="users.data.length > 0" class="flex justify-center">
                <div class="flex gap-2">
                    <Link
                        v-for="(link, index) in users.links"
                        :key="index"
                        :href="link.url ?? ''"
                        :disabled="!link.url"
                        :class="[
                            'rounded-lg px-3 py-1.5 text-sm font-medium',
                            link.active
                                ? 'bg-primary text-primary-foreground'
                                : 'border border-input bg-background hover:bg-accent hover:text-accent-foreground',
                            !link.url ? 'cursor-not-allowed opacity-50' : '',
                        ]"
                        v-html="link.label"
                        preserve-scroll
                    />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
