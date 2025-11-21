<!-- resources/js/pages/admin/users/Import.vue -->
<script setup lang="ts">
import { Alert, AlertDescription } from '@/components/ui/alert';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Label } from '@/components/ui/label';
import AppLayout from '@/layouts/AppLayout.vue';
import { AppPageProps } from '@/types';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import {
    AlertCircle,
    ArrowLeft,
    CheckCircle,
    Clock,
    Download,
    FileDown,
    FileSearch,
    FileText,
    Loader2,
    TrendingUp,
    Upload,
    UserPlus,
    Users,
    X,
    Zap,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

// Definir tipos para las props de Inertia
interface FlashMessages {
    success?: string;
    error?: string;
    import_results?: {
        total_rows: number;
        imported: number;
        skipped: number;
        errors: Array<{
            row: number;
            field: string;
            error: string;
            value: string;
            type: string;
        }>;
        has_errors: boolean;
    };
    import_errors?: Array<{
        row: number;
        field: string;
        error: string;
        value: string;
    }>;
}

interface PageProps extends AppPageProps {
    flash?: FlashMessages;
}

// Breadcrumbs
const breadcrumbs = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Usuarios', href: '/admin/users' },
    { title: 'Importar Usuarios', href: '#' },
];

// State
const importFile = ref<File | null>(null);
const loading = ref(false);
const fileInput = ref<HTMLInputElement | null>(null);
const page = usePage<PageProps>();

// Computed properties for flash messages and results
const successMessage = computed(() => page.props.flash?.success);
const errorMessage = computed(() => page.props.flash?.error);
const importResults = computed(() => page.props.flash?.import_results);
const importErrors = computed(() => page.props.flash?.import_errors);

// CORRECCIÓN: Usar template ref para el input nativo
function triggerFileInput() {
    const nativeInput = document.getElementById(
        'import_file',
    ) as HTMLInputElement;
    if (nativeInput) {
        nativeInput.click();
    }
}

function handleFileChange(event: Event) {
    const target = event.target as HTMLInputElement;
    if (target.files && target.files[0]) {
        importFile.value = target.files[0];
    }
}

function clearFile() {
    importFile.value = null;
    const nativeInput = document.getElementById(
        'import_file',
    ) as HTMLInputElement;
    if (nativeInput) {
        nativeInput.value = '';
    }
}

function submit() {
    if (!importFile.value) {
        alert('Por favor selecciona un archivo para importar');
        return;
    }

    loading.value = true;

    const formData = new FormData();
    formData.append('import_file', importFile.value);

    router.post('/admin/users/import', formData, {
        preserveScroll: true,
        onSuccess: () => {
            loading.value = false;
            importFile.value = null;
            const nativeInput = document.getElementById(
                'import_file',
            ) as HTMLInputElement;
            if (nativeInput) nativeInput.value = '';
        },
        onError: () => {
            loading.value = false;
        },
    });
}

function downloadTemplate() {
    globalThis.location.href = '/admin/users/import/template';
}

function downloadReport() {
    if (importResults.value?.has_errors) {
        globalThis.location.href = '/admin/users/import/report';
    }
}

function clearImportSession() {
    router.delete('/admin/users/import/session', {
        preserveScroll: true,
        onSuccess: () => {
            router.reload({ only: [] });
        },
    });
}

// Format file size
function formatFileSize(bytes: number) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

// Format row number for display (accounting for header row)
function formatRowNumber(row: number) {
    return row - 1;
}
</script>

<template>
    <Head>
        <title>Importar Usuarios</title>
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-foreground">
                        Importar Usuarios
                    </h1>
                    <p
                        class="mt-2 flex items-center gap-2 text-muted-foreground"
                    >
                        <Users class="h-4 w-4 text-primary" />
                        Importa usuarios masivamente desde un archivo CSV o
                        Excel
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <div
                        class="flex items-center gap-2 rounded-lg border border-primary/20 bg-primary/10 px-4 py-2 text-primary"
                    >
                        <Zap class="h-4 w-4 animate-pulse" />
                        <span class="text-sm font-medium"
                            >Importación Masiva</span
                        >
                    </div>
                    <Button variant="outline" as-child>
                        <Link href="/admin/users">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Volver a Usuarios
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Alert Messages -->
            <div v-if="successMessage || errorMessage" class="space-y-4">
                <Alert
                    v-if="successMessage"
                    class="border-green-200 bg-green-50"
                >
                    <CheckCircle class="h-4 w-4 text-green-600" />
                    <AlertDescription class="text-green-800">
                        {{ successMessage }}
                    </AlertDescription>
                </Alert>

                <Alert v-if="errorMessage" class="border-red-200 bg-red-50">
                    <AlertCircle class="h-4 w-4 text-red-600" />
                    <AlertDescription class="text-red-800">
                        {{ errorMessage }}
                    </AlertDescription>
                </Alert>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Formulario de Importación -->
                <Card class="border-border bg-card shadow-lg lg:col-span-2">
                    <CardHeader class="border-b border-border bg-muted/50">
                        <CardTitle
                            class="flex items-center gap-2 text-foreground"
                        >
                            <Upload class="h-5 w-5 text-primary" />
                            Subir Archivo
                        </CardTitle>
                        <CardDescription class="text-muted-foreground">
                            Selecciona un archivo CSV o Excel con la información
                            de los usuarios
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6 pt-6">
                        <!-- File Upload - CORREGIDO -->
                        <div class="space-y-3">
                            <Label for="import_file" class="text-foreground"
                                >Archivo de Importación *</Label
                            >
                            <div
                                class="cursor-pointer rounded-lg border-2 border-dashed border-border p-6 text-center transition-colors hover:border-primary/50"
                                @click="triggerFileInput"
                            >
                                <!-- ✅ SOLUCIÓN: Input HTML nativo en lugar del componente Shadcn -->
                                <input
                                    id="import_file"
                                    type="file"
                                    accept=".csv,.xlsx,.xls"
                                    @change="handleFileChange"
                                    class="hidden"
                                />
                                <div class="mx-auto max-w-md">
                                    <Upload
                                        class="mx-auto mb-4 h-12 w-12 text-muted-foreground"
                                    />
                                    <p class="mb-2 font-medium text-foreground">
                                        Haz clic para seleccionar un archivo
                                    </p>
                                    <p class="text-sm text-muted-foreground">
                                        Arrastra y suelta tu archivo aquí
                                    </p>
                                    <p
                                        class="mt-2 text-xs text-muted-foreground"
                                    >
                                        Formatos soportados: CSV, Excel (.xlsx,
                                        .xls)
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        Tamaño máximo: 10MB
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- File Preview -->
                        <div
                            v-if="importFile"
                            class="rounded-lg border border-border bg-muted/30 p-4"
                        >
                            <div class="flex items-center gap-4">
                                <div
                                    class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/10"
                                >
                                    <FileText class="h-6 w-6 text-primary" />
                                </div>
                                <div class="min-w-0 flex-1">
                                    <p
                                        class="truncate font-medium text-foreground"
                                    >
                                        {{ importFile.name }}
                                    </p>
                                    <div
                                        class="mt-1 flex items-center gap-4 text-sm text-muted-foreground"
                                    >
                                        <span>{{
                                            formatFileSize(importFile.size)
                                        }}</span>
                                        <span>•</span>
                                        <span>{{
                                            importFile.type || 'Archivo'
                                        }}</span>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Badge
                                        variant="outline"
                                        class="border-blue-200 bg-blue-500/10 text-blue-600"
                                    >
                                        Listo para importar
                                    </Badge>
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="clearFile"
                                        class="h-8 w-8 p-0"
                                    >
                                        <X class="h-4 w-4" />
                                    </Button>
                                </div>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <Button
                            @click="submit"
                            :disabled="!importFile || loading"
                            class="w-full bg-primary text-primary-foreground hover:bg-primary/90"
                            size="lg"
                        >
                            <Loader2
                                v-if="loading"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            <Upload v-else class="mr-2 h-4 w-4" />
                            {{
                                loading
                                    ? 'Procesando...'
                                    : 'Iniciar Importación'
                            }}
                        </Button>

                        <!-- Import Results -->
                        <div v-if="importResults" class="space-y-4">
                            <div
                                class="rounded-lg border p-4"
                                :class="
                                    importResults.has_errors
                                        ? 'border-amber-200 bg-amber-50'
                                        : 'border-green-200 bg-green-50'
                                "
                            >
                                <div class="mb-3 flex items-center gap-3">
                                    <CheckCircle
                                        v-if="!importResults.has_errors"
                                        class="h-5 w-5 text-green-600"
                                    />
                                    <AlertCircle
                                        v-else
                                        class="h-5 w-5 text-amber-600"
                                    />
                                    <h3
                                        class="font-semibold"
                                        :class="
                                            importResults.has_errors
                                                ? 'text-amber-800'
                                                : 'text-green-800'
                                        "
                                    >
                                        Resultado de la Importación
                                    </h3>
                                </div>

                                <div class="grid grid-cols-2 gap-4 text-sm">
                                    <div>
                                        <span class="font-medium"
                                            >Total de filas:</span
                                        >
                                        <span class="ml-2">{{
                                            importResults.total_rows
                                        }}</span>
                                    </div>
                                    <div>
                                        <span class="font-medium"
                                            >Importados:</span
                                        >
                                        <span
                                            class="ml-2 font-semibold text-green-600"
                                            >{{ importResults.imported }}</span
                                        >
                                    </div>
                                    <div>
                                        <span class="font-medium"
                                            >Omitidos:</span
                                        >
                                        <span
                                            class="ml-2 font-semibold text-amber-600"
                                            >{{ importResults.skipped }}</span
                                        >
                                    </div>
                                </div>

                                <!-- Error Details -->
                                <div
                                    v-if="importResults.has_errors"
                                    class="mt-4"
                                >
                                    <div
                                        class="mb-2 flex items-center justify-between"
                                    >
                                        <h4 class="font-medium text-amber-800">
                                            Errores encontrados:
                                        </h4>
                                        <Button
                                            variant="outline"
                                            size="sm"
                                            @click="downloadReport"
                                            class="text-xs"
                                        >
                                            <FileSearch class="mr-1 h-3 w-3" />
                                            Descargar Reporte
                                        </Button>
                                    </div>
                                    <div
                                        class="max-h-40 space-y-2 overflow-y-auto"
                                    >
                                        <div
                                            v-for="(
                                                error, index
                                            ) in importResults.errors.slice(
                                                0,
                                                5,
                                            )"
                                            :key="index"
                                            class="rounded border border-amber-200 bg-white p-2 text-xs"
                                        >
                                            <div class="font-medium">
                                                Fila
                                                {{ formatRowNumber(error.row) }}
                                                - {{ error.field }}
                                            </div>
                                            <div class="text-amber-700">
                                                {{ error.error }}
                                            </div>
                                            <div class="text-gray-500">
                                                Valor: {{ error.value }}
                                            </div>
                                        </div>
                                        <div
                                            v-if="
                                                importResults.errors.length > 5
                                            "
                                            class="py-2 text-center text-xs text-amber-700"
                                        >
                                            ... y
                                            {{
                                                importResults.errors.length - 5
                                            }}
                                            errores más
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Clear Session Button -->
                            <div class="flex justify-end">
                                <Button
                                    variant="outline"
                                    size="sm"
                                    @click="clearImportSession"
                                >
                                    Limpiar Resultados
                                </Button>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Información y Plantilla -->
                <div class="space-y-6">
                    <!-- Template Card -->
                    <Card class="border-border bg-card shadow-lg">
                        <CardHeader class="border-b border-border bg-muted/50">
                            <CardTitle class="text-foreground"
                                >Plantilla</CardTitle
                            >
                            <CardDescription class="text-muted-foreground">
                                Descarga la plantilla oficial
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="pt-6">
                            <div class="text-center">
                                <div
                                    class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-xl bg-primary/10"
                                >
                                    <FileDown class="h-8 w-8 text-primary" />
                                </div>
                                <p class="mb-4 text-sm text-muted-foreground">
                                    Usa nuestra plantilla predefinida para
                                    asegurar el formato correcto
                                </p>
                                <Button
                                    variant="outline"
                                    class="w-full"
                                    @click="downloadTemplate"
                                >
                                    <Download class="mr-2 h-4 w-4" />
                                    Descargar Plantilla CSV
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Format Info Card -->
                    <Card class="border-border bg-card shadow-lg">
                        <CardHeader class="border-b border-border bg-muted/50">
                            <CardTitle class="text-foreground"
                                >Formato Requerido</CardTitle
                            >
                        </CardHeader>
                        <CardContent class="pt-6">
                            <div class="space-y-4">
                                <div>
                                    <h4
                                        class="mb-3 flex items-center gap-2 text-sm font-medium text-foreground"
                                    >
                                        <CheckCircle
                                            class="h-4 w-4 text-green-500"
                                        />
                                        Columnas Obligatorias
                                    </h4>
                                    <ul
                                        class="space-y-2 text-sm text-muted-foreground"
                                    >
                                        <li class="flex items-center gap-2">
                                            <div
                                                class="h-1.5 w-1.5 rounded-full bg-primary"
                                            ></div>
                                            <span
                                                ><strong>name</strong>
                                                (Nombre)</span
                                            >
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <div
                                                class="h-1.5 w-1.5 rounded-full bg-primary"
                                            ></div>
                                            <span
                                                ><strong>last_name</strong>
                                                (Apellido)</span
                                            >
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <div
                                                class="h-1.5 w-1.5 rounded-full bg-primary"
                                            ></div>
                                            <span
                                                ><strong>email</strong> (Email
                                                único)</span
                                            >
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <div
                                                class="h-1.5 w-1.5 rounded-full bg-primary"
                                            ></div>
                                            <span
                                                ><strong>dni</strong> (DNI - 8
                                                dígitos)</span
                                            >
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <div
                                                class="h-1.5 w-1.5 rounded-full bg-primary"
                                            ></div>
                                            <span
                                                ><strong>phone</strong>
                                                (Teléfono - 9 dígitos)</span
                                            >
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <div
                                                class="h-1.5 w-1.5 rounded-full bg-primary"
                                            ></div>
                                            <span
                                                ><strong>user_type</strong>
                                                (student/teacher/external/staff)</span
                                            >
                                        </li>
                                    </ul>
                                </div>

                                <div class="border-t border-border pt-4">
                                    <h4
                                        class="mb-3 flex items-center gap-2 text-sm font-medium text-foreground"
                                    >
                                        <AlertCircle
                                            class="h-4 w-4 text-amber-500"
                                        />
                                        Consideraciones
                                    </h4>
                                    <ul
                                        class="space-y-1 text-xs text-muted-foreground"
                                    >
                                        <li>
                                            • Las contraseñas se generan
                                            automáticamente
                                        </li>
                                        <li>
                                            • Los usuarios reciben email de
                                            verificación
                                        </li>
                                        <li>
                                            • Se asignan permisos según el tipo
                                            de usuario
                                        </li>
                                        <li>
                                            • El sistema valida duplicados
                                            automáticamente
                                        </li>
                                        <li>• Fechas en formato YYYY-MM-DD</li>
                                        <li>
                                            • Booleanos: 1/0, true/false, yes/no
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>

            <!-- Importaciones Recientes -->
            <Card class="border-border bg-card shadow-lg">
                <CardHeader class="border-b border-border bg-muted/50">
                    <CardTitle class="flex items-center gap-2 text-foreground">
                        <TrendingUp class="h-5 w-5 text-primary" />
                        Procesos de Importación
                    </CardTitle>
                    <CardDescription class="text-muted-foreground">
                        Historial y estado de importaciones recientes
                    </CardDescription>
                </CardHeader>
                <CardContent class="pt-6">
                    <div class="py-12 text-center">
                        <div class="mx-auto max-w-md">
                            <div
                                class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-primary/10 p-4"
                            >
                                <Clock class="h-10 w-10 text-primary" />
                            </div>
                            <h3 class="mb-3 text-2xl font-bold text-foreground">
                                No hay importaciones recientes
                            </h3>
                            <p class="mb-6 text-lg text-muted-foreground">
                                Comienza importando tu primer lote de usuarios
                            </p>
                            <div class="flex justify-center gap-3">
                                <Button
                                    variant="outline"
                                    as-child
                                    class="border-primary/20 text-primary hover:bg-primary hover:text-primary-foreground"
                                >
                                    <Link href="/admin/users/create">
                                        <UserPlus class="mr-2 h-4 w-4" />
                                        Crear Usuario Manual
                                    </Link>
                                </Button>
                                <Button
                                    as-child
                                    class="bg-primary text-primary-foreground hover:bg-primary/90"
                                >
                                    <Link href="/admin/users">
                                        <Users class="mr-2 h-4 w-4" />
                                        Ver Todos los Usuarios
                                    </Link>
                                </Button>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
