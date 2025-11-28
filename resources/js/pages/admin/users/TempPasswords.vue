<!-- resources/js/pages/admin/users/TempPasswords.vue -->
<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import type { AppPageProps } from '@/types';
import { Link, router, usePage } from '@inertiajs/vue3';
import {
    AlertTriangle, ArrowLeft, Check, Clock, Copy, Download, Eye, EyeOff, FileText, Key, RefreshCw, Shield, UserCheck, UserPlus, Users,
} from 'lucide-vue-next';
import { computed, onMounted, ref } from 'vue';

// Definir tipos
interface TempPassword {
    row?: number;
    email: string;
    name: string;
    temp_password: string;
    user_id?: number;
    institutional_id: string;
}

interface ImportResults {
    total_rows?: number;
    imported?: number;
    skipped?: number;
    has_errors?: boolean;
}

interface PageProps extends AppPageProps {
    flash?: {
        temp_passwords?: TempPassword[];
        temp_password?: string; // Para creación individual/reset
        import_results?: ImportResults;
        user_created?: {
            name: string;
            email: string;
            institutional_id: string;
        };
        user_reset?: { name: string; email: string; institutional_id: string };
        success?: string;
        error?: string;
    };
}

// Estado
const page = usePage<PageProps>();
const copiedIndex = ref<number | null>(null);
const showPasswords = ref<boolean[]>([]);
const showIndividualPassword = ref(false);

// Computed
const tempPasswords = computed(() => {
    const passwordsFromImport = page.props.flash?.temp_passwords || [];
    const passwordFromIndividual = page.props.flash?.temp_password;

    const allPasswords = [...passwordsFromImport];

    // AGREGAR CONTRASEÑA INDIVIDUAL SI EXISTE
    if (passwordFromIndividual) {
        const userData =
            page.props.flash?.user_created || page.props.flash?.user_reset;
        const actionType = page.props.flash?.user_created
            ? 'creado'
            : 'reseteado';

        allPasswords.push({
            email: userData?.email || 'Usuario individual',
            name: userData?.name || `Usuario ${actionType}`,
            temp_password: passwordFromIndividual,
            institutional_id:
                userData?.institutional_id || 'Asignado al usuario',
        });
    }

    return allPasswords;
});

const importResults = computed(() => page.props.flash?.import_results);
const hasPasswords = computed(() => tempPasswords.value.length > 0);
const isIndividualCreation = computed(
    () =>
        !!page.props.flash?.temp_password && !page.props.flash?.temp_passwords,
);
const actionType = computed(() =>
    page.props.flash?.user_created ? 'creado' : 'reseteado',
);

// Inicializar array de visibilidad de contraseñas
onMounted(() => {
    showPasswords.value = new Array(tempPasswords.value.length).fill(false);
});

// Breadcrumbs
const breadcrumbs = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Usuarios', href: '/admin/users' },
    { title: 'Contraseñas Temporales', href: '#' },
];

// Métodos
function copyToClipboard(text: string, index: number) {
    navigator.clipboard.writeText(text).then(() => {
        copiedIndex.value = index;
        setTimeout(() => {
            copiedIndex.value = null;
        }, 2000);
    });
}

function togglePasswordVisibility(index: number) {
    showPasswords.value[index] = !showPasswords.value[index];
}

function downloadPasswordReport() {
    // DETERMINAR QUÉ RUTA USAR SEGÚN EL CONTEXTO
    const route = page.props.flash?.temp_passwords
        ? '/admin/users/import/passwords-report'
        : '/admin/users/temp-passwords/report';
    globalThis.location.href = route;
}

function goToUsers() {
    router.visit('/admin/users');
}

function goToImport() {
    router.visit('/admin/users/import');
}

// Formatear fecha de expiración
function getExpirationDate() {
    const date = new Date();
    date.setDate(date.getDate() + 7);
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-foreground">
                        Contraseñas Temporales
                    </h1>
                    <p class="mt-2 flex items-center gap-2 text-muted-foreground">
                        <Key class="h-4 w-4 text-primary" />
                        <span v-if="isIndividualCreation">
                            Credenciales para usuario {{ actionType }}
                        </span>
                        <span v-else>
                            Credenciales de acceso para nuevos usuarios
                        </span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Badge variant="outline"
                        class="border-amber-200 bg-amber-500/10 text-amber-600 dark:border-amber-800 dark:bg-amber-900/20 dark:text-amber-400">
                        <Clock class="mr-1 h-3 w-3" />
                        Expiran en 7 días
                    </Badge>
                    <Button variant="outline" as-child>
                        <Link href="/admin/users">
                        <ArrowLeft class="mr-2 h-4 w-4" />
                        Volver a Usuarios
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Alertas de Éxito/Error -->
            <Alert v-if="page.props.flash?.success"
                class="border-green-200 bg-green-50 dark:border-green-900/50 dark:bg-green-900/20">
                <UserCheck class="h-4 w-4 text-green-600 dark:text-green-400" />
                <AlertDescription class="text-green-800 dark:text-green-300">
                    {{ page.props.flash.success }}
                </AlertDescription>
            </Alert>

            <Alert v-if="page.props.flash?.error"
                class="border-red-200 bg-red-50 dark:border-red-900/50 dark:bg-red-900/20">
                <AlertTriangle class="h-4 w-4 text-red-600 dark:text-red-400" />
                <AlertDescription class="text-red-800 dark:text-red-300">
                    {{ page.props.flash.error }}
                </AlertDescription>
            </Alert>

            <!-- Alertas Importantes -->
            <Alert class="border-amber-200 bg-amber-50 dark:border-amber-900/50 dark:bg-amber-900/20">
                <AlertTriangle class="h-4 w-4 text-amber-600 dark:text-amber-400" />
                <AlertDescription class="text-amber-800 dark:text-amber-300">
                    <div class="font-semibold">Información Importante</div>
                    <ul class="mt-1 space-y-1 text-sm">
                        <li>
                            • Las contraseñas son temporales y expiran el
                            {{ getExpirationDate() }}
                        </li>
                        <li>
                            • Los usuarios deben cambiar su contraseña en el
                            primer inicio de sesión
                        </li>
                        <li>
                            • Esta información solo está disponible
                            inmediatamente después de la creación/importación
                        </li>
                        <li>
                            • Guarda o descarga esta información antes de salir
                            de esta página
                        </li>
                    </ul>
                </AlertDescription>
            </Alert>

            <!-- Resultados de Importación (si aplica) -->
            <div v-if="importResults" class="space-y-4">
                <Card class="border-blue-200 bg-blue-50/30 dark:border-blue-900/50 dark:bg-blue-900/10">
                    <CardHeader class="border-b border-blue-200 dark:border-blue-900/50">
                        <CardTitle class="flex items-center gap-2 text-blue-900 dark:text-blue-300">
                            <FileText class="h-5 w-5" />
                            Resumen de Importación
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-6">
                        <div class="grid grid-cols-2 gap-4 text-sm md:grid-cols-4">
                            <div
                                class="rounded-lg border border-blue-100 bg-white p-3 text-center dark:border-blue-800 dark:bg-card">
                                <div class="font-semibold text-blue-900 dark:text-blue-300">
                                    {{ importResults.total_rows || 0 }}
                                </div>
                                <div class="text-xs text-blue-700 dark:text-blue-400">
                                    Total de Filas
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-green-100 bg-white p-3 text-center dark:border-green-800 dark:bg-card">
                                <div class="font-semibold text-green-900 dark:text-green-300">
                                    {{ importResults.imported || 0 }}
                                </div>
                                <div class="text-xs text-green-700 dark:text-green-400">
                                    Usuarios Importados
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-amber-100 bg-white p-3 text-center dark:border-amber-800 dark:bg-card">
                                <div class="font-semibold text-amber-900 dark:text-amber-300">
                                    {{ importResults.skipped || 0 }}
                                </div>
                                <div class="text-xs text-amber-700 dark:text-amber-400">
                                    Filas Omitidas
                                </div>
                            </div>
                            <div
                                class="rounded-lg border border-blue-100 bg-white p-3 text-center dark:border-blue-800 dark:bg-card">
                                <div class="font-semibold text-blue-900 dark:text-blue-300">
                                    {{ tempPasswords.length }}
                                </div>
                                <div class="text-xs text-blue-700 dark:text-blue-400">
                                    Contraseñas Generadas
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Indicador de Creación Individual -->
            <div v-if="isIndividualCreation" class="space-y-4">
                <Card class="border-green-200 bg-green-50/30 dark:border-green-900/50 dark:bg-green-900/10">
                    <CardHeader class="border-b border-green-200 dark:border-green-900/50">
                        <CardTitle class="flex items-center gap-2 text-green-900 dark:text-green-300">
                            <UserPlus v-if="page.props.flash?.user_created" class="h-5 w-5" />
                            <RefreshCw v-if="page.props.flash?.user_reset" class="h-5 w-5" />
                            Usuario
                            {{
                                actionType === 'creado' ? 'Creado' : 'Reseteado'
                            }}
                            Exitosamente
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-6">
                        <div
                            class="flex items-center gap-4 rounded-lg border border-green-100 bg-white p-4 dark:border-green-800 dark:bg-card">
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                                <UserCheck class="h-6 w-6 text-green-600 dark:text-green-400" />
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-green-900 dark:text-green-300">
                                    {{
                                        page.props.flash?.user_created?.name ||
                                        page.props.flash?.user_reset?.name
                                    }}
                                </h3>
                                <div class="mt-1 text-sm text-green-700 dark:text-green-400">
                                    {{
                                        page.props.flash?.user_created?.email ||
                                        page.props.flash?.user_reset?.email
                                    }}
                                </div>
                                <div class="mt-1 text-xs text-green-600 dark:text-green-500">
                                    ID:
                                    {{
                                        page.props.flash?.user_created
                                            ?.institutional_id ||
                                        page.props.flash?.user_reset
                                            ?.institutional_id
                                    }}
                                </div>
                            </div>
                            <Badge variant="outline"
                                class="border-green-200 bg-green-500/10 text-green-600 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
                                {{
                                    actionType === 'creado'
                                        ? 'Nuevo Usuario'
                                        : 'Contraseña Reseteada'
                                }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Lista de Contraseñas -->
            <Card class="border-border shadow-lg">
                <CardHeader class="border-b border-border bg-muted/50">
                    <CardTitle class="flex items-center gap-2 text-foreground">
                        <Shield class="h-5 w-5 text-primary" />
                        Credenciales de Acceso
                        <Badge variant="secondary" class="ml-2">
                            {{ tempPasswords.length }}
                        </Badge>
                    </CardTitle>
                    <CardDescription class="text-muted-foreground">
                        Contraseñas temporales generadas para los nuevos
                        usuarios
                    </CardDescription>
                </CardHeader>

                <CardContent class="p-0">
                    <!-- Estado Vacío -->
                    <div v-if="!hasPasswords" class="py-12 text-center">
                        <div class="mx-auto max-w-md">
                            <div
                                class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-muted p-4">
                                <Key class="h-10 w-10 text-muted-foreground" />
                            </div>
                            <h3 class="mb-3 text-2xl font-bold text-foreground">
                                No hay contraseñas temporales
                            </h3>
                            <p class="mb-6 text-lg text-muted-foreground">
                                Las contraseñas temporales solo están
                                disponibles inmediatamente después de crear o
                                importar usuarios.
                            </p>
                            <div class="flex justify-center gap-3">
                                <Button variant="outline" @click="goToImport">
                                    <Users class="mr-2 h-4 w-4" />
                                    Importar Usuarios
                                </Button>
                                <Button @click="goToUsers"
                                    class="bg-primary text-primary-foreground hover:bg-primary/90">
                                    <UserCheck class="mr-2 h-4 w-4" />
                                    Ver Todos los Usuarios
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Contraseñas -->
                    <div v-else class="divide-y divide-border">
                        <div v-for="(password, index) in tempPasswords" :key="index"
                            class="p-6 transition-colors hover:bg-muted/30">
                            <div class="flex items-start justify-between">
                                <!-- Información del Usuario -->
                                <div class="min-w-0 flex-1">
                                    <div class="mb-3 flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10">
                                            <UserCheck class="h-5 w-5 text-primary" />
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <h3 class="truncate font-semibold text-foreground">
                                                {{ password.name }}
                                            </h3>
                                            <div class="mt-1 flex items-center gap-4 text-sm text-muted-foreground">
                                                <span class="truncate">{{
                                                    password.email
                                                    }}</span>
                                                <span>•</span>
                                                <span class="rounded bg-muted px-2 py-1 font-mono text-xs">
                                                    {{
                                                        password.institutional_id
                                                    }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contraseña -->
                                    <div class="rounded-lg border border-border bg-muted/50 p-4">
                                        <div class="mb-2 flex items-center justify-between">
                                            <Label class="text-sm font-medium text-foreground">Contraseña
                                                Temporal</Label>
                                            <div class="flex items-center gap-2">
                                                <Button variant="ghost" size="sm" @click="
                                                    togglePasswordVisibility(
                                                        index,
                                                    )
                                                    " class="h-8 w-8 p-0">
                                                    <EyeOff v-if="
                                                        showPasswords[index]
                                                    " class="h-4 w-4" />
                                                    <Eye v-else class="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="sm" @click="
                                                    copyToClipboard(
                                                        password.temp_password,
                                                        index,
                                                    )
                                                    " class="h-8 w-8 p-0">
                                                    <Check v-if="
                                                        copiedIndex ===
                                                        index
                                                    " class="h-4 w-4 text-green-600" />
                                                    <Copy v-else class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </div>
                                        <div
                                            class="flex items-center justify-between rounded border border-border bg-background px-3 py-2 font-mono text-lg">
                                            <span>
                                                {{
                                                    showPasswords[index]
                                                        ? password.temp_password
                                                        : '•'.repeat(12)
                                                }}
                                            </span>
                                            <Badge v-if="copiedIndex === index" variant="outline"
                                                class="border-green-200 bg-green-500/10 text-xs text-green-600 dark:border-green-800 dark:bg-green-900/20 dark:text-green-400">
                                                Copiado
                                            </Badge>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>

                <!-- Footer con Acciones -->
                <CardFooter v-if="hasPasswords" class="flex justify-between border-t border-border bg-muted/30 p-6">
                    <div class="text-sm text-muted-foreground">
                        <div class="flex items-center gap-2">
                            <Clock class="h-4 w-4" />
                            <span>Estas contraseñas expiran el
                                {{ getExpirationDate() }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <Button variant="outline" @click="downloadPasswordReport">
                            <Download class="mr-2 h-4 w-4" />
                            Descargar Reporte
                        </Button>
                        <Button @click="goToUsers" class="bg-primary text-primary-foreground hover:bg-primary/90">
                            <UserCheck class="mr-2 h-4 w-4" />
                            Ver Todos los Usuarios
                        </Button>
                    </div>
                </CardFooter>
            </Card>

            <!-- Instrucciones para el Administrador -->
            <Card class="border-blue-200 bg-blue-50/30 dark:border-blue-900/50 dark:bg-blue-900/10">
                <CardHeader>
                    <CardTitle class="flex items-center gap-2 text-blue-900 dark:text-blue-300">
                        <Users class="h-5 w-5" />
                        Instrucciones para Distribuir Credenciales
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 gap-6 text-sm md:grid-cols-2">
                        <div class="space-y-3">
                            <h4 class="flex items-center gap-2 font-semibold text-blue-900 dark:text-blue-300">
                                <UserCheck class="h-4 w-4" />
                                Para Usuarios Individuales
                            </h4>
                            <ul class="space-y-2 text-blue-800 dark:text-blue-200">
                                <li class="flex items-start gap-2">
                                    <div class="mt-2 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-blue-500"></div>
                                    <span>Comparte la contraseña de forma segura
                                        con el usuario</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <div class="mt-2 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-blue-500"></div>
                                    <span>Indica al usuario que debe cambiar la
                                        contraseña en el primer inicio de
                                        sesión</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <div class="mt-2 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-blue-500"></div>
                                    <span>Recuerda que la contraseña expira en 7
                                        días</span>
                                </li>
                            </ul>
                        </div>
                        <div class="space-y-3">
                            <h4 class="flex items-center gap-2 font-semibold text-blue-900 dark:text-blue-300">
                                <FileText class="h-4 w-4" />
                                Para Importaciones Masivas
                            </h4>
                            <ul class="space-y-2 text-blue-800 dark:text-blue-200">
                                <li class="flex items-start gap-2">
                                    <div class="mt-2 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-blue-500"></div>
                                    <span>Descarga el reporte completo en formato
                                        CSV</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <div class="mt-2 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-blue-500"></div>
                                    <span>Distribuye las credenciales de forma
                                        segura a cada usuario</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <div class="mt-2 h-1.5 w-1.5 flex-shrink-0 rounded-full bg-blue-500"></div>
                                    <span>Considera usar un sistema de mensajería
                                        seguro o entrega en persona</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
