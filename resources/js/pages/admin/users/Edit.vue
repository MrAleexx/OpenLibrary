<!-- resources/js/pages/admin/users/Edit.vue -->
<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
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
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import AppLayout from '@/layouts/AppLayout.vue';
import { Link, router } from '@inertiajs/vue3';
import { ArrowLeft, Loader2, Save, User } from 'lucide-vue-next';
import { reactive } from 'vue';

// Props
const props = defineProps<{
    user: any;
    userTypes: any[];
    errors?: Record<string, string>;
}>();

// Breadcrumbs
const breadcrumbs = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Usuarios', href: '/admin/users' },
    { title: `Editar ${props.user.name}`, href: '#' },
];

// Form state
const form = reactive({
    name: props.user.name,
    last_name: props.user.last_name,
    email: props.user.email,
    dni: props.user.dni,
    phone: props.user.phone,
    user_type: props.user.user_type,
    institutional_email: props.user.institutional_email || '',
    institutional_id: props.user.institutional_id || '',
    membership_expires_at: props.user.membership_expires_at
        ? new Date(props.user.membership_expires_at).toISOString().split('T')[0]
        : '',
    max_concurrent_loans: props.user.max_concurrent_loans,
    can_download: props.user.can_download,
    is_active: props.user.is_active,
});

// Loading state
const loading = reactive({
    submitting: false,
    fields: {} as Record<string, boolean>,
});

function validateField(field: string) {
    loading.fields[field] = true;
    // Simular validación o debounce
    setTimeout(() => {
        loading.fields[field] = false;
    }, 500);
}

// Methods
function submit() {
    loading.submitting = true;

    router.put(`/admin/users/${props.user.id}`, form, {
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
        <div class="min-h-screen bg-background">
            <!-- Header Fijo -->
            <div
                class="sticky top-0 z-10 border-b border-border bg-card/50 backdrop-blur supports-[backdrop-filter]:bg-card/60"
            >
                <div class="p-6">
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <h1
                                class="text-3xl font-bold tracking-tight text-foreground"
                            >
                                Editar Usuario
                            </h1>
                            <p
                                class="mt-1 flex items-center gap-2 text-muted-foreground"
                            >
                                <User class="h-4 w-4" />
                                Actualiza la información de {{ user.name }}
                                {{ user.last_name }}
                            </p>
                        </div>
                        <Button variant="outline" size="sm" as-child>
                            <Link href="/admin/users">
                                <ArrowLeft class="mr-2 h-4 w-4" />
                                Volver a Usuarios
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>

            <!-- Contenido del Formulario -->
            <div class="mx-auto max-w-7xl p-6">
                <form @submit.prevent="submit" class="space-y-6">
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
                                    <Label
                                        for="last_name"
                                        class="text-foreground"
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
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    type="email"
                                    placeholder="usuario@ejemplo.com"
                                    :class="[
                                        'w-full border-border bg-background focus:border-primary',
                                        errors?.email
                                            ? 'border-destructive focus:border-destructive'
                                            : '',
                                    ]"
                                    required
                                />

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
                                    <Label
                                        for="user_type"
                                        class="text-foreground"
                                        >Tipo de Usuario *</Label
                                    >
                                    <Select
                                        v-model:model-value="form.user_type"
                                    >
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
                                Información adicional para usuarios
                                institucionales
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

                                <!-- Código Institucional -->
                                <div class="space-y-2">
                                    <Label
                                        for="institutional_id"
                                        class="text-foreground"
                                        >Código Institucional</Label
                                    >
                                    <Input
                                        id="institutional_id"
                                        v-model="form.institutional_id"
                                        placeholder="Código o ID institucional"
                                        class="w-full border-border bg-background focus:border-primary"
                                    />
                                    <InputError
                                        :message="errors?.institutional_id"
                                    />
                                </div>

                                <!-- Fecha de Expiración de Membresía -->
                                <div class="space-y-2">
                                    <Label
                                        for="membership_expires_at"
                                        class="text-foreground"
                                        >Membresía hasta</Label
                                    >
                                    <Input
                                        id="membership_expires_at"
                                        v-model="form.membership_expires_at"
                                        type="date"
                                        :min="
                                            new Date()
                                                .toISOString()
                                                .split('T')[0]
                                        "
                                        class="w-full border-border bg-background focus:border-primary"
                                    />
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
                                        ? 'Actualizando...'
                                        : 'Actualizar Usuario'
                                }}
                            </Button>
                        </CardFooter>
                    </Card>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
