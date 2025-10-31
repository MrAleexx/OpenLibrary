<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import { 
  Card, 
  CardContent, 
  CardDescription, 
  CardHeader, 
  CardTitle 
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { 
  ArrowLeft, 
  Download,
  User
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'

// Props
const props = defineProps<{
  user: any
  downloads: any
}>()

// Format date
function formatDateTime(date: string) {
  return new Date(date).toLocaleString('es-ES')
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Button variant="outline" size="sm" as-child>
          <a :href="`/admin/users/${user.id}`">
            <ArrowLeft class="h-4 w-4 mr-2" />
            Volver al Usuario
          </a>
        </Button>
        <div>
          <h1 class="text-3xl font-bold tracking-tight">
            Historial de Descargas - {{ user.name }} {{ user.last_name }}
          </h1>
          <p class="text-muted-foreground mt-1">
            Todas las descargas realizadas por el usuario
          </p>
        </div>
      </div>

      <Card>
        <CardHeader>
          <CardTitle class="flex items-center gap-2">
            <Download class="h-5 w-5" />
            Descargas ({{ downloads.total }})
          </CardTitle>
          <CardDescription>
            Lista completa de libros descargados por el usuario
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="downloads.data.length > 0" class="space-y-3">
            <div
              v-for="download in downloads.data"
              :key="download.id"
              class="flex items-center justify-between p-4 border rounded-lg"
            >
              <div class="flex-1">
                <p class="font-medium">{{ download.book.title }}</p>
                <div class="text-sm text-muted-foreground space-y-1 mt-1">
                  <p>ISBN: {{ download.book.isbn }}</p>
                  <p>Descargado el {{ formatDateTime(download.downloaded_at) }}</p>
                  <p>IP: {{ download.ip_address }}</p>
                </div>
              </div>
              <Badge variant="outline">
                {{ download.book.book_type }}
              </Badge>
            </div>
          </div>
          <div v-else class="text-center py-8 text-muted-foreground">
            <Download class="h-12 w-12 mx-auto mb-4 opacity-50" />
            <p>No se encontraron descargas</p>
          </div>

          <!-- Pagination -->
          <div v-if="downloads.data.length > 0" class="flex justify-center mt-6">
            <div class="flex gap-2">
              <Button
                v-for="(link, index) in downloads.links"
                :key="index"
                :href="link.url"
                :disabled="!link.url"
                :variant="link.active ? 'default' : 'outline'"
                size="sm"
                v-html="link.label"
                preserve-scroll
              />
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>