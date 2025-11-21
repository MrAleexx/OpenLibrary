<!-- resources/js/pages/admin/users/Create.vue -->
<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Calendar } from '@/components/ui/calendar';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { format } from 'date-fns';
import { es } from 'date-fns/locale';
import {
    ArrowLeft,
    Calendar as CalendarIcon,
    Info,
    Key,
    Loader2,
    Save,
    Upload,
    User,
    UserPlus,
} from 'lucide-vue-next';
import { reactive, watch } from 'vue';

// Función para formatear la fecha
const formatDate = (date: Date | undefined) => {
    if (!date) return '';
    return format(date, 'PPP', { locale: es });
};

// Props
const props = defineProps<{
    userTypes: any[];
    errors?: Record<string, string>;
}>();

// Breadcrumbs
const breadcrumbs = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Usuarios', href: '/admin/users' },
    { title: 'Crear Usuario', href: '#' },
];

// Form state
const form = reactive({
    name: '',
    last_name: '',
    email: '',
    dni: '',
    phone: '',
    user_type: 'student',
    institutional_email: '',
    membership_expires_at: null as any,
    max_concurrent_loans: 3,
    can_download: true,
    is_active: true,
});

// Información del ID que se generará
const autoIdInfo = reactive({
    prefix: 'EST',
    year: new Date().getFullYear().toString().slice(-2),
    typeName: 'Estudiante',
});

// Watch para actualizar la información del ID automático
watch(
    () => form.user_type,
    (newType) => {
        const prefixes: Record<string, string> = {
            admin: 'ADM',
            librarian: 'BIB',
            student: 'EST',
            teacher: 'DOC',
            external: 'EXT',
        };

        const typeNames: Record<string, string> = {
            admin: 'Administrador',
            librarian: 'Bibliotecario',
            student: 'Estudiante',
            teacher: 'Docente',
            external: 'Externo',
        };

        autoIdInfo.prefix = prefixes[newType] || 'USU';
        autoIdInfo.typeName = typeNames[newType] || 'Usuario';
    },
);

// Loading state
const loading = reactive({
    submitting: false,
});

// Methods
function submit() {
    loading.submitting = true;

    const formData = {
        ...form,
        membership_expires_at: form.membership_expires_at
            ? format(form.membership_expires_at, 'yyyy-MM-dd')
            : null,
        // ✅ NO se envía institutional_id - se genera automáticamente en el backend
    };

    router.post('/admin/users', formData, {
        preserveScroll: true,
        onSuccess: () => {
            loading.submitting = false;
        },
        onError: () => {
            loading.submitting = false;
        },
    });
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Header Simple -->
        <div class="flex items-center gap-4 border-b border-border p-6">
            <div class="flex-1">
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    Crear Nuevo Usuario
                </h1>
                <p class="mt-1 flex items-center gap-2 text-muted-foreground">
                    <UserPlus class="h-4 w-4" />
                    Agrega un nuevo usuario al sistema bibliotecario
                </p>
            </div>
            <Button variant="outline" size="sm" as-child>
                <Link href="/admin/users">
                    <ArrowLeft class="mr-2 h-4 w-4" />
                    Volver a Usuarios
                </Link>
            </Button>
            <Button
                variant="outline"
                as-child
                class="border-primary/20 text-primary hover:bg-primary hover:text-primary-foreground"
            >
                <Link href="/admin/users/import">
                    <Upload class="mr-2 h-4 w-4" />
                    Importar Usuarios
                </Link>
            </Button>
        </div>

        <!-- Contenido del Formulario -->
        <div class="w-full max-w-full p-6">
            <form @submit.prevent="submit" class="w-full space-y-6">
                <!-- Información Personal -->
                <Card class="border-border bg-card shadow-lg">
                    <CardHeader class="border-b border-border bg-muted/50">
                        <CardTitle
                            class="flex items-center gap-2 text-foreground"
                        >
                            <User class="h-5 w-5 text-primary" />
                            Información Personal
                        </CardTitle>
                        <CardDescription class="text-muted-foreground">
                            Información básica del usuario
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 pt-6">
                        <div
                            class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3"
                        >
                            <!-- Nombre -->
                            <div class="space-y-2">
                                <Label for="name" class="text-foreground"
                                    >Nombre *</Label
                                >
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Ingresa el nombre"
                                    class="w-full border-border bg-background focus:border-primary"
                                    required
                                />
                                <InputError :message="errors?.name" />
                            </div>

                            <!-- Apellido -->
                            <div class="space-y-2">
                                <Label for="last_name" class="text-foreground"
                                    >Apellido *</Label
                                >
                                <Input
                                    id="last_name"
                                    v-model="form.last_name"
                                    placeholder="Ingresa el apellido"
                                    class="w-full border-border bg-background focus:border-primary"
                                    required
                                />
                                <InputError :message="errors?.last_name" />
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <Label for="email" class="text-foreground"
                                    >Email *</Label
                                >
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="usuario@ejemplo.com"
                                    class="w-full border-border bg-background focus:border-primary"
                                    required
                                />
                                <InputError :message="errors?.email" />
                            </div>

                            <!-- DNI -->
                            <div class="space-y-2">
                                <Label for="dni" class="text-foreground"
                                    >DNI *</Label
                                >
                                <Input
                                    id="dni"
                                    v-model="form.dni"
                                    placeholder="12345678"
                                    maxlength="8"
                                    class="w-full border-border bg-background focus:border-primary"
                                    required
                                />
                                <InputError :message="errors?.dni" />
                            </div>

                            <!-- Teléfono -->
                            <div class="space-y-2">
                                <Label for="phone" class="text-foreground"
                                    >Teléfono *</Label
                                >
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    placeholder="987654321"
                                    maxlength="9"
                                    class="w-full border-border bg-background focus:border-primary"
                                    required
                                />
                                <InputError :message="errors?.phone" />
                            </div>

                            <!-- Tipo de Usuario -->
                            <div class="space-y-2">
                                <Label for="user_type" class="text-foreground"
                                    >Tipo de Usuario *</Label
                                >
                                <Select v-model="form.user_type">
                                    <SelectTrigger
                                        class="w-full border-border bg-background focus:border-primary"
                                    >
                                        <SelectValue
                                            placeholder="Selecciona el tipo"
                                        />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem
                                            v-for="type in userTypes"
                                            :key="type.value"
                                            :value="type.value"
                                        >
                                            {{ type.label }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                                <InputError :message="errors?.user_type" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Información Institucional -->
                <Card class="border-border bg-card shadow-lg">
                    <CardHeader class="border-b border-border bg-muted/50">
                        <CardTitle class="text-foreground"
                            >Información Institucional</CardTitle
                        >
                        <CardDescription class="text-muted-foreground">
                            Información adicional para usuarios institucionales
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 pt-6">
                        <div
                            class="grid grid-cols-1 gap-6 lg:grid-cols-2 xl:grid-cols-3"
                        >
                            <!-- Email Institucional -->
                            <div class="space-y-2">
                                <Label
                                    for="institutional_email"
                                    class="text-foreground"
                                    >Email Institucional</Label
                                >
                                <Input
                                    id="institutional_email"
                                    v-model="form.institutional_email"
                                    type="email"
                                    placeholder="usuario@institucion.edu.pe"
                                    class="w-full border-border bg-background focus:border-primary"
                                />
                                <InputError
                                    :message="errors?.institutional_email"
                                />
                            </div>

                            <!-- ✅ INFORMACIÓN DEL ID AUTOMÁTICO (NO EDITABLE) -->
                            <div class="space-y-2">
                                <Label class="text-foreground"
                                    >Código Institucional</Label
                                >
                                <div
                                    class="rounded-lg border border-border bg-muted/30 p-3"
                                >
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10"
                                        >
                                            <Key class="h-5 w-5 text-primary" />
                                        </div>
                                        <div class="flex-1">
                                            <div
                                                class="font-semibold text-foreground"
                                            >
                                                {{ autoIdInfo.prefix
                                                }}{{ autoIdInfo.year }}XXX
                                            </div>
                                            <div
                                                class="text-sm text-muted-foreground"
                                            >
                                                ID automático para
                                                {{ autoIdInfo.typeName }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    El sistema generará automáticamente un
                                    código único secuencial
                                </p>
                            </div>

                            <!-- Fecha de Expiración de Membresía -->
                            <div class="space-y-2">
                                <Label
                                    for="membership_expires_at"
                                    class="text-foreground"
                                    >Membresía hasta</Label
                                >
                                <Popover>
                                    <PopoverTrigger as-child>
                                        <Button
                                            variant="outline"
                                            :class="[
                                                'w-full justify-start text-left font-normal',
                                                !form.membership_expires_at &&
                                                    'text-muted-foreground',
                                            ]"
                                        >
                                            <CalendarIcon
                                                class="mr-2 h-4 w-4"
                                            />
                                            {{
                                                form.membership_expires_at
                                                    ? formatDate(
                                                          form.membership_expires_at,
                                                      )
                                                    : 'Selecciona una fecha'
                                            }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-auto p-0">
                                        <Calendar
                                            v-model="form.membership_expires_at"
                                            initialFocus
                                            mode="single"
                                        />
                                    </PopoverContent>
                                </Popover>
                                <InputError
                                    :message="errors?.membership_expires_at"
                                />
                            </div>

                            <!-- Máximo de Préstamos -->
                            <div class="space-y-2">
                                <Label
                                    for="max_concurrent_loans"
                                    class="text-foreground"
                                    >Máximo de Préstamos *</Label
                                >
                                <Input
                                    id="max_concurrent_loans"
                                    v-model="form.max_concurrent_loans"
                                    type="number"
                                    min="1"
                                    max="10"
                                    class="w-full border-border bg-background focus:border-primary"
                                    required
                                />
                                <InputError
                                    :message="errors?.max_concurrent_loans"
                                />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Configuración -->
                <Card class="border-border bg-card shadow-lg">
                    <CardHeader class="border-b border-border bg-muted/50">
                        <CardTitle class="text-foreground"
                            >Configuración</CardTitle
                        >
                        <CardDescription class="text-muted-foreground">
                            Opciones de acceso y permisos del usuario
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 pt-6">
                        <!-- Checkboxes -->
                        <div class="flex max-w-md flex-col gap-4">
                            <div class="flex items-center space-x-3">
                                <Checkbox
                                    id="can_download"
                                    v-model:checked="form.can_download"
                                    class="data-[state=checked]:border-primary data-[state=checked]:bg-primary"
                                />
                                <Label
                                    for="can_download"
                                    class="cursor-pointer text-sm font-normal text-foreground"
                                >
                                    Permitir descarga de libros digitales
                                </Label>
                            </div>
                            <div class="flex items-center space-x-3">
                                <Checkbox
                                    id="is_active"
                                    v-model:checked="form.is_active"
                                    class="data-[state=checked]:border-primary data-[state=checked]:bg-primary"
                                />
                                <Label
                                    for="is_active"
                                    class="cursor-pointer text-sm font-normal text-foreground"
                                >
                                    Usuario activo en el sistema
                                </Label>
                            </div>
                        </div>
                        <InputError :message="errors?.can_download" />
                        <InputError :message="errors?.is_active" />
                    </CardContent>
                </Card>

                <!-- Información del Sistema -->
                <Card
                    class="border-blue-200 border-border bg-blue-50/30 bg-card shadow-lg"
                >
                    <CardHeader class="border-b border-blue-200">
                        <CardTitle
                            class="flex items-center gap-2 text-blue-900"
                        >
                            <Info class="h-5 w-5" />
                            Información del Sistema
                        </CardTitle>
                        <CardDescription class="text-blue-700">
                            Características automáticas del nuevo usuario
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pt-6">
                        <div
                            class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2"
                        >
                            <div
                                class="flex items-center gap-3 rounded-lg border border-blue-100 bg-white p-3"
                            >
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100"
                                >
                                    <Key class="h-4 w-4 text-blue-600" />
                                </div>
                                <div>
                                    <div class="font-medium text-blue-900">
                                        ID Institucional
                                    </div>
                                    <div class="text-xs text-blue-700">
                                        {{ autoIdInfo.prefix
                                        }}{{ autoIdInfo.year }}XXX → Número
                                        secuencial automático
                                    </div>
                                </div>
                            </div>
                            <div
                                class="flex items-center gap-3 rounded-lg border border-blue-100 bg-white p-3"
                            >
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-blue-100"
                                >
                                    <UserPlus class="h-4 w-4 text-blue-600" />
                                </div>
                                <div>
                                    <div class="font-medium text-blue-900">
                                        Contraseña Temporal
                                    </div>
                                    <div class="text-xs text-blue-700">
                                        Se generará automáticamente
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <Card class="border-border bg-card shadow-lg">
                    <CardFooter class="flex justify-between pt-6">
                        <Button
                            variant="outline"
                            type="button"
                            as-child
                            class="border-border hover:bg-accent"
                        >
                            <Link href="/admin/users"> Cancelar </Link>
                        </Button>
                        <Button
                            type="submit"
                            :disabled="loading.submitting"
                            class="bg-primary px-8 text-primary-foreground hover:bg-primary/90"
                        >
                            <Loader2
                                v-if="loading.submitting"
                                class="mr-2 h-4 w-4 animate-spin"
                            />
                            <Save v-else class="mr-2 h-4 w-4" />
                            {{
                                loading.submitting
                                    ? 'Creando...'
                                    : 'Crear Usuario'
                            }}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>
