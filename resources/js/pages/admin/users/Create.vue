<!-- resources/js/pages/admin/users/Create.vue -->
<script setup lang="ts">
import { reactive, watch } from 'vue'
import { router, Link } from '@inertiajs/vue3'
import { format } from 'date-fns'
import { es } from 'date-fns/locale'
import { Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter
} from '@/components/ui/card'
import { Calendar } from '@/components/ui/calendar'
import { Popover, PopoverContent, PopoverTrigger,
} from '@/components/ui/popover'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue,
} from '@/components/ui/select'
import { Checkbox } from '@/components/ui/checkbox'
import { ArrowLeft, Save, User, Loader2, UserPlus, Upload, Calendar as CalendarIcon, Info, Key
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import InputError from '@/components/InputError.vue'
import { Alert, AlertDescription } from '@/components/ui/alert'

// Función para formatear la fecha
const formatDate = (date: Date | undefined) => {
    if (!date) return ''
    return format(date, 'PPP', { locale: es })
}

// Props
const props = defineProps<{
    userTypes: any[]
    errors?: Record<string, string>
}>()

// Breadcrumbs
const breadcrumbs = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Usuarios', href: '/admin/users' },
    { title: 'Crear Usuario', href: '#' },
]

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
})

// Información del ID que se generará
const autoIdInfo = reactive({
    prefix: 'EST',
    year: new Date().getFullYear().toString().slice(-2),
    typeName: 'Estudiante'
})

// Watch para actualizar la información del ID automático
watch(() => form.user_type, (newType) => {
    const prefixes: Record<string, string> = {
        'admin': 'ADM',
        'librarian': 'BIB',
        'student': 'EST',
        'teacher': 'DOC',
        'external': 'EXT',
        'staff': 'PER'
    }
    
    const typeNames: Record<string, string> = {
        'admin': 'Administrador',
        'librarian': 'Bibliotecario',
        'student': 'Estudiante',
        'teacher': 'Docente',
        'external': 'Externo',
        'staff': 'Personal'
    }
    
    autoIdInfo.prefix = prefixes[newType] || 'USU'
    autoIdInfo.typeName = typeNames[newType] || 'Usuario'
})

// Loading state
const loading = reactive({
    submitting: false
})

// Methods
function submit() {
    loading.submitting = true

    const formData = {
        ...form,
        membership_expires_at: form.membership_expires_at ? format(form.membership_expires_at, 'yyyy-MM-dd') : null
        // ✅ NO se envía institutional_id - se genera automáticamente en el backend
    }

    router.post('/admin/users', formData, {
        preserveScroll: true,
        onSuccess: () => {
            loading.submitting = false
        },
        onError: () => {
            loading.submitting = false
        }
    })
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <!-- Header Simple -->
        <div class="flex items-center gap-4 p-6 border-b border-border">
            <div class="flex-1">
                <h1 class="text-3xl font-bold tracking-tight text-foreground">
                    Crear Nuevo Usuario
                </h1>
                <p class="text-muted-foreground mt-1 flex items-center gap-2">
                    <UserPlus class="w-4 h-4" />
                    Agrega un nuevo usuario al sistema bibliotecario
                </p>
            </div>
            <Button variant="outline" size="sm" as-child>
                <Link href="/admin/users">
                <ArrowLeft class="h-4 w-4 mr-2" />
                Volver a Usuarios
                </Link>
            </Button>
            <Button variant="outline" as-child
                class="border-primary/20 text-primary hover:bg-primary hover:text-primary-foreground">
                <Link href="/admin/users/import">
                <Upload class="h-4 w-4 mr-2" />
                Importar Usuarios
                </Link>
            </Button>
        </div>

        <!-- Contenido del Formulario -->
        <div class="p-6 w-full max-w-full">
            <form @submit.prevent="submit" class="space-y-6 w-full">
                <!-- Información Personal -->
                <Card class="bg-card border-border shadow-lg">
                    <CardHeader class="bg-muted/50 border-b border-border">
                        <CardTitle class="flex items-center gap-2 text-foreground">
                            <User class="h-5 w-5 text-primary" />
                            Información Personal
                        </CardTitle>
                        <CardDescription class="text-muted-foreground">
                            Información básica del usuario
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 pt-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                            <!-- Nombre -->
                            <div class="space-y-2">
                                <Label for="name" class="text-foreground">Nombre *</Label>
                                <Input id="name" v-model="form.name" placeholder="Ingresa el nombre"
                                    class="bg-background border-border focus:border-primary w-full" required />
                                <InputError :message="errors?.name" />
                            </div>

                            <!-- Apellido -->
                            <div class="space-y-2">
                                <Label for="last_name" class="text-foreground">Apellido *</Label>
                                <Input id="last_name" v-model="form.last_name" placeholder="Ingresa el apellido"
                                    class="bg-background border-border focus:border-primary w-full" required />
                                <InputError :message="errors?.last_name" />
                            </div>

                            <!-- Email -->
                            <div class="space-y-2">
                                <Label for="email" class="text-foreground">Email *</Label>
                                <Input id="email" v-model="form.email" type="email" placeholder="usuario@ejemplo.com"
                                    class="bg-background border-border focus:border-primary w-full" required />
                                <InputError :message="errors?.email" />
                            </div>

                            <!-- DNI -->
                            <div class="space-y-2">
                                <Label for="dni" class="text-foreground">DNI *</Label>
                                <Input id="dni" v-model="form.dni" placeholder="12345678" maxlength="8"
                                    class="bg-background border-border focus:border-primary w-full" required />
                                <InputError :message="errors?.dni" />
                            </div>

                            <!-- Teléfono -->
                            <div class="space-y-2">
                                <Label for="phone" class="text-foreground">Teléfono *</Label>
                                <Input id="phone" v-model="form.phone" placeholder="987654321" maxlength="9"
                                    class="bg-background border-border focus:border-primary w-full" required />
                                <InputError :message="errors?.phone" />
                            </div>

                            <!-- Tipo de Usuario -->
                            <div class="space-y-2">
                                <Label for="user_type" class="text-foreground">Tipo de Usuario *</Label>
                                <Select v-model="form.user_type">
                                    <SelectTrigger class="bg-background border-border focus:border-primary w-full">
                                        <SelectValue placeholder="Selecciona el tipo" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem v-for="type in userTypes" :key="type.value" :value="type.value">
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
                <Card class="bg-card border-border shadow-lg">
                    <CardHeader class="bg-muted/50 border-b border-border">
                        <CardTitle class="text-foreground">Información Institucional</CardTitle>
                        <CardDescription class="text-muted-foreground">
                            Información adicional para usuarios institucionales
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 pt-6">
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-6">
                            <!-- Email Institucional -->
                            <div class="space-y-2">
                                <Label for="institutional_email" class="text-foreground">Email Institucional</Label>
                                <Input id="institutional_email" v-model="form.institutional_email" type="email"
                                    placeholder="usuario@institucion.edu.pe"
                                    class="bg-background border-border focus:border-primary w-full" />
                                <InputError :message="errors?.institutional_email" />
                            </div>

                            <!-- ✅ INFORMACIÓN DEL ID AUTOMÁTICO (NO EDITABLE) -->
                            <div class="space-y-2">
                                <Label class="text-foreground">Código Institucional</Label>
                                <div class="p-3 bg-muted/30 border border-border rounded-lg">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-primary/10 rounded-lg flex items-center justify-center">
                                            <Key class="h-5 w-5 text-primary" />
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-semibold text-foreground">
                                                {{ autoIdInfo.prefix }}{{ autoIdInfo.year }}XXX
                                            </div>
                                            <div class="text-sm text-muted-foreground">
                                                ID automático para {{ autoIdInfo.typeName }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <p class="text-xs text-muted-foreground">
                                    El sistema generará automáticamente un código único secuencial
                                </p>
                            </div>

                            <!-- Fecha de Expiración de Membresía -->
                            <div class="space-y-2">
                                <Label for="membership_expires_at" class="text-foreground">Membresía hasta</Label>
                                <Popover>
                                    <PopoverTrigger as-child>
                                        <Button variant="outline" :class="[
                                            'w-full justify-start text-left font-normal',
                                            !form.membership_expires_at && 'text-muted-foreground'
                                        ]">
                                            <CalendarIcon class="mr-2 h-4 w-4" />
                                            {{ form.membership_expires_at ? formatDate(form.membership_expires_at) :
                                                'Selecciona una fecha' }}
                                        </Button>
                                    </PopoverTrigger>
                                    <PopoverContent class="w-auto p-0">
                                        <Calendar v-model="form.membership_expires_at" initialFocus mode="single" />
                                    </PopoverContent>
                                </Popover>
                                <InputError :message="errors?.membership_expires_at" />
                            </div>

                            <!-- Máximo de Préstamos -->
                            <div class="space-y-2">
                                <Label for="max_concurrent_loans" class="text-foreground">Máximo de Préstamos *</Label>
                                <Input id="max_concurrent_loans" v-model="form.max_concurrent_loans" type="number"
                                    min="1" max="10" class="bg-background border-border focus:border-primary w-full"
                                    required />
                                <InputError :message="errors?.max_concurrent_loans" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Configuración -->
                <Card class="bg-card border-border shadow-lg">
                    <CardHeader class="bg-muted/50 border-b border-border">
                        <CardTitle class="text-foreground">Configuración</CardTitle>
                        <CardDescription class="text-muted-foreground">
                            Opciones de acceso y permisos del usuario
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4 pt-6">
                        <!-- Checkboxes -->
                        <div class="flex flex-col gap-4 max-w-md">
                            <div class="flex items-center space-x-3">
                                <Checkbox id="can_download" v-model:checked="form.can_download"
                                    class="data-[state=checked]:bg-primary data-[state=checked]:border-primary" />
                                <Label for="can_download" class="text-sm font-normal text-foreground cursor-pointer">
                                    Permitir descarga de libros digitales
                                </Label>
                            </div>
                            <div class="flex items-center space-x-3">
                                <Checkbox id="is_active" v-model:checked="form.is_active"
                                    class="data-[state=checked]:bg-primary data-[state=checked]:border-primary" />
                                <Label for="is_active" class="text-sm font-normal text-foreground cursor-pointer">
                                    Usuario activo en el sistema
                                </Label>
                            </div>
                        </div>
                        <InputError :message="errors?.can_download" />
                        <InputError :message="errors?.is_active" />
                    </CardContent>
                </Card>

                <!-- Información del Sistema -->
                <Card class="bg-card border-border shadow-lg border-blue-200 bg-blue-50/30">
                    <CardHeader class="border-b border-blue-200">
                        <CardTitle class="text-blue-900 flex items-center gap-2">
                            <Info class="h-5 w-5" />
                            Información del Sistema
                        </CardTitle>
                        <CardDescription class="text-blue-700">
                            Características automáticas del nuevo usuario
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="pt-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                            <div class="flex items-center gap-3 p-3 bg-white rounded-lg border border-blue-100">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <Key class="h-4 w-4 text-blue-600" />
                                </div>
                                <div>
                                    <div class="font-medium text-blue-900">ID Institucional</div>
                                    <div class="text-blue-700 text-xs">
                                        {{ autoIdInfo.prefix }}{{ autoIdInfo.year }}XXX → Número secuencial automático
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 p-3 bg-white rounded-lg border border-blue-100">
                                <div class="w-8 h-8 bg-blue-100 rounded-full flex items-center justify-center">
                                    <UserPlus class="h-4 w-4 text-blue-600" />
                                </div>
                                <div>
                                    <div class="font-medium text-blue-900">Contraseña Temporal</div>
                                    <div class="text-blue-700 text-xs">Se generará automáticamente</div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Actions -->
                <Card class="bg-card border-border shadow-lg">
                    <CardFooter class="flex justify-between pt-6">
                        <Button variant="outline" type="button" as-child class="border-border hover:bg-accent">
                            <Link href="/admin/users">
                            Cancelar
                            </Link>
                        </Button>
                        <Button type="submit" :disabled="loading.submitting"
                            class="bg-primary text-primary-foreground hover:bg-primary/90 px-8">
                            <Loader2 v-if="loading.submitting" class="h-4 w-4 mr-2 animate-spin" />
                            <Save v-else class="h-4 w-4 mr-2" />
                            {{ loading.submitting ? 'Creando...' : 'Crear Usuario' }}
                        </Button>
                    </CardFooter>
                </Card>
            </form>
        </div>
    </AppLayout>
</template>