<script setup lang="ts">
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import { 
  Card, 
  CardContent, 
  CardDescription, 
  CardHeader, 
  CardTitle 
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import {ArrowLeft,Edit,BookOpen,Download,Users,FileText,Library,Calendar,Hash,Globe,FileDigit,BookCopy,Star,Eye,Archive,TrendingUp,Zap
} from 'lucide-vue-next'

// Props
const props = defineProps<{
  book: any
  stats: any
}>()

// Breadcrumbs
const breadcrumbs = [
  { title: 'Dashboard', href: '/admin/dashboard' },
  { title: 'Libros', href: '/admin/books' },
  { title: props.book.title, href: `/admin/books/${props.book.id}` }
]

// Methods
function editBook() {
  router.get(`/admin/books/${props.book.id}/edit`)
}

// Contributor type labels
const contributorTypes = {
  author: 'Autor',
  editor: 'Editor',
  translator: 'Traductor',
  illustrator: 'Ilustrador',
  prologuist: 'Prologuista'
}

// Book type labels and styling
const bookTypeLabels: Record<string, string> = {
  digital: 'Digital',
  physical: 'Físico',
  both: 'Híbrido'
}

const bookTypeColors: Record<string, string> = {
  digital: 'bg-blue-500/10 text-blue-600 border-blue-200 dark:bg-blue-500/20 dark:text-blue-400 dark:border-blue-800',
  physical: 'bg-emerald-500/10 text-emerald-600 border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-400 dark:border-emerald-800',
  both: 'bg-purple-500/10 text-purple-600 border-purple-200 dark:bg-purple-500/20 dark:text-purple-400 dark:border-purple-800'
}

const bookTypeIcons: Record<string, any> = {
  digital: FileDigit,
  physical: BookCopy,
  both: Globe
}

// Format date
const formatDate = (dateString: string) => {
  return new Date(dateString).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Get status badge
const getStatusBadge = (book: any) => {
  if (!book.is_active) {
    return { label: 'Inactivo', variant: 'destructive' as const, icon: Archive }
  }
  if (book.featured) {
    return { label: 'Destacado', variant: 'default' as const, icon: Star }
  }
  return { label: 'Activo', variant: 'default' as const, icon: Eye }
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-8">
      <!-- Header Mejorado -->
      <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-6">
        <div class="flex items-start gap-4 flex-1">
          <Button variant="outline" size="sm" as-child class="flex-shrink-0">
            <a href="/admin/books">
              <ArrowLeft class="h-4 w-4 mr-2" />
              Volver
            </a>
          </Button>
          <div class="min-w-0 flex-1">
            <div class="flex items-center gap-3 mb-2">
              <Badge :class="bookTypeColors[book.book_type]" class="font-medium">
                <component :is="bookTypeIcons[book.book_type]" class="w-3 h-3 mr-1" />
                {{ bookTypeLabels[book.book_type] }}
              </Badge>
              <Badge :variant="getStatusBadge(book).variant" class="font-medium">
                <component :is="getStatusBadge(book).icon" class="w-3 h-3 mr-1" />
                {{ getStatusBadge(book).label }}
              </Badge>
            </div>
            <h1 class="text-3xl font-bold tracking-tight text-foreground line-clamp-2">
              {{ book.title }}
            </h1>
            <p class="text-muted-foreground mt-2 flex items-center gap-2">
              <Library class="w-4 h-4" />
              Detalles completos del libro en el catálogo
            </p>
          </div>
        </div>
        <Button @click="editBook" class="bg-primary text-primary-foreground hover:bg-primary/90">
          <Edit class="h-4 w-4 mr-2" />
          Editar Libro
        </Button>
      </div>

      <!-- Stats Cards Mejoradas -->
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Descargas -->
        <Card class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-primary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1">
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground mb-2">
                  Total Descargas
                </p>
                <p class="text-3xl font-bold text-foreground">
                  {{ stats.total_downloads || 0 }}
                </p>
                <div class="flex items-center gap-1 mt-2 text-xs text-success">
                  <TrendingUp class="w-3 h-3" />
                  <span>+15% este mes</span>
                </div>
              </div>
              <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <Download class="w-6 h-6 text-primary" />
              </div>
            </div>
          </CardContent>
          <div class="h-1 bg-gradient-to-r from-primary to-primary/60 w-0 group-hover:w-full transition-all duration-500"></div>
        </Card>

        <!-- Préstamos -->
        <Card class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-secondary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1">
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground mb-2">
                  Total Préstamos
                </p>
                <p class="text-3xl font-bold text-foreground">
                  {{ stats.total_loans || 0 }}
                </p>
                <div class="flex items-center gap-1 mt-2 text-xs text-success">
                  <TrendingUp class="w-3 h-3" />
                  <span>+8% este mes</span>
                </div>
              </div>
              <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <Users class="w-6 h-6 text-secondary" />
              </div>
            </div>
          </CardContent>
          <div class="h-1 bg-gradient-to-r from-secondary to-secondary/60 w-0 group-hover:w-full transition-all duration-500"></div>
        </Card>

        <!-- Vistas -->
        <Card class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-primary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1">
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground mb-2">
                  Total Vistas
                </p>
                <p class="text-3xl font-bold text-foreground">
                  {{ stats.total_views || 0 }}
                </p>
                <div class="flex items-center gap-1 mt-2 text-xs text-success">
                  <TrendingUp class="w-3 h-3" />
                  <span>+12% este mes</span>
                </div>
              </div>
              <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <Eye class="w-6 h-6 text-primary" />
              </div>
            </div>
          </CardContent>
          <div class="h-1 bg-gradient-to-r from-primary to-primary/60 w-0 group-hover:w-full transition-all duration-500"></div>
        </Card>

        <!-- Copias Disponibles -->
        <Card class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-secondary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1">
          <CardContent class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground mb-2">
                  Copias Disponibles
                </p>
                <p class="text-3xl font-bold text-foreground">
                  {{ book.available_copies_count || 0 }}
                </p>
                <div class="flex items-center gap-1 mt-2 text-xs text-muted-foreground">
                  <BookCopy class="w-3 h-3" />
                  <span>{{ book.physical_copies_count || 0 }} totales</span>
                </div>
              </div>
              <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <BookCopy class="w-6 h-6 text-secondary" />
              </div>
            </div>
          </CardContent>
          <div class="h-1 bg-gradient-to-r from-secondary to-secondary/60 w-0 group-hover:w-full transition-all duration-500"></div>
        </Card>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Información Principal -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Información Básica Mejorada -->
          <Card class="bg-card rounded-xl border border-border shadow-lg">
            <CardHeader class="pb-4">
              <CardTitle class="flex items-center gap-2 text-xl">
                <BookOpen class="h-5 w-5 text-primary" />
                Información Básica del Libro
              </CardTitle>
              <CardDescription>
                Información principal y detalles de identificación
              </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
              <!-- Título y ISBN -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <Label class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                    <FileText class="w-4 h-4" />
                    Título del Libro
                  </Label>
                  <p class="text-foreground font-medium text-lg">{{ book.title }}</p>
                </div>
                <div class="space-y-2">
                  <Label class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                    <Hash class="w-4 h-4" />
                    ISBN
                  </Label>
                  <p class="text-foreground font-mono font-medium bg-muted/30 p-2 rounded-lg">{{ book.isbn }}</p>
                </div>
              </div>

              <!-- Editorial e Idioma -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <Label class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                    <Library class="w-4 h-4" />
                    Editorial
                  </Label>
                  <div class="flex items-center gap-2">
                    <Badge variant="outline" class="bg-secondary/10">
                      {{ book.publisher?.name || 'No especificada' }}
                    </Badge>
                    <span v-if="book.publisher?.city" class="text-sm text-muted-foreground">
                      • {{ book.publisher.city }}, {{ book.publisher.country }}
                    </span>
                  </div>
                </div>
                <div class="space-y-2">
                  <Label class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                    <Globe class="w-4 h-4" />
                    Idioma
                  </Label>
                  <Badge variant="outline" class="bg-primary/10">
                    {{ book.language?.native_name || book.language_code }}
                  </Badge>
                </div>
              </div>

              <!-- Páginas y Año -->
              <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                  <Label class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                    <FileText class="w-4 h-4" />
                    Número de Páginas
                  </Label>
                  <div class="flex items-center gap-2">
                    <Badge variant="outline" class="font-mono">
                      {{ book.pages }} páginas
                    </Badge>
                  </div>
                </div>
                <div class="space-y-2">
                  <Label class="text-sm font-medium text-muted-foreground flex items-center gap-2">
                    <Calendar class="w-4 h-4" />
                    Año de Publicación
                  </Label>
                  <Badge variant="outline" class="font-mono">
                    {{ book.publication_year }}
                  </Badge>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Descripción Mejorada -->
          <Card v-if="book.details?.description" class="bg-card rounded-xl border border-border shadow-lg">
            <CardHeader class="pb-4">
              <CardTitle class="flex items-center gap-2 text-xl">
                <FileText class="h-5 w-5 text-primary" />
                Descripción del Contenido
              </CardTitle>
              <CardDescription>
                Resumen y detalles del contenido del libro
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="prose prose-sm max-w-none">
                <p class="text-foreground leading-relaxed whitespace-pre-line">{{ book.details.description }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Contribuidores Mejorados -->
          <Card class="bg-card rounded-xl border border-border shadow-lg">
            <CardHeader class="pb-4">
              <CardTitle class="flex items-center gap-2 text-xl">
                <Users class="h-5 w-5 text-primary" />
                Autores y Contribuidores
              </CardTitle>
              <CardDescription>
                Personas que participaron en la creación de este libro
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div
                  v-for="contributor in book.contributors"
                  :key="contributor.id"
                  class="group p-4 border border-border rounded-xl hover:border-primary/30 transition-all duration-300 hover:shadow-md"
                >
                  <div class="flex items-start justify-between">
                    <div class="space-y-2 flex-1">
                      <h4 class="font-semibold text-foreground group-hover:text-primary transition-colors">
                        {{ contributor.full_name }}
                      </h4>
                      <Badge variant="outline" class="bg-secondary/10">
                        {{ contributorTypes[contributor.contributor_type as keyof typeof contributorTypes] }}
                      </Badge>
                    </div>
                    <Badge variant="secondary" class="flex-shrink-0 ml-2">
                      #{{ contributor.sequence_number }}
                    </Badge>
                  </div>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Categorías Mejoradas -->
          <Card class="bg-card rounded-xl border border-border shadow-lg">
            <CardHeader class="pb-4">
              <CardTitle class="flex items-center gap-2 text-xl">
                <Library class="h-5 w-5 text-primary" />
                Categorías y Temáticas
              </CardTitle>
              <CardDescription>
                Áreas de conocimiento y clasificaciones del libro
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="flex flex-wrap gap-2">
                <Badge 
                  v-for="category in book.categories" 
                  :key="category.id"
                  variant="secondary"
                  class="bg-primary/10 text-primary border-primary/20 hover:bg-primary/20 transition-colors"
                >
                  {{ category.name }}
                </Badge>
              </div>
            </CardContent>
          </Card>
        </div>

        <!-- Sidebar Mejorada -->
        <div class="space-y-6">
          <!-- Metadatos Mejorados -->
          <Card class="bg-card rounded-xl border border-border shadow-lg">
            <CardHeader class="pb-4">
              <CardTitle class="flex items-center gap-2">
                <Zap class="h-5 w-5 text-primary" />
                Información Adicional
              </CardTitle>
              <CardDescription>
                Metadatos y fechas importantes
              </CardDescription>
            </CardHeader>
            <CardContent class="space-y-4">
              <div v-if="book.details?.edition" class="space-y-2">
                <Label class="text-sm font-medium text-muted-foreground">Edición</Label>
                <p class="text-foreground font-medium">{{ book.details.edition }}</p>
              </div>
              
              <div v-if="book.details?.keywords" class="space-y-2">
                <Label class="text-sm font-medium text-muted-foreground">Palabras Clave</Label>
                <div class="flex flex-wrap gap-1">
                  <Badge 
                    v-for="keyword in book.details.keywords.split(',')" 
                    :key="keyword.trim()"
                    variant="outline"
                    class="text-xs"
                  >
                    {{ keyword.trim() }}
                  </Badge>
                </div>
              </div>

              <div class="space-y-2 pt-4 border-t border-border">
                <Label class="text-sm font-medium text-muted-foreground">Fecha de Creación</Label>
                <p class="text-foreground text-sm">{{ formatDate(book.created_at) }}</p>
              </div>

              <div class="space-y-2">
                <Label class="text-sm font-medium text-muted-foreground">Última Actualización</Label>
                <p class="text-foreground text-sm">{{ formatDate(book.updated_at) }}</p>
              </div>
            </CardContent>
          </Card>

          <!-- Copias Físicas Mejoradas -->
          <Card v-if="book.book_type !== 'digital' && book.physicalCopies && book.physicalCopies.length > 0" 
                class="bg-card rounded-xl border border-border shadow-lg">
            <CardHeader class="pb-4">
              <CardTitle class="flex items-center gap-2">
                <BookCopy class="h-5 w-5 text-primary" />
                Copias Físicas
              </CardTitle>
              <CardDescription>
                {{ book.physical_copies_count }} copias totales, {{ book.available_copies_count }} disponibles
              </CardDescription>
            </CardHeader>
            <CardContent>
              <div class="space-y-3">
                <div
                  v-for="copy in book.physicalCopies"
                  :key="copy.id"
                  class="flex items-center justify-between p-3 border border-border rounded-lg hover:border-primary/30 transition-colors"
                >
                  <div class="space-y-1">
                    <p class="font-medium text-sm">{{ copy.barcode }}</p>
                    <p class="text-xs text-muted-foreground">Copia #{{ copy.copy_number }}</p>
                  </div>
                  <Badge 
                    :variant="copy.status === 'available' ? 'default' : 'secondary'"
                    :class="copy.status === 'available' ? 'bg-success/10 text-success border-success/20' : 'bg-warning/10 text-warning border-warning/20'"
                  >
                    {{ copy.status === 'available' ? 'Disponible' : 'Prestado' }}
                  </Badge>
                </div>
              </div>
            </CardContent>
          </Card>

          <!-- Acciones Rápidas -->
          <Card class="bg-card rounded-xl border border-border shadow-lg">
            <CardHeader class="pb-4">
              <CardTitle class="flex items-center gap-2">
                <Zap class="h-5 w-5 text-primary" />
                Acciones Rápidas
              </CardTitle>
            </CardHeader>
            <CardContent class="space-y-3">
              <Button @click="editBook" variant="outline" class="w-full justify-start">
                <Edit class="h-4 w-4 mr-2" />
                Editar Información
              </Button>
              <Button variant="outline" class="w-full justify-start" as-child>
                <a :href="`/admin/books/${book.id}/loans`">
                  <Users class="h-4 w-4 mr-2" />
                  Ver Préstamos
                </a>
              </Button>
              <Button variant="outline" class="w-full justify-start" as-child>
                <a :href="`/admin/books/${book.id}/downloads`">
                  <Download class="h-4 w-4 mr-2" />
                  Ver Descargas
                </a>
              </Button>
            </CardContent>
          </Card>
        </div>
      </div>
    </div>
  </AppLayout>
</template>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.prose {
  color: hsl(var(--foreground));
}

.prose p {
  margin-top: 0;
  margin-bottom: 0;
}
</style>