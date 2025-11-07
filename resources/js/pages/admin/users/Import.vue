<!-- resources/js/pages/admin/users/Import.vue -->
<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'
import { router, Head, Link, usePage } from '@inertiajs/vue3'
import { AppPageProps } from '@/types'
import {
  Card, CardContent, CardDescription, CardHeader, CardTitle
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Badge } from '@/components/ui/badge'
import { Alert, AlertDescription } from '@/components/ui/alert'
import {
  ArrowLeft, Download, Upload, FileText, Users, FileDown, CheckCircle, 
  AlertCircle, Clock, TrendingUp, Zap, Loader2, UserPlus, X, FileSearch
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'

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
]

// State
const importFile = ref<File | null>(null)
const loading = ref(false)
const fileInput = ref<HTMLInputElement | null>(null)
const page = usePage<PageProps>()

// Computed properties for flash messages and results
const successMessage = computed(() => page.props.flash?.success)
const errorMessage = computed(() => page.props.flash?.error)
const importResults = computed(() => page.props.flash?.import_results)
const importErrors = computed(() => page.props.flash?.import_errors)

// CORRECCIÓN: Usar template ref para el input nativo
function triggerFileInput() {
  const nativeInput = document.getElementById('import_file') as HTMLInputElement
  if (nativeInput) {
    nativeInput.click()
  }
}

function handleFileChange(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    importFile.value = target.files[0]
  }
}

function clearFile() {
  importFile.value = null
  const nativeInput = document.getElementById('import_file') as HTMLInputElement
  if (nativeInput) {
    nativeInput.value = ''
  }
}

function submit() {
  if (!importFile.value) {
    alert('Por favor selecciona un archivo para importar')
    return
  }

  loading.value = true

  const formData = new FormData()
  formData.append('import_file', importFile.value)

  router.post('/admin/users/import', formData, {
    preserveScroll: true,
    onSuccess: () => {
      loading.value = false
      importFile.value = null
      const nativeInput = document.getElementById('import_file') as HTMLInputElement
      if (nativeInput) nativeInput.value = ''
    },
    onError: () => {
      loading.value = false
    }
  })
}

function downloadTemplate() {
  globalThis.location.href = '/admin/users/import/template'
}

function downloadReport() {
  if (importResults.value?.has_errors) {
    globalThis.location.href = '/admin/users/import/report'
  }
}

function clearImportSession() {
  router.delete('/admin/users/import/session', {
    preserveScroll: true,
    onSuccess: () => {
      router.reload({ only: [] })
    }
  })
}

// Format file size
function formatFileSize(bytes: number) {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

// Format row number for display (accounting for header row)
function formatRowNumber(row: number) {
  return row - 1
}
</script>

<template>
  <Head>
    <title>Importar Usuarios</title>
  </Head>

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-8">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-foreground">
            Importar Usuarios
          </h1>
          <p class="text-muted-foreground mt-2 flex items-center gap-2">
            <Users class="w-4 h-4 text-primary" />
            Importa usuarios masivamente desde un archivo CSV o Excel
          </p>
        </div>
        <div class="flex items-center gap-3">
          <div class="flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary rounded-lg border border-primary/20">
            <Zap class="w-4 h-4 animate-pulse" />
            <span class="text-sm font-medium">Importación Masiva</span>
          </div>
          <Button variant="outline" as-child>
            <Link href="/admin/users">
              <ArrowLeft class="h-4 w-4 mr-2" />
              Volver a Usuarios
            </Link>
          </Button>
        </div>
      </div>

      <!-- Alert Messages -->
      <div v-if="successMessage || errorMessage" class="space-y-4">
        <Alert v-if="successMessage" class="bg-green-50 border-green-200">
          <CheckCircle class="h-4 w-4 text-green-600" />
          <AlertDescription class="text-green-800">
            {{ successMessage }}
          </AlertDescription>
        </Alert>

        <Alert v-if="errorMessage" class="bg-red-50 border-red-200">
          <AlertCircle class="h-4 w-4 text-red-600" />
          <AlertDescription class="text-red-800">
            {{ errorMessage }}
          </AlertDescription>
        </Alert>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Formulario de Importación -->
        <Card class="lg:col-span-2 bg-card border-border shadow-lg">
          <CardHeader class="bg-muted/50 border-b border-border">
            <CardTitle class="flex items-center gap-2 text-foreground">
              <Upload class="h-5 w-5 text-primary" />
              Subir Archivo
            </CardTitle>
            <CardDescription class="text-muted-foreground">
              Selecciona un archivo CSV o Excel con la información de los usuarios
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-6 pt-6">
            <!-- File Upload - CORREGIDO -->
            <div class="space-y-3">
              <Label for="import_file" class="text-foreground">Archivo de Importación *</Label>
              <div
                class="border-2 border-dashed border-border rounded-lg p-6 text-center hover:border-primary/50 transition-colors cursor-pointer"
                @click="triggerFileInput">
                <!-- ✅ SOLUCIÓN: Input HTML nativo en lugar del componente Shadcn -->
                <input 
                  id="import_file" 
                  type="file" 
                  accept=".csv,.xlsx,.xls" 
                  @change="handleFileChange"
                  class="hidden" 
                />
                <div class="max-w-md mx-auto">
                  <Upload class="h-12 w-12 text-muted-foreground mx-auto mb-4" />
                  <p class="text-foreground font-medium mb-2">
                    Haz clic para seleccionar un archivo
                  </p>
                  <p class="text-sm text-muted-foreground">
                    Arrastra y suelta tu archivo aquí
                  </p>
                  <p class="text-xs text-muted-foreground mt-2">
                    Formatos soportados: CSV, Excel (.xlsx, .xls)
                  </p>
                  <p class="text-xs text-muted-foreground">
                    Tamaño máximo: 10MB
                  </p>
                </div>
              </div>
            </div>

            <!-- File Preview -->
            <div v-if="importFile" class="p-4 border border-border rounded-lg bg-muted/30">
              <div class="flex items-center gap-4">
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center">
                  <FileText class="h-6 w-6 text-primary" />
                </div>
                <div class="flex-1 min-w-0">
                  <p class="font-medium text-foreground truncate">{{ importFile.name }}</p>
                  <div class="flex items-center gap-4 text-sm text-muted-foreground mt-1">
                    <span>{{ formatFileSize(importFile.size) }}</span>
                    <span>•</span>
                    <span>{{ importFile.type || 'Archivo' }}</span>
                  </div>
                </div>
                <div class="flex items-center gap-2">
                  <Badge variant="outline" class="bg-blue-500/10 text-blue-600 border-blue-200">
                    Listo para importar
                  </Badge>
                  <Button variant="ghost" size="sm" @click="clearFile" class="h-8 w-8 p-0">
                    <X class="h-4 w-4" />
                  </Button>
                </div>
              </div>
            </div>

            <!-- Submit Button -->
            <Button @click="submit" :disabled="!importFile || loading"
              class="w-full bg-primary text-primary-foreground hover:bg-primary/90" size="lg">
              <Loader2 v-if="loading" class="h-4 w-4 mr-2 animate-spin" />
              <Upload v-else class="h-4 w-4 mr-2" />
              {{ loading ? 'Procesando...' : 'Iniciar Importación' }}
            </Button>

            <!-- Import Results -->
            <div v-if="importResults" class="space-y-4">
              <div class="p-4 border rounded-lg"
                :class="importResults.has_errors ? 'border-amber-200 bg-amber-50' : 'border-green-200 bg-green-50'">
                <div class="flex items-center gap-3 mb-3">
                  <CheckCircle v-if="!importResults.has_errors" class="h-5 w-5 text-green-600" />
                  <AlertCircle v-else class="h-5 w-5 text-amber-600" />
                  <h3 class="font-semibold" :class="importResults.has_errors ? 'text-amber-800' : 'text-green-800'">
                    Resultado de la Importación
                  </h3>
                </div>

                <div class="grid grid-cols-2 gap-4 text-sm">
                  <div>
                    <span class="font-medium">Total de filas:</span>
                    <span class="ml-2">{{ importResults.total_rows }}</span>
                  </div>
                  <div>
                    <span class="font-medium">Importados:</span>
                    <span class="ml-2 text-green-600 font-semibold">{{ importResults.imported }}</span>
                  </div>
                  <div>
                    <span class="font-medium">Omitidos:</span>
                    <span class="ml-2 text-amber-600 font-semibold">{{ importResults.skipped }}</span>
                  </div>
                </div>

                <!-- Error Details -->
                <div v-if="importResults.has_errors" class="mt-4">
                  <div class="flex items-center justify-between mb-2">
                    <h4 class="font-medium text-amber-800">Errores encontrados:</h4>
                    <Button variant="outline" size="sm" @click="downloadReport" class="text-xs">
                      <FileSearch class="h-3 w-3 mr-1" />
                      Descargar Reporte
                    </Button>
                  </div>
                  <div class="max-h-40 overflow-y-auto space-y-2">
                    <div v-for="(error, index) in importResults.errors.slice(0, 5)" :key="index"
                      class="text-xs p-2 bg-white rounded border border-amber-200">
                      <div class="font-medium">Fila {{ formatRowNumber(error.row) }} - {{ error.field }}</div>
                      <div class="text-amber-700">{{ error.error }}</div>
                      <div class="text-gray-500">Valor: {{ error.value }}</div>
                    </div>
                    <div v-if="importResults.errors.length > 5" class="text-center text-xs text-amber-700 py-2">
                      ... y {{ importResults.errors.length - 5 }} errores más
                    </div>
                  </div>
                </div>
              </div>

              <!-- Clear Session Button -->
              <div class="flex justify-end">
                <Button variant="outline" size="sm" @click="clearImportSession">
                  Limpiar Resultados
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Información y Plantilla -->
        <div class="space-y-6">
          <!-- Template Card -->
          <Card class="bg-card border-border shadow-lg">
            <CardHeader class="bg-muted/50 border-b border-border">
              <CardTitle class="text-foreground">Plantilla</CardTitle>
              <CardDescription class="text-muted-foreground">
                Descarga la plantilla oficial
              </CardDescription>
            </CardHeader>
            <CardContent class="pt-6">
              <div class="text-center">
                <div class="w-16 h-16 bg-primary/10 rounded-xl flex items-center justify-center mx-auto mb-4">
                  <FileDown class="h-8 w-8 text-primary" />
                </div>
                <p class="text-sm text-muted-foreground mb-4">
                  Usa nuestra plantilla predefinida para asegurar el formato correcto
                </p>
                <Button variant="outline" class="w-full" @click="downloadTemplate">
                  <Download class="h-4 w-4 mr-2" />
                  Descargar Plantilla CSV
                </Button>
              </div>
            </CardContent>
          </Card>

          <!-- Format Info Card -->
          <Card class="bg-card border-border shadow-lg">
            <CardHeader class="bg-muted/50 border-b border-border">
              <CardTitle class="text-foreground">Formato Requerido</CardTitle>
            </CardHeader>
            <CardContent class="pt-6">
              <div class="space-y-4">
                <div>
                  <h4 class="font-medium text-foreground text-sm mb-3 flex items-center gap-2">
                    <CheckCircle class="h-4 w-4 text-green-500" />
                    Columnas Obligatorias
                  </h4>
                  <ul class="text-sm text-muted-foreground space-y-2">
                    <li class="flex items-center gap-2">
                      <div class="w-1.5 h-1.5 bg-primary rounded-full"></div>
                      <span><strong>name</strong> (Nombre)</span>
                    </li>
                    <li class="flex items-center gap-2">
                      <div class="w-1.5 h-1.5 bg-primary rounded-full"></div>
                      <span><strong>last_name</strong> (Apellido)</span>
                    </li>
                    <li class="flex items-center gap-2">
                      <div class="w-1.5 h-1.5 bg-primary rounded-full"></div>
                      <span><strong>email</strong> (Email único)</span>
                    </li>
                    <li class="flex items-center gap-2">
                      <div class="w-1.5 h-1.5 bg-primary rounded-full"></div>
                      <span><strong>dni</strong> (DNI - 8 dígitos)</span>
                    </li>
                    <li class="flex items-center gap-2">
                      <div class="w-1.5 h-1.5 bg-primary rounded-full"></div>
                      <span><strong>phone</strong> (Teléfono - 9 dígitos)</span>
                    </li>
                    <li class="flex items-center gap-2">
                      <div class="w-1.5 h-1.5 bg-primary rounded-full"></div>
                      <span><strong>user_type</strong> (student/teacher/external/staff)</span>
                    </li>
                  </ul>
                </div>

                <div class="pt-4 border-t border-border">
                  <h4 class="font-medium text-foreground text-sm mb-3 flex items-center gap-2">
                    <AlertCircle class="h-4 w-4 text-amber-500" />
                    Consideraciones
                  </h4>
                  <ul class="text-xs text-muted-foreground space-y-1">
                    <li>• Las contraseñas se generan automáticamente</li>
                    <li>• Los usuarios reciben email de verificación</li>
                    <li>• Se asignan permisos según el tipo de usuario</li>
                    <li>• El sistema valida duplicados automáticamente</li>
                    <li>• Fechas en formato YYYY-MM-DD</li>
                    <li>• Booleanos: 1/0, true/false, yes/no</li>
                  </ul>
                </div>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Importaciones Recientes -->
      <Card class="bg-card border-border shadow-lg">
        <CardHeader class="bg-muted/50 border-b border-border">
          <CardTitle class="flex items-center gap-2 text-foreground">
            <TrendingUp class="h-5 w-5 text-primary" />
            Procesos de Importación
          </CardTitle>
          <CardDescription class="text-muted-foreground">
            Historial y estado de importaciones recientes
          </CardDescription>
        </CardHeader>
        <CardContent class="pt-6">
          <div class="text-center py-12">
            <div class="max-w-md mx-auto">
              <div class="p-4 bg-primary/10 rounded-full w-20 h-20 mx-auto mb-6 flex items-center justify-center">
                <Clock class="w-10 h-10 text-primary" />
              </div>
              <h3 class="text-2xl font-bold text-foreground mb-3">No hay importaciones recientes</h3>
              <p class="text-muted-foreground text-lg mb-6">
                Comienza importando tu primer lote de usuarios
              </p>
              <div class="flex gap-3 justify-center">
                <Button variant="outline" as-child
                  class="border-primary/20 text-primary hover:bg-primary hover:text-primary-foreground">
                  <Link href="/admin/users/create">
                  <UserPlus class="w-4 h-4 mr-2" />
                  Crear Usuario Manual
                  </Link>
                </Button>
                <Button as-child class="bg-primary text-primary-foreground hover:bg-primary/90">
                  <Link href="/admin/users">
                  <Users class="w-4 h-4 mr-2" />
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