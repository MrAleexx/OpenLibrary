<script setup lang="ts">
import { ref } from 'vue'
import { router } from '@inertiajs/vue3'
import { 
  Card, 
  CardContent, 
  CardDescription, 
  CardHeader, 
  CardTitle 
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { 
  ArrowLeft, 
  Download,
  Upload,
  FileText,
  Users
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'

// State
const importFile = ref<File | null>(null)

// Methods
function submit() {
  if (!importFile.value) {
    alert('Por favor selecciona un archivo para importar')
    return
  }

  const formData = new FormData()
  formData.append('import_file', importFile.value)

  router.post('/admin/users/import', formData, {
    onSuccess: () => {
      alert('Archivo enviado para procesamiento. Recibirás una notificación cuando la importación esté completa.')
    }
  })
}

function handleFileChange(event: Event) {
  const target = event.target as HTMLInputElement
  if (target.files && target.files[0]) {
    importFile.value = target.files[0]
  }
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Button variant="outline" size="sm" as-child>
          <a href="/admin/users">
            <ArrowLeft class="h-4 w-4 mr-2" />
            Volver
          </a>
        </Button>
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Importar Usuarios</h1>
          <p class="text-muted-foreground mt-1">
            Importa usuarios masivamente desde un archivo CSV o Excel
          </p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Formulario de Importación -->
        <Card class="lg:col-span-2">
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <Upload class="h-5 w-5" />
              Subir Archivo
            </CardTitle>
            <CardDescription>
              Selecciona un archivo CSV o Excel con la información de los usuarios
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="space-y-2">
              <Label for="import_file">Archivo de Importación</Label>
              <Input
                id="import_file"
                type="file"
                accept=".csv,.xlsx,.xls"
                @change="handleFileChange"
              />
              <p class="text-sm text-muted-foreground">
                Formatos soportados: CSV, Excel (.xlsx, .xls). Tamaño máximo: 10MB
              </p>
            </div>

            <div v-if="importFile" class="p-4 border rounded-lg bg-muted/50">
              <div class="flex items-center gap-3">
                <FileText class="h-8 w-8 text-blue-600" />
                <div>
                  <p class="font-medium">{{ importFile.name }}</p>
                  <p class="text-sm text-muted-foreground">
                    {{ (importFile.size / 1024 / 1024).toFixed(2) }} MB
                  </p>
                </div>
              </div>
            </div>

            <Button @click="submit" :disabled="!importFile" class="w-full">
              <Upload class="h-4 w-4 mr-2" />
              Iniciar Importación
            </Button>
          </CardContent>
        </Card>

        <!-- Información y Plantilla -->
        <Card>
          <CardHeader>
            <CardTitle>Información de Importación</CardTitle>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="space-y-3">
              <div>
                <h4 class="font-medium text-sm mb-2">Formato Requerido</h4>
                <p class="text-sm text-muted-foreground">
                  El archivo debe contener las siguientes columnas:
                </p>
                <ul class="text-sm text-muted-foreground mt-2 space-y-1">
                  <li>• name (Nombre)</li>
                  <li>• last_name (Apellido)</li>
                  <li>• email (Email)</li>
                  <li>• dni (DNI - 8 dígitos)</li>
                  <li>• phone (Teléfono - 9 dígitos)</li>
                  <li>• user_type (student/teacher/external/staff)</li>
                </ul>
              </div>

              <div class="pt-4 border-t">
                <h4 class="font-medium text-sm mb-2">Plantilla</h4>
                <p class="text-sm text-muted-foreground mb-3">
                  Descarga la plantilla para asegurar el formato correcto:
                </p>
                <Button variant="outline" size="sm" class="w-full" as-child>
                  <a href="/admin/users/import/template">
                    <Download class="h-4 w-4 mr-2" />
                    Descargar Plantilla
                  </a>
                </Button>
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Procesos de Importación Anteriores -->
      <Card>
        <CardHeader>
          <CardTitle>Importaciones Recientes</CardTitle>
          <CardDescription>
            Historial de importaciones realizadas
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div class="text-center py-8 text-muted-foreground">
            <Users class="h-12 w-12 mx-auto mb-4 opacity-50" />
            <p>No se encontraron importaciones recientes</p>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>