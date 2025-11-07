<!-- resources/js/pages/admin/users/DownloadHistory.vue -->
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle 
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { ArrowLeft, Download, User, Calendar, Globe, FileText, BookOpen, Hash, Eye, ChevronDown, ChevronUp, TrendingUp
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'
import { ref, computed } from 'vue'

// Props
const props = defineProps<{
  user: any
  downloads: {
    data: Array<{
      id: number
      downloaded_at: string
      ip_address: string
      user_agent?: string
      book: {
        id: number
        title: string
        isbn: string
        book_type: string
        cover_image?: string
      }
    }>
    total: number
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
}>()

// Breadcrumbs
const breadcrumbs = [
  { title: 'Dashboard', href: '/admin/dashboard' },
  { title: 'Usuarios', href: '/admin/users' },
  { title: `${props.user.name} ${props.user.last_name}`, href: `/admin/users/${props.user.id}` },
  { title: 'Historial de Descargas', href: '#' },
]

// State for expanded items
const expandedItems = ref<Set<number>>(new Set())

// Format date
function formatDateTime(date: string) {
  return new Date(date).toLocaleString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

function formatDate(date: string) {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

// Toggle expanded state
function toggleExpand(downloadId: number) {
  if (expandedItems.value.has(downloadId)) {
    expandedItems.value.delete(downloadId)
  } else {
    expandedItems.value.add(downloadId)
  }
}

// Book type badges
const bookTypeColors: any = {
  digital: 'bg-blue-500/10 text-blue-600 border-blue-200 dark:bg-blue-500/20 dark:text-blue-400 dark:border-blue-800',
  physical: 'bg-green-500/10 text-green-600 border-green-200 dark:bg-green-500/20 dark:text-green-400 dark:border-green-800',
  both: 'bg-purple-500/10 text-purple-600 border-purple-200 dark:bg-purple-500/20 dark:text-purple-400 dark:border-purple-800'
}

const bookTypeLabels: any = {
  digital: 'Digital',
  physical: 'Físico',
  both: 'Ambos'
}

// Computed for stats
const totalDownloads = computed(() => props.downloads.total)
const todayDownloads = computed(() => {
  const today = new Date().toDateString()
  return props.downloads.data.filter(download => 
    new Date(download.downloaded_at).toDateString() === today
  ).length
})

const thisWeekDownloads = computed(() => {
  const oneWeekAgo = new Date()
  oneWeekAgo.setDate(oneWeekAgo.getDate() - 7)
  return props.downloads.data.filter(download => 
    new Date(download.downloaded_at) >= oneWeekAgo
  ).length
})
</script>

<template>
  <Head>
    <title>Historial de Descargas - {{ user.name }}</title>
  </Head>

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-foreground">
            Historial de Descargas
          </h1>
          <p class="text-muted-foreground mt-2 flex items-center gap-2">
            <Download class="w-4 h-4 text-primary" />
            Todas las descargas realizadas por {{ user.name }} {{ user.last_name }}
          </p>
        </div>
        <div class="flex items-center gap-3">
          <Button variant="outline" as-child>
            <Link :href="`/admin/users/${user.id}`">
              <ArrowLeft class="h-4 w-4 mr-2" />
              Volver al Usuario
            </Link>
          </Button>
          <Button variant="outline" as-child>
            <Link :href="`/admin/users/${user.id}/loan-history`">
              <BookOpen class="h-4 w-4 mr-2" />
              Ver Préstamos
            </Link>
          </Button>
        </div>
      </div>

      <!-- Stats Cards -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <Card class="bg-gradient-to-br from-blue-500/10 to-blue-600/5 border-blue-200">
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-blue-600 mb-1">Total Descargas</p>
                <p class="text-3xl font-bold text-foreground">{{ totalDownloads }}</p>
                <p class="text-xs text-muted-foreground mt-1">Historial completo</p>
              </div>
              <div class="w-12 h-12 bg-blue-500/20 rounded-xl flex items-center justify-center">
                <Download class="w-6 h-6 text-blue-500" />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-br from-green-500/10 to-green-600/5 border-green-200">
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-green-600 mb-1">Descargas Hoy</p>
                <p class="text-3xl font-bold text-foreground">{{ todayDownloads }}</p>
                <p class="text-xs text-muted-foreground mt-1">{{ formatDate(new Date().toISOString()) }}</p>
              </div>
              <div class="w-12 h-12 bg-green-500/20 rounded-xl flex items-center justify-center">
                <Calendar class="w-6 h-6 text-green-500" />
              </div>
            </div>
          </CardContent>
        </Card>

        <Card class="bg-gradient-to-br from-purple-500/10 to-purple-600/5 border-purple-200">
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-purple-600 mb-1">Esta Semana</p>
                <p class="text-3xl font-bold text-foreground">{{ thisWeekDownloads }}</p>
                <p class="text-xs text-muted-foreground mt-1">Últimos 7 días</p>
              </div>
              <div class="w-12 h-12 bg-purple-500/20 rounded-xl flex items-center justify-center">
                <TrendingUp class="w-6 h-6 text-purple-500" />
              </div>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Downloads List -->
      <Card class="bg-card border-border shadow-lg">
        <CardHeader class="bg-muted/50 border-b border-border">
          <CardTitle class="flex items-center gap-2 text-foreground">
            <Download class="h-5 w-5 text-primary" />
            Lista de Descargas
          </CardTitle>
          <CardDescription class="text-muted-foreground">
            {{ downloads.total }} descargas encontradas en el historial
          </CardDescription>
        </CardHeader>
        <CardContent class="p-0">
          <div v-if="downloads.data.length > 0" class="divide-y divide-border">
            <div
              v-for="download in downloads.data"
              :key="download.id"
              class="p-6 hover:bg-accent/50 transition-colors group"
            >
              <!-- Main Download Info -->
              <div class="flex items-start justify-between">
                <div class="flex-1 min-w-0">
                  <div class="flex items-start gap-4">
                    <!-- Book Icon/Placeholder -->
                    <div class="w-12 h-16 bg-primary/10 rounded-lg flex items-center justify-center flex-shrink-0 mt-1">
                      <BookOpen class="w-6 h-6 text-primary" />
                    </div>
                    
                    <div class="flex-1 min-w-0">
                      <!-- Book Title -->
                      <h3 class="font-semibold text-foreground group-hover:text-primary transition-colors text-lg mb-2">
                        {{ download.book.title }}
                      </h3>
                      
                      <!-- Metadata Row -->
                      <div class="flex flex-wrap items-center gap-4 text-sm text-muted-foreground mb-3">
                        <div class="flex items-center gap-1">
                          <Hash class="w-3 h-3" />
                          <span>ISBN: {{ download.book.isbn }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                          <Calendar class="w-3 h-3" />
                          <span>{{ formatDateTime(download.downloaded_at) }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                          <Globe class="w-3 h-3" />
                          <span>{{ download.ip_address }}</span>
                        </div>
                      </div>

                      <!-- Badges -->
                      <div class="flex flex-wrap gap-2">
                        <Badge :class="bookTypeColors[download.book.book_type]" class="border font-medium">
                          {{ bookTypeLabels[download.book.book_type] }}
                        </Badge>
                        <Badge variant="outline" class="bg-gray-500/10 text-gray-600 border-gray-200">
                          <FileText class="w-3 h-3 mr-1" />
                          Descarga #{{ download.id }}
                        </Badge>
                      </div>
                    </div>
                  </div>

                  <!-- Expanded Details -->
                  <div v-if="expandedItems.has(download.id)" class="mt-4 pl-16 border-t border-border pt-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 text-sm">
                      <div>
                        <h4 class="font-medium text-foreground mb-2 flex items-center gap-2">
                          <Globe class="w-4 h-4 text-blue-500" />
                          Información de Conexión
                        </h4>
                        <div class="space-y-1 text-muted-foreground">
                          <p><strong>IP:</strong> {{ download.ip_address }}</p>
                          <p><strong>Fecha y Hora:</strong> {{ formatDateTime(download.downloaded_at) }}</p>
                          <p v-if="download.user_agent" class="break-all">
                            <strong>User Agent:</strong> {{ download.user_agent }}
                          </p>
                        </div>
                      </div>
                      <div>
                        <h4 class="font-medium text-foreground mb-2 flex items-center gap-2">
                          <BookOpen class="w-4 h-4 text-green-500" />
                          Información del Libro
                        </h4>
                        <div class="space-y-1 text-muted-foreground">
                          <p><strong>Título:</strong> {{ download.book.title }}</p>
                          <p><strong>ISBN:</strong> {{ download.book.isbn }}</p>
                          <p><strong>Tipo:</strong> {{ bookTypeLabels[download.book.book_type] }}</p>
                          <Button variant="outline" size="sm" as-child class="mt-2">
                            <Link :href="`/admin/books/${download.book.id}`">
                              <Eye class="w-3 h-3 mr-1" />
                              Ver libro
                            </Link>
                          </Button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center gap-2 ml-4 flex-shrink-0">
                  <Button 
                    variant="ghost" 
                    size="sm" 
                    @click="toggleExpand(download.id)"
                    class="h-8 w-8 p-0 opacity-50 group-hover:opacity-100 transition-opacity"
                  >
                    <component 
                      :is="expandedItems.has(download.id) ? ChevronUp : ChevronDown" 
                      class="h-4 w-4" 
                    />
                  </Button>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-16 border-2 border-dashed border-border rounded-xl m-6">
            <div class="max-w-md mx-auto">
              <div class="p-4 bg-primary/10 rounded-full w-20 h-20 mx-auto mb-6 flex items-center justify-center">
                <Download class="w-10 h-10 text-primary" />
              </div>
              <h3 class="text-2xl font-bold text-foreground mb-3">No se encontraron descargas</h3>
              <p class="text-muted-foreground text-lg mb-6">
                {{ user.name }} no ha realizado ninguna descarga aún
              </p>
              <div class="flex gap-3 justify-center">
                <Button variant="outline" as-child class="border-primary/20 text-primary hover:bg-primary hover:text-primary-foreground">
                  <Link :href="`/admin/users/${user.id}`">
                    <User class="w-4 h-4 mr-2" />
                    Volver al Usuario
                  </Link>
                </Button>
                <Button as-child class="bg-primary text-primary-foreground hover:bg-primary/90">
                  <Link href="/admin/books">
                    <BookOpen class="w-4 h-4 mr-2" />
                    Explorar Libros
                  </Link>
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Pagination -->
      <div v-if="downloads.data.length > 0" class="flex justify-center">
        <div class="flex gap-2">
          <Link 
            v-for="(link, index) in downloads.links" 
            :key="index" 
            :href="link.url ?? ''" 
            :disabled="!link.url"
            :class="[
              'rounded-lg font-medium px-3 py-1.5 text-sm transition-colors',
              link.active 
                ? 'bg-primary text-primary-foreground hover:bg-primary/90' 
                : 'bg-background border border-input hover:bg-accent hover:text-accent-foreground',
              !link.url 
                ? 'opacity-50 cursor-not-allowed' 
                : 'cursor-pointer'
            ]"
            v-html="link.label" 
            preserve-scroll 
          />
        </div>
      </div>

      <!-- Quick Stats Footer -->
      <Card class="bg-muted/50 border-border">
        <CardContent class="p-6">
          <div class="grid grid-cols-1 md:grid-cols-4 gap-6 text-center">
            <div>
              <p class="text-2xl font-bold text-foreground">{{ totalDownloads }}</p>
              <p class="text-sm text-muted-foreground">Total Descargas</p>
            </div>
            <div>
              <p class="text-2xl font-bold text-foreground">{{ todayDownloads }}</p>
              <p class="text-sm text-muted-foreground">Hoy</p>
            </div>
            <div>
              <p class="text-2xl font-bold text-foreground">{{ thisWeekDownloads }}</p>
              <p class="text-sm text-muted-foreground">Esta Semana</p>
            </div>
            <div>
              <p class="text-2xl font-bold text-foreground">{{ downloads.data.length }}</p>
              <p class="text-sm text-muted-foreground">En esta página</p>
            </div>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>