<!-- resources/js/pages/admin/users/TempPasswords.vue -->
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router, Link, usePage } from '@inertiajs/vue3'
import type { AppPageProps } from '@/types'
import {
    Card, CardContent, CardDescription, CardHeader, CardTitle, CardFooter
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { Label } from '@/components/ui/label'
import {
    ArrowLeft, Download, Copy, Check, Users, Key, Clock, AlertTriangle,
    Eye, EyeOff, FileText, UserCheck, Shield, UserPlus, RefreshCw
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'

// Definir tipos
interface TempPassword {
    row?: number
    email: string
    name: string
    temp_password: string
    user_id?: number
    institutional_id: string
}

interface ImportResults {
    total_rows?: number
    imported?: number
    skipped?: number
    has_errors?: boolean
}

interface PageProps extends AppPageProps {
    flash?: {
        temp_passwords?: TempPassword[]
        temp_password?: string // Para creación individual/reset
        import_results?: ImportResults
        user_created?: { name: string; email: string; institutional_id: string }
        user_reset?: { name: string; email: string; institutional_id: string }
        success?: string
        error?: string
    }
}

// Estado
const page = usePage<PageProps>()
const copiedIndex = ref<number | null>(null)
const showPasswords = ref<boolean[]>([])
const showIndividualPassword = ref(false)

// Computed
const tempPasswords = computed(() => {
    const passwordsFromImport = page.props.flash?.temp_passwords || []
    const passwordFromIndividual = page.props.flash?.temp_password

    let allPasswords = [...passwordsFromImport]

    // AGREGAR CONTRASEÑA INDIVIDUAL SI EXISTE
    if (passwordFromIndividual) {
        const userData = page.props.flash?.user_created || page.props.flash?.user_reset
        const actionType = page.props.flash?.user_created ? 'creado' : 'reseteado'

        allPasswords.push({
            email: userData?.email || 'Usuario individual',
            name: userData?.name || `Usuario ${actionType}`,
            temp_password: passwordFromIndividual,
            institutional_id: userData?.institutional_id || 'Asignado al usuario'
        })
    }

    return allPasswords
})

const importResults = computed(() => page.props.flash?.import_results)
const hasPasswords = computed(() => tempPasswords.value.length > 0)
const isIndividualCreation = computed(() => !!page.props.flash?.temp_password && !page.props.flash?.temp_passwords)
const actionType = computed(() => page.props.flash?.user_created ? 'creado' : 'reseteado')

// Inicializar array de visibilidad de contraseñas
onMounted(() => {
    showPasswords.value = new Array(tempPasswords.value.length).fill(false)
})

// Breadcrumbs
const breadcrumbs = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Usuarios', href: '/admin/users' },
    { title: 'Contraseñas Temporales', href: '#' },
]

// Métodos
function copyToClipboard(text: string, index: number) {
    navigator.clipboard.writeText(text).then(() => {
        copiedIndex.value = index
        setTimeout(() => {
            copiedIndex.value = null
        }, 2000)
    })
}

function togglePasswordVisibility(index: number) {
    showPasswords.value[index] = !showPasswords.value[index]
}

function downloadPasswordReport() {
    // ✅ DETERMINAR QUÉ RUTA USAR SEGÚN EL CONTEXTO
    const route = page.props.flash?.temp_passwords
        ? '/admin/users/import/passwords-report'
        : '/admin/users/temp-passwords/report'
    globalThis.location.href = route
}

function goToUsers() {
    router.visit('/admin/users')
}

function goToImport() {
    router.visit('/admin/users/import')
}

// Formatear fecha de expiración
function getExpirationDate() {
    const date = new Date()
    date.setDate(date.getDate() + 7)
    return date.toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    })
}
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-foreground">
                        Contraseñas Temporales
                    </h1>
                    <p class="text-muted-foreground mt-2 flex items-center gap-2">
                        <Key class="w-4 h-4 text-primary" />
                        <span v-if="isIndividualCreation">
                            Credenciales para usuario {{ actionType }}
                        </span>
                        <span v-else>
                            Credenciales de acceso para nuevos usuarios
                        </span>
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Badge variant="outline" class="bg-amber-500/10 text-amber-600 border-amber-200">
                        <Clock class="w-3 h-3 mr-1" />
                        Expiran en 7 días
                    </Badge>
                    <Button variant="outline" as-child>
                        <Link href="/admin/users">
                        <ArrowLeft class="h-4 w-4 mr-2" />
                        Volver a Usuarios
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Alertas de Éxito/Error -->
            <Alert v-if="page.props.flash?.success" class="bg-green-50 border-green-200">
                <UserCheck class="h-4 w-4 text-green-600" />
                <AlertDescription class="text-green-800">
                    {{ page.props.flash.success }}
                </AlertDescription>
            </Alert>

            <Alert v-if="page.props.flash?.error" class="bg-red-50 border-red-200">
                <AlertTriangle class="h-4 w-4 text-red-600" />
                <AlertDescription class="text-red-800">
                    {{ page.props.flash.error }}
                </AlertDescription>
            </Alert>

            <!-- Alertas Importantes -->
            <Alert class="bg-amber-50 border-amber-200">
                <AlertTriangle class="h-4 w-4 text-amber-600" />
                <AlertDescription class="text-amber-800">
                    <div class="font-semibold">Información Importante</div>
                    <ul class="mt-1 text-sm space-y-1">
                        <li>• Las contraseñas son temporales y expiran el {{ getExpirationDate() }}</li>
                        <li>• Los usuarios deben cambiar su contraseña en el primer inicio de sesión</li>
                        <li>• Esta información solo está disponible inmediatamente después de la creación/importación
                        </li>
                        <li>• Guarda o descarga esta información antes de salir de esta página</li>
                    </ul>
                </AlertDescription>
            </Alert>

            <!-- Resultados de Importación (si aplica) -->
            <div v-if="importResults" class="space-y-4">
                <Card class="border-blue-200 bg-blue-50/30">
                    <CardHeader class="border-b border-blue-200">
                        <CardTitle class="text-blue-900 flex items-center gap-2">
                            <FileText class="h-5 w-5" />
                            Resumen de Importación
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-6">
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                            <div class="text-center p-3 bg-white rounded-lg border border-blue-100">
                                <div class="font-semibold text-blue-900">{{ importResults.total_rows || 0 }}</div>
                                <div class="text-blue-700 text-xs">Total de Filas</div>
                            </div>
                            <div class="text-center p-3 bg-white rounded-lg border border-green-100">
                                <div class="font-semibold text-green-900">{{ importResults.imported || 0 }}</div>
                                <div class="text-green-700 text-xs">Usuarios Importados</div>
                            </div>
                            <div class="text-center p-3 bg-white rounded-lg border border-amber-100">
                                <div class="font-semibold text-amber-900">{{ importResults.skipped || 0 }}</div>
                                <div class="text-amber-700 text-xs">Filas Omitidas</div>
                            </div>
                            <div class="text-center p-3 bg-white rounded-lg border border-blue-100">
                                <div class="font-semibold text-blue-900">{{ tempPasswords.length }}</div>
                                <div class="text-blue-700 text-xs">Contraseñas Generadas</div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Indicador de Creación Individual -->
            <div v-if="isIndividualCreation" class="space-y-4">
                <Card class="border-green-200 bg-green-50/30">
                    <CardHeader class="border-b border-green-200">
                        <CardTitle class="text-green-900 flex items-center gap-2">
                            <UserPlus v-if="page.props.flash?.user_created" class="h-5 w-5" />
                            <RefreshCw v-if="page.props.flash?.user_reset" class="h-5 w-5" />
                            Usuario {{ actionType === 'creado' ? 'Creado' : 'Reseteado' }} Exitosamente
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="pt-6">
                        <div class="flex items-center gap-4 p-4 bg-white rounded-lg border border-green-100">
                            <div class="w-12 h-12 bg-green-100 rounded-full flex items-center justify-center">
                                <UserCheck class="h-6 w-6 text-green-600" />
                            </div>
                            <div class="flex-1">
                                <h3 class="font-semibold text-green-900">
                                    {{ page.props.flash?.user_created?.name || page.props.flash?.user_reset?.name }}
                                </h3>
                                <div class="text-sm text-green-700 mt-1">
                                    {{ page.props.flash?.user_created?.email || page.props.flash?.user_reset?.email }}
                                </div>
                                <div class="text-xs text-green-600 mt-1">
                                    ID: {{ page.props.flash?.user_created?.institutional_id ||
                                        page.props.flash?.user_reset?.institutional_id }}
                                </div>
                            </div>
                            <Badge variant="outline" class="bg-green-500/10 text-green-600 border-green-200">
                                {{ actionType === 'creado' ? 'Nuevo Usuario' : 'Contraseña Reseteada' }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Lista de Contraseñas -->
            <Card class="border-border shadow-lg">
                <CardHeader class="bg-muted/50 border-b border-border">
                    <CardTitle class="flex items-center gap-2 text-foreground">
                        <Shield class="h-5 w-5 text-primary" />
                        Credenciales de Acceso
                        <Badge variant="secondary" class="ml-2">
                            {{ tempPasswords.length }}
                        </Badge>
                    </CardTitle>
                    <CardDescription class="text-muted-foreground">
                        Contraseñas temporales generadas para los nuevos usuarios
                    </CardDescription>
                </CardHeader>

                <CardContent class="p-0">
                    <!-- Estado Vacío -->
                    <div v-if="!hasPasswords" class="text-center py-12">
                        <div class="max-w-md mx-auto">
                            <div
                                class="p-4 bg-muted rounded-full w-20 h-20 mx-auto mb-6 flex items-center justify-center">
                                <Key class="w-10 h-10 text-muted-foreground" />
                            </div>
                            <h3 class="text-2xl font-bold text-foreground mb-3">No hay contraseñas temporales</h3>
                            <p class="text-muted-foreground text-lg mb-6">
                                Las contraseñas temporales solo están disponibles inmediatamente después de crear o
                                importar usuarios.
                            </p>
                            <div class="flex gap-3 justify-center">
                                <Button variant="outline" @click="goToImport">
                                    <Users class="w-4 h-4 mr-2" />
                                    Importar Usuarios
                                </Button>
                                <Button @click="goToUsers"
                                    class="bg-primary text-primary-foreground hover:bg-primary/90">
                                    <UserCheck class="w-4 h-4 mr-2" />
                                    Ver Todos los Usuarios
                                </Button>
                            </div>
                        </div>
                    </div>

                    <!-- Lista de Contraseñas -->
                    <div v-else class="divide-y divide-border">
                        <div v-for="(password, index) in tempPasswords" :key="index"
                            class="p-6 hover:bg-muted/30 transition-colors">
                            <div class="flex items-start justify-between">
                                <!-- Información del Usuario -->
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-3">
                                        <div
                                            class="w-10 h-10 bg-primary/10 rounded-full flex items-center justify-center">
                                            <UserCheck class="h-5 w-5 text-primary" />
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h3 class="font-semibold text-foreground truncate">
                                                {{ password.name }}
                                            </h3>
                                            <div class="flex items-center gap-4 text-sm text-muted-foreground mt-1">
                                                <span class="truncate">{{ password.email }}</span>
                                                <span>•</span>
                                                <span class="font-mono text-xs bg-muted px-2 py-1 rounded">
                                                    {{ password.institutional_id }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Contraseña -->
                                    <div class="bg-muted/50 border border-border rounded-lg p-4">
                                        <div class="flex items-center justify-between mb-2">
                                            <Label class="text-sm font-medium text-foreground">Contraseña
                                                Temporal</Label>
                                            <div class="flex items-center gap-2">
                                                <Button variant="ghost" size="sm"
                                                    @click="togglePasswordVisibility(index)" class="h-8 w-8 p-0">
                                                    <EyeOff v-if="showPasswords[index]" class="h-4 w-4" />
                                                    <Eye v-else class="h-4 w-4" />
                                                </Button>
                                                <Button variant="ghost" size="sm"
                                                    @click="copyToClipboard(password.temp_password, index)"
                                                    class="h-8 w-8 p-0">
                                                    <Check v-if="copiedIndex === index"
                                                        class="h-4 w-4 text-green-600" />
                                                    <Copy v-else class="h-4 w-4" />
                                                </Button>
                                            </div>
                                        </div>
                                        <div
                                            class="font-mono text-lg bg-background border border-border rounded px-3 py-2 flex items-center justify-between">
                                            <span>
                                                {{ showPasswords[index] ? password.temp_password : '•'.repeat(12) }}
                                            </span>
                                            <Badge v-if="copiedIndex === index" variant="outline"
                                                class="bg-green-500/10 text-green-600 border-green-200 text-xs">
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
                            <Clock class="w-4 h-4" />
                            <span>Estas contraseñas expiran el {{ getExpirationDate() }}</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <Button variant="outline" @click="downloadPasswordReport">
                            <Download class="h-4 w-4 mr-2" />
                            Descargar Reporte
                        </Button>
                        <Button @click="goToUsers" class="bg-primary text-primary-foreground hover:bg-primary/90">
                            <UserCheck class="h-4 w-4 mr-2" />
                            Ver Todos los Usuarios
                        </Button>
                    </div>
                </CardFooter>
            </Card>

            <!-- Instrucciones para el Administrador -->
            <Card class="border-blue-200 bg-blue-50/30">
                <CardHeader>
                    <CardTitle class="text-blue-900 flex items-center gap-2">
                        <Users class="h-5 w-5" />
                        Instrucciones para Distribuir Credenciales
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-sm">
                        <div class="space-y-3">
                            <h4 class="font-semibold text-blue-900 flex items-center gap-2">
                                <UserCheck class="h-4 w-4" />
                                Para Usuarios Individuales
                            </h4>
                            <ul class="text-blue-800 space-y-2">
                                <li class="flex items-start gap-2">
                                    <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                    <span>Comparte la contraseña de forma segura con el usuario</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                    <span>Indica al usuario que debe cambiar la contraseña en el primer inicio de
                                        sesión</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                    <span>Recuerda que la contraseña expira en 7 días</span>
                                </li>
                            </ul>
                        </div>
                        <div class="space-y-3">
                            <h4 class="font-semibold text-blue-900 flex items-center gap-2">
                                <FileText class="h-4 w-4" />
                                Para Importaciones Masivas
                            </h4>
                            <ul class="text-blue-800 space-y-2">
                                <li class="flex items-start gap-2">
                                    <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                    <span>Descarga el reporte completo en formato CSV</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                    <span>Distribuye las credenciales de forma segura a cada usuario</span>
                                </li>
                                <li class="flex items-start gap-2">
                                    <div class="w-1.5 h-1.5 bg-blue-500 rounded-full mt-2 flex-shrink-0"></div>
                                    <span>Considera usar un sistema de mensajería seguro o entrega en persona</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>