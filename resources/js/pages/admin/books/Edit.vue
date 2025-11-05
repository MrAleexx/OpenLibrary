<script setup lang="ts">
import { reactive, ref, computed, onMounted } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import type { Book, BookFormData } from '@/types/book'
import {
  Card,
  CardContent,
  CardDescription,
  CardHeader,
  CardTitle,
  CardFooter
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Label } from '@/components/ui/label'
import { Textarea } from '@/components/ui/textarea'
import { Badge } from '@/components/ui/badge'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { Checkbox } from '@/components/ui/checkbox'
import {
  ArrowLeft,
  Plus,
  Trash2,
  User,
  BookOpen,
  Save,
  AlertCircle,
  Info,
  Upload,
  Image,
  FileText,
  X,
  Search,
  Loader2,
  Eye,
  EyeOff
} from 'lucide-vue-next'
import { Alert, AlertDescription } from '@/components/ui/alert'
import {
  Tooltip,
  TooltipContent,
  TooltipProvider,
  TooltipTrigger,
} from '@/components/ui/tooltip'

// Define el tipo localmente
interface Contributor {
  full_name: string
  contributor_type: 'author' | 'editor' | 'translator' | 'illustrator' | 'prologuist'
  sequence_number: number
}

// Props
const props = defineProps<{
  book: Book
  categories: Array<{
    id: number
    name: string
    full_path: string
    breadcrumb: string
  }>
  publishers: Array<{ id: number; name: string; city: string; country: string }>
  languages: Array<{ code: string; name: string; native_name: string }>
  book_types: Array<{ value: string; label: string }>
  errors?: Record<string, string>
}>()

// Breadcrumbs
const breadcrumbs = [
  { title: 'Dashboard', href: '/admin/dashboard' },
  { title: 'Libros', href: '/admin/books' },
  { title: 'Editar Libro', href: `/admin/books/${props.book.id}/edit` }
]

// File refs
const coverImageInput = ref<HTMLInputElement>()
const pdfFileInput = ref<HTMLInputElement>()

// Preview states
const coverPreview = ref<string | null>(props.book.cover_image ? `/storage/${props.book.cover_image}` : null)
const pdfFileName = ref<string | null>(props.book.pdf_file ? props.book.pdf_file.split('/').pop() || null : null)

// UI States
const isLoading = ref(false)
const searchCategory = ref('')
const showDescriptionPreview = ref(false)
const isDraggingCover = ref(false)
const isDraggingPdf = ref(false)

// Form state - inicializar con los datos del libro
const form = reactive<BookFormData>({
  title: props.book?.title || '',
  publisher_id: props.book?.publisher_id || null,
  isbn: props.book?.isbn || '',
  language_code: props.book?.language_code || 'es',
  pages: props.book?.pages?.toString() || '',
  publication_year: props.book?.publication_year?.toString() || new Date().getFullYear().toString(),
  book_type: props.book?.book_type || 'digital',
  featured: props.book?.featured || false,
  is_active: props.book?.is_active ?? true,
  downloadable: props.book?.downloadable ?? true,
  description: props.book?.details?.description || '',
  edition: props.book?.details?.edition || '1ra',
  keywords: props.book?.details?.keywords || '',
  categories: props.book?.categories ? props.book.categories.map(c => c.id.toString()) : [],
  contributors: props.book?.contributors && props.book.contributors.length > 0
    ? props.book.contributors.map(c => ({
      full_name: c.full_name,
      contributor_type: c.contributor_type,
      sequence_number: c.sequence_number
    }))
    : [{
      full_name: '',
      contributor_type: 'author',
      sequence_number: 1
    }],
  cover_image: null,
  pdf_file: null
})

// Computed
const filteredCategories = computed(() => {
  if (!searchCategory.value) return props.categories
  return props.categories.filter(category =>
    category.name.toLowerCase().includes(searchCategory.value.toLowerCase()) ||
    category.full_path.toLowerCase().includes(searchCategory.value.toLowerCase())
  )
})

const selectedCategoriesCount = computed(() => form.categories.length)
const selectedContributorsCount = computed(() => form.contributors.length)

const progress = computed(() => {
  const fields = [
    form.title, form.isbn, form.pages, form.categories.length,
    form.contributors.length, form.language_code, form.book_type
  ]
  const filled = fields.filter(field =>
    field && (typeof field === 'string' ? field.trim() !== '' : true)
  ).length
  return Math.round((filled / fields.length) * 100)
})

// Methods
function addContributor(): void {
  form.contributors.push({
    full_name: '',
    contributor_type: 'author',
    sequence_number: form.contributors.length + 1
  })
}

function removeContributor(index: number): void {
  if (form.contributors.length > 1) {
    form.contributors.splice(index, 1)
    // Reordenar sequence numbers
    form.contributors.forEach((contributor: Contributor, idx: number) => {
      contributor.sequence_number = idx + 1
    })
  }
}

// Cover Image Methods
function triggerCoverUpload(): void {
  coverImageInput.value?.click()
}

function handleCoverChange(event: Event): void {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (file) {
    processCoverFile(file)
  }
}

function processCoverFile(file: File): void {
  // Validar tipo de archivo
  if (!file.type.startsWith('image/')) {
    alert('Por favor, selecciona un archivo de imagen válido (JPEG, PNG, JPG, GIF)')
    return
  }

  // Validar tamaño (2MB)
  if (file.size > 2 * 1024 * 1024) {
    alert('La imagen no debe superar los 2MB')
    return
  }

  form.cover_image = file

  // Crear preview
  const reader = new FileReader()
  reader.onload = (e) => {
    coverPreview.value = e.target?.result as string
  }
  reader.readAsDataURL(file)
}

function removeCover(): void {
  form.cover_image = null
  coverPreview.value = props.book.cover_image ? `/storage/${props.book.cover_image}` : null
  if (coverImageInput.value) {
    coverImageInput.value.value = ''
  }
}

// PDF Methods
function triggerPdfUpload(): void {
  pdfFileInput.value?.click()
}

function handlePdfChange(event: Event): void {
  const target = event.target as HTMLInputElement
  const file = target.files?.[0]

  if (file) {
    processPdfFile(file)
  }
}

function processPdfFile(file: File): void {
  // Validar tipo de archivo
  if (file.type !== 'application/pdf') {
    alert('Por favor, selecciona un archivo PDF válido')
    return
  }

  // Validar tamaño (10MB)
  if (file.size > 10 * 1024 * 1024) {
    alert('El PDF no debe superar los 10MB')
    return
  }

  form.pdf_file = file
  pdfFileName.value = file.name
}

function removePdf(): void {
  form.pdf_file = null
  pdfFileName.value = props.book.pdf_file ? props.book.pdf_file.split('/').pop() || null : null
  if (pdfFileInput.value) {
    pdfFileInput.value.value = ''
  }
}

// Drag and Drop handlers
function handleDragOver(event: DragEvent, type: 'cover' | 'pdf'): void {
  event.preventDefault()
  if (type === 'cover') isDraggingCover.value = true
  else isDraggingPdf.value = true
}

function handleDragLeave(event: DragEvent, type: 'cover' | 'pdf'): void {
  event.preventDefault()
  if (type === 'cover') isDraggingCover.value = false
  else isDraggingPdf.value = false
}

function handleDrop(event: DragEvent, type: 'cover' | 'pdf'): void {
  event.preventDefault()
  if (type === 'cover') isDraggingCover.value = false
  else isDraggingPdf.value = false

  const files = event.dataTransfer?.files
  if (files && files.length > 0) {
    const file = files[0]
    if (type === 'cover') {
      processCoverFile(file)
    } else {
      processPdfFile(file)
    }
  }
}

function formatFileSize(bytes: number): string {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i]
}

async function submit(): Promise<void> {
  isLoading.value = true

  // Crear FormData para enviar archivos
  const formData = new FormData()

  // Agregar campos del formulario
  formData.append('title', form.title)
  formData.append('isbn', form.isbn)
  formData.append('language_code', form.language_code)
  formData.append('pages', form.pages.toString())
  formData.append('book_type', form.book_type)
  
  // CORREGIDO: Convertir booleanos a strings compatibles
  formData.append('featured', form.featured ? '1' : '0')
  formData.append('is_active', form.is_active ? '1' : '0')
  formData.append('downloadable', form.downloadable ? '1' : '0')
  
  formData.append('description', form.description)
  formData.append('edition', form.edition)
  formData.append('keywords', form.keywords)
  formData.append('_method', 'PUT')

  if (form.publisher_id) {
    formData.append('publisher_id', form.publisher_id.toString())
  }

  if (form.publication_year) {
    formData.append('publication_year', form.publication_year.toString())
  }

  // Agregar categorías con tipo explícito
  form.categories.forEach((categoryId: string) => {
    formData.append('categories[]', categoryId)
  })

  // Agregar contribuidores con tipo explícito
  form.contributors.forEach((contributor: Contributor, index: number) => {
    formData.append(`contributors[${index}][full_name]`, contributor.full_name)
    formData.append(`contributors[${index}][contributor_type]`, contributor.contributor_type)
    formData.append(`contributors[${index}][sequence_number]`, contributor.sequence_number.toString())
  })

  // Agregar archivos si existen (solo si se subieron nuevos)
  if (form.cover_image) {
    formData.append('cover_image', form.cover_image)
  }

  if (form.pdf_file) {
    formData.append('pdf_file', form.pdf_file)
  }

  try {
    await router.post(`/admin/books/${props.book.id}`, formData, {
      forceFormData: true,
      onError: (errors) => {
        console.error('Error al actualizar libro:', errors)
        isLoading.value = false
      },
      onFinish: () => {
        isLoading.value = false
      }
    })
  } catch (error) {
    isLoading.value = false
    console.error('Error al enviar formulario:', error)
  }
}

// Contributor type labels
const contributorTypes = {
  author: 'Autor',
  editor: 'Editor',
  translator: 'Traductor',
  illustrator: 'Ilustrador',
  prologuist: 'Prologuista'
}

// Validación de campos requeridos
const hasEmptyRequiredFields = (): boolean => {
  return !form.title || !form.isbn || !form.pages || form.categories.length === 0
}

// Auto-save description to localStorage
onMounted(() => {
  const savedDescription = localStorage.getItem('book_description_draft')
  if (savedDescription && !form.description) {
    form.description = savedDescription
  }
})

function saveDescriptionDraft(): void {
  if (form.description) {
    localStorage.setItem('book_description_draft', form.description)
  }
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <!-- Header con Progress Bar -->
      <div class="space-y-4">
        <div class="flex items-center justify-between">
          <div class="flex-1">
            <h1 class="text-3xl font-bold tracking-tight text-foreground">
              Editar Libro
            </h1>
            <p class="text-muted-foreground mt-1">
              Actualiza la información del libro: {{ book?.title }}
            </p>
          </div>
          <Button variant="outline" size="sm" as-child class="border-border hover:bg-accent">
            <a href="/admin/books">
              <ArrowLeft class="h-4 w-4 mr-2" />
              Volver
            </a>
          </Button>
        </div>

        <!-- Progress Bar -->
        <div class="space-y-2">
          <div class="flex justify-between text-sm">
            <span class="text-muted-foreground">Progreso del formulario</span>
            <span class="font-medium text-primary">{{ progress }}%</span>
          </div>
          <div class="w-full bg-muted rounded-full h-2">
            <div
              class="bg-primary h-2 rounded-full transition-all duration-500 ease-out"
              :style="{ width: `${progress}%` }"></div>
          </div>
        </div>
      </div>

      <!-- Alert de errores mejorado -->
      <Alert v-if="Object.keys(props.errors || {}).length > 0" variant="destructive"
        class="bg-destructive/10 border-destructive/20 animate-in fade-in-50">
        <AlertCircle class="h-4 w-4" />
        <AlertDescription class="flex-1">
          <div class="space-y-2">
            <p class="font-medium">Se encontraron los siguientes errores:</p>
            <ul class="text-sm space-y-1">
              <li v-for="(error, field) in props.errors" :key="field" class="flex items-start gap-2">
                <div class="w-1 h-1 bg-destructive rounded-full mt-2 flex-shrink-0"></div>
                <span>{{ error }}</span>
              </li>
            </ul>
          </div>
        </AlertDescription>
      </Alert>

      <form @submit.prevent="submit" class="space-y-6" enctype="multipart/form-data">
        <!-- Inputs de archivo ocultos -->
        <input type="file" ref="coverImageInput" accept="image/*" class="hidden" @change="handleCoverChange" />
        <input type="file" ref="pdfFileInput" accept=".pdf" class="hidden" @change="handlePdfChange" />

        <!-- Información Básica -->
        <Card class="group hover:shadow-md transition-all duration-300 border-border bg-card">
          <CardHeader class="pb-4">
            <CardTitle class="flex items-center gap-3 text-xl text-card-foreground">
              <div class="p-2 bg-primary/10 rounded-lg">
                <BookOpen class="h-6 w-6 text-primary" />
              </div>
              <div>
                Información Básica
                <Badge variant="secondary" class="ml-2">Requerido</Badge>
              </div>
            </CardTitle>
            <CardDescription class="text-muted-foreground text-base">
              Información principal e identificadora del libro
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-6 pt-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Título -->
              <div class="md:col-span-2">
                <Label for="title" class="required text-base font-semibold text-card-foreground">Título del Libro</Label>
                <Input id="title" v-model="form.title" placeholder="Ingresa el título completo del libro"
                  :class="errors?.title ? 'border-destructive focus-visible:ring-destructive' : 'border-input focus-visible:ring-ring'"
                  class="h-12 text-base mt-2 bg-background text-foreground" required />
                <p v-if="errors?.title" class="text-destructive text-sm mt-2 flex items-center gap-1">
                  <AlertCircle class="h-3 w-3" />
                  {{ errors.title }}
                </p>
              </div>

              <!-- ISBN -->
              <div>
                <Label for="isbn" class="required text-base font-semibold text-card-foreground">ISBN</Label>
                <Input id="isbn" v-model="form.isbn" placeholder="978-612-00123-4-7"
                  :class="errors?.isbn ? 'border-destructive focus-visible:ring-destructive' : 'border-input focus-visible:ring-ring'"
                  class="h-12 text-base mt-2 font-mono bg-background text-foreground" required />
                <p v-if="errors?.isbn" class="text-destructive text-sm mt-2 flex items-center gap-1">
                  <AlertCircle class="h-3 w-3" />
                  {{ errors.isbn }}
                </p>
              </div>

              <!-- Editorial -->
              <div>
                <Label for="publisher_id" class="text-base font-semibold text-card-foreground">Editorial</Label>
                <Select v-model="form.publisher_id" 
                  :class="errors?.publisher_id ? 'border-destructive' : ''">
                  <SelectTrigger class="h-12 text-base mt-2 bg-background text-foreground border-input">
                    <SelectValue placeholder="Selecciona una editorial" />
                  </SelectTrigger>
                  <SelectContent class="bg-popover text-popover-foreground border-border">
                    <SelectItem :value="null" class="focus:bg-accent focus:text-accent-foreground">Sin editorial</SelectItem>
                    <SelectItem 
                      v-for="publisher in publishers" 
                      :key="publisher.id" 
                      :value="publisher.id"
                      class="text-base focus:bg-accent focus:text-accent-foreground"
                    >
                      <div class="flex flex-col">
                        <span class="font-medium">{{ publisher.name }}</span>
                        <span class="text-sm text-muted-foreground">
                          {{ publisher.city }}, {{ publisher.country }}
                        </span>
                      </div>
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="errors?.publisher_id" class="text-destructive text-sm mt-2 flex items-center gap-1">
                  <AlertCircle class="h-3 w-3" />
                  {{ errors.publisher_id }}
                </p>
              </div>

              <!-- Páginas y Año -->
              <div>
                <Label for="pages" class="required text-base font-semibold text-card-foreground">Número de Páginas</Label>
                <Input id="pages" v-model="form.pages" type="number" min="1"
                  :class="errors?.pages ? 'border-destructive focus-visible:ring-destructive' : 'border-input focus-visible:ring-ring'"
                  class="h-12 text-base mt-2 bg-background text-foreground" required />
                <p v-if="errors?.pages" class="text-destructive text-sm mt-2 flex items-center gap-1">
                  <AlertCircle class="h-3 w-3" />
                  {{ errors.pages }}
                </p>
              </div>

              <div>
                <Label for="publication_year" class="text-base font-semibold text-card-foreground">Año de Publicación</Label>
                <Input id="publication_year" v-model="form.publication_year" type="number" :min="1800"
                  :max="new Date().getFullYear() + 1"
                  :class="errors?.publication_year ? 'border-destructive focus-visible:ring-destructive' : 'border-input focus-visible:ring-ring'"
                  class="h-12 text-base mt-2 bg-background text-foreground" />
                <p v-if="errors?.publication_year" class="text-destructive text-sm mt-2 flex items-center gap-1">
                  <AlertCircle class="h-3 w-3" />
                  {{ errors.publication_year }}
                </p>
              </div>

              <!-- Idioma -->
              <div>
                <Label for="language_code" class="required text-base font-semibold text-card-foreground">Idioma</Label>
                <Select v-model="form.language_code" 
                  :class="errors?.language_code ? 'border-destructive' : ''">
                  <SelectTrigger class="h-12 text-base mt-2 bg-background text-foreground border-input">
                    <SelectValue placeholder="Selecciona un idioma" />
                  </SelectTrigger>
                  <SelectContent class="bg-popover text-popover-foreground border-border">
                    <SelectItem 
                      v-for="language in languages" 
                      :key="language.code" 
                      :value="language.code"
                      class="text-base focus:bg-accent focus:text-accent-foreground"
                    >
                      {{ language.native_name }} ({{ language.name }})
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="errors?.language_code" class="text-destructive text-sm mt-2 flex items-center gap-1">
                  <AlertCircle class="h-3 w-3" />
                  {{ errors.language_code }}
                </p>
              </div>

              <!-- Tipo de Libro -->
              <div>
                <Label for="book_type" class="required text-base font-semibold text-card-foreground">Tipo de Libro</Label>
                <Select v-model="form.book_type" 
                  :class="errors?.book_type ? 'border-destructive' : ''">
                  <SelectTrigger class="h-12 text-base mt-2 bg-background text-foreground border-input">
                    <SelectValue placeholder="Selecciona el tipo" />
                  </SelectTrigger>
                  <SelectContent class="bg-popover text-popover-foreground border-border">
                    <SelectItem 
                      v-for="type in book_types" 
                      :key="type.value" 
                      :value="type.value"
                      class="text-base focus:bg-accent focus:text-accent-foreground"
                    >
                      {{ type.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="errors?.book_type" class="text-destructive text-sm mt-2 flex items-center gap-1">
                  <AlertCircle class="h-3 w-3" />
                  {{ errors.book_type }}
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Archivos: Cover y PDF -->
        <Card class="group hover:shadow-md transition-all duration-300 border-border bg-card">
          <CardHeader class="pb-4">
            <CardTitle class="flex items-center gap-3 text-xl text-card-foreground">
              <div class="p-2 bg-primary/10 rounded-lg">
                <Image class="h-6 w-6 text-primary" />
              </div>
              Archivos del Libro
            </CardTitle>
            <CardDescription class="text-muted-foreground text-base">
              Actualiza la portada y el archivo PDF del libro
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-8 pt-4">
            <!-- Cover Image -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <div class="space-y-4">
                <Label class="flex items-center gap-2 text-base font-semibold text-card-foreground">
                  <Image class="h-5 w-5" />
                  Portada del Libro
                </Label>
                <p class="text-sm text-muted-foreground">
                  Formatos: JPEG, PNG, JPG, GIF. Máximo: 2MB
                  <span v-if="book.cover_image" class="text-green-600 font-medium"> (Portada actual disponible)</span>
                </p>

                <div
                  class="border-2 border-dashed rounded-xl p-8 text-center transition-all duration-300 cursor-pointer group/upload border-border hover:border-primary/50 hover:bg-accent/50"
                  :class="[
                    isDraggingCover ? 'border-primary bg-accent scale-[1.02]' : '',
                    coverPreview ? 'bg-green-50 border-green-200 dark:bg-green-950/20 dark:border-green-800' : ''
                  ]" 
                  @click="triggerCoverUpload" 
                  @dragover="(e) => handleDragOver(e, 'cover')"
                  @dragleave="(e) => handleDragLeave(e, 'cover')" 
                  @drop="(e) => handleDrop(e, 'cover')">
                  <div class="space-y-4">
                    <div class="relative mx-auto w-16 h-16">
                      <Upload class="h-8 w-8 mx-auto text-muted-foreground group-hover/upload:text-primary transition-colors absolute inset-0 m-auto" />
                      <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-primary rounded-full flex items-center justify-center">
                        <Image class="h-3 w-3 text-primary-foreground" />
                      </div>
                    </div>
                    <div>
                      <p class="font-medium text-lg group-hover/upload:text-primary transition-colors text-card-foreground">
                        {{ coverPreview ? (form.cover_image ? 'Nueva portada lista ✓' : 'Portada actual disponible') : 'Haz clic para cambiar la portada' }}
                      </p>
                      <p class="text-sm text-muted-foreground mt-1">
                        {{ coverPreview ? 'Arrastra otra imagen para cambiar' : 'o arrastra y suelta la imagen aquí' }}
                      </p>
                    </div>
                  </div>
                </div>

                <p v-if="errors?.cover_image" class="text-destructive text-sm flex items-center gap-1">
                  <AlertCircle class="h-3 w-3" />
                  {{ errors.cover_image }}
                </p>
              </div>

              <!-- Cover Preview -->
              <div class="space-y-4">
                <Label class="text-base font-semibold text-card-foreground">Vista Previa de la Portada</Label>
                <div class="aspect-[3/4] max-w-xs border-2 border-dashed border-border rounded-xl overflow-hidden bg-muted/20 group/preview">
                  <div v-if="coverPreview" class="h-full flex items-center justify-center relative">
                    <img :src="coverPreview" alt="Vista previa de la portada"
                      class="w-full h-full object-cover group-hover/preview:scale-105 transition-transform duration-300" />
                    <div class="absolute inset-0 bg-black/50 opacity-0 group-hover/preview:opacity-100 transition-opacity duration-300 flex items-center justify-center">
                      <Button type="button" variant="destructive" size="sm"
                        class="scale-90 group-hover/preview:scale-100 transition-transform duration-300"
                        @click.stop="removeCover">
                        <Trash2 class="h-4 w-4 mr-1" />
                        {{ form.cover_image ? 'Cancelar' : 'Eliminar' }}
                      </Button>
                    </div>
                  </div>
                  <div v-else class="h-full flex flex-col items-center justify-center text-muted-foreground p-6">
                    <Image class="h-16 w-16 mb-3 opacity-30" />
                    <p class="text-sm text-center font-medium">No hay portada disponible</p>
                    <p class="text-xs text-center mt-1">Sube una imagen para ver la vista previa</p>
                  </div>
                </div>
              </div>
            </div>

            <!-- PDF File -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
              <div class="space-y-4">
                <Label class="flex items-center gap-2 text-base font-semibold text-card-foreground">
                  <FileText class="h-5 w-5" />
                  Archivo PDF
                </Label>
                <p class="text-sm text-muted-foreground">
                  Formato: PDF. Máximo: 10MB
                  <span v-if="book.pdf_file" class="text-green-600 font-medium"> (PDF actual disponible)</span>
                </p>

                <div
                  class="border-2 border-dashed rounded-xl p-8 text-center transition-all duration-300 cursor-pointer group/upload border-border hover:border-primary/50 hover:bg-accent/50"
                  :class="[
                    isDraggingPdf ? 'border-primary bg-accent scale-[1.02]' : '',
                    pdfFileName ? 'bg-green-50 border-green-200 dark:bg-green-950/20 dark:border-green-800' : ''
                  ]" 
                  @click="triggerPdfUpload" 
                  @dragover="(e) => handleDragOver(e, 'pdf')"
                  @dragleave="(e) => handleDragLeave(e, 'pdf')" 
                  @drop="(e) => handleDrop(e, 'pdf')">
                  <div class="space-y-4">
                    <div class="relative mx-auto w-16 h-16">
                      <Upload class="h-8 w-8 mx-auto text-muted-foreground group-hover/upload:text-primary transition-colors absolute inset-0 m-auto" />
                      <div class="absolute -bottom-1 -right-1 w-6 h-6 bg-primary rounded-full flex items-center justify-center">
                        <FileText class="h-3 w-3 text-primary-foreground" />
                      </div>
                    </div>
                    <div>
                      <p class="font-medium text-lg group-hover/upload:text-primary transition-colors text-card-foreground">
                        {{ pdfFileName ? (form.pdf_file ? 'Nuevo PDF listo ✓' : 'PDF actual disponible') : 'Haz clic para cambiar el PDF' }}
                      </p>
                      <p class="text-sm text-muted-foreground mt-1">
                        {{ pdfFileName ? 'Arrastra otro PDF para cambiar' : 'o arrastra y suelta el archivo aquí' }}
                      </p>
                    </div>
                  </div>
                </div>

                <p v-if="errors?.pdf_file" class="text-destructive text-sm flex items-center gap-1">
                  <AlertCircle class="h-3 w-3" />
                  {{ errors.pdf_file }}
                </p>
              </div>

              <!-- PDF Info -->
              <div class="space-y-4">
                <Label class="text-base font-semibold text-card-foreground">Información del Archivo PDF</Label>
                <div class="border-2 border-dashed border-border rounded-xl p-6 transition-all duration-300 group/pdf bg-muted/20"
                  :class="pdfFileName ? 'bg-green-50 border-green-200 dark:bg-green-950/20 dark:border-green-800' : ''">
                  <div v-if="pdfFileName" class="space-y-4">
                    <div class="flex items-center gap-3">
                      <div class="p-2 bg-primary/10 rounded-lg">
                        <FileText class="h-6 w-6 text-primary" />
                      </div>
                      <div class="flex-1 min-w-0">
                        <p class="font-medium text-lg truncate text-card-foreground">{{ pdfFileName }}</p>
                        <p class="text-sm text-muted-foreground">
                          {{ form.pdf_file ? formatFileSize(form.pdf_file.size) : 'Archivo actual del libro' }}
                        </p>
                      </div>
                    </div>
                    <div class="flex gap-2">
                      <Button type="button" variant="outline" size="sm" class="flex-1 border-border hover:bg-accent hover:text-accent-foreground" @click="removePdf">
                        <Trash2 class="h-4 w-4 mr-1" />
                        {{ form.pdf_file ? 'Cancelar' : 'Eliminar' }}
                      </Button>
                    </div>
                  </div>
                  <div v-else class="text-center text-muted-foreground py-8">
                    <FileText class="h-12 w-12 mx-auto mb-3 opacity-30" />
                    <p class="text-sm font-medium">No hay PDF disponible</p>
                    <p class="text-xs mt-1">Sube un archivo PDF para ver la información</p>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Categorías -->
        <Card class="group hover:shadow-md transition-all duration-300 border-border bg-card">
          <CardHeader class="pb-4">
            <CardTitle class="flex items-center gap-3 text-xl text-card-foreground">
              <div class="p-2 bg-primary/10 rounded-lg">
                <BookOpen class="h-6 w-6 text-primary" />
              </div>
              <div>
                Categorías
                <Badge variant="secondary" class="ml-2">Requerido</Badge>
              </div>
              <TooltipProvider>
                <Tooltip>
                  <TooltipTrigger as-child>
                    <Info class="h-5 w-5 text-muted-foreground cursor-help transition-colors hover:text-primary" />
                  </TooltipTrigger>
                  <TooltipContent class="max-w-sm text-sm bg-popover text-popover-foreground border-border">
                    <p>Selecciona categorías específicas (sin subcategorías) para una mejor organización y búsqueda</p>
                  </TooltipContent>
                </Tooltip>
              </TooltipProvider>
            </CardTitle>
            <CardDescription class="text-muted-foreground text-base">
              Actualiza las categorías específicas a las que pertenece este libro
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4 pt-4">
            <!-- Búsqueda y contador -->
            <div class="flex flex-col sm:flex-row gap-4 justify-between items-start sm:items-center">
              <div class="relative flex-1 max-w-md">
                <Search class="absolute left-3 top-1/2 transform -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input 
                  v-model="searchCategory" 
                  placeholder="Buscar categorías..." 
                  class="pl-10 h-11 bg-background text-foreground border-input"
                />
              </div>
              <Badge variant="outline" class="text-sm px-3 py-1 border-border text-muted-foreground">
                {{ selectedCategoriesCount }} categoría(s) seleccionada(s)
              </Badge>
            </div>

            <!-- Grid de categorías -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 max-h-96 overflow-y-auto p-1 custom-scrollbar">
              <div 
                v-for="category in filteredCategories" 
                :key="category.id" 
                class="flex items-start space-x-3 p-4 border-2 rounded-xl transition-all duration-200 group/category cursor-pointer border-border hover:border-primary/50 hover:bg-accent/50"
                :class="[
                  form.categories.includes(category.id.toString()) 
                    ? 'border-primary bg-accent shadow-sm scale-[1.02]' 
                    : ''
                ]"
                @click="() => {
                  const categoryId = category.id.toString()
                  if (form.categories.includes(categoryId)) {
                    form.categories = form.categories.filter((id: string) => id !== categoryId)
                  } else {
                    form.categories.push(categoryId)
                  }
                }">
                <Checkbox 
                  :id="`category-${category.id}`" 
                  :checked="form.categories.includes(category.id.toString())"
                  class="mt-0.5" 
                  @click.stop
                />
                <Label 
                  :for="`category-${category.id}`" 
                  class="text-sm font-normal cursor-pointer flex-1 space-y-1"
                >
                  <div class="font-semibold text-card-foreground group-hover/category:text-primary transition-colors">
                    {{ category.name }}
                  </div>
                  <div class="text-xs text-muted-foreground line-clamp-2 leading-relaxed">
                    {{ category.full_path }}
                  </div>
                </Label>
                
                <!-- Tooltip con breadcrumb completo -->
                <TooltipProvider>
                  <Tooltip>
                    <TooltipTrigger as-child>
                      <Info class="h-3.5 w-3.5 text-muted-foreground cursor-help shrink-0 mt-0.5 transition-colors hover:text-primary" />
                    </TooltipTrigger>
                    <TooltipContent class="max-w-xs text-xs bg-popover text-popover-foreground border-border">
                      <p class="font-medium">Ruta completa:</p>
                      <p>{{ category.breadcrumb }}</p>
                    </TooltipContent>
                  </Tooltip>
                </TooltipProvider>
              </div>
            </div>

            <!-- Mensaje cuando no hay categorías seleccionadas -->
            <div 
              v-if="form.categories.length === 0"
              class="text-center py-8 border-2 border-dashed border-muted-foreground/20 rounded-xl bg-muted/10"
            >
              <BookOpen class="h-12 w-12 mx-auto mb-3 text-muted-foreground/50" />
              <p class="text-muted-foreground font-medium">No hay categorías seleccionadas</p>
              <p class="text-sm text-muted-foreground mt-1">Selecciona al menos una categoría específica</p>
            </div>

            <!-- Categorías seleccionadas -->
            <div v-else class="space-y-3">
              <Label class="text-sm font-medium text-card-foreground">Categorías seleccionadas:</Label>
              <div class="flex flex-wrap gap-2">
                <Badge 
                  v-for="categoryId in form.categories" 
                  :key="categoryId" 
                  variant="secondary"
                  class="flex items-center gap-1.5 group/badge px-3 py-1.5 text-sm transition-all duration-200 hover:scale-105 border-border bg-accent text-accent-foreground"
                >
                  <span class="font-medium">
                    {{ categories.find(c => c.id.toString() === categoryId)?.name }}
                  </span>
                  <Button
                    type="button"
                    variant="ghost"
                    size="sm"
                    class="h-4 w-4 p-0 hover:bg-transparent hover:text-destructive transition-colors"
                    @click="form.categories = form.categories.filter((id: string) => id !== categoryId)"
                  >
                    <X class="h-3 w-3" />
                  </Button>
                </Badge>
              </div>
            </div>
            
            <p v-if="errors?.categories" class="text-destructive text-sm flex items-center gap-1 mt-2">
              <AlertCircle class="h-3 w-3" />
              {{ errors.categories }}
            </p>
          </CardContent>
        </Card>

        <!-- Contribuidores -->
        <Card class="group hover:shadow-md transition-all duration-300 border-border bg-card">
          <CardHeader class="pb-4">
            <CardTitle class="flex items-center gap-3 text-xl text-card-foreground">
              <div class="p-2 bg-primary/10 rounded-lg">
                <User class="h-6 w-6 text-primary" />
              </div>
              <div>
                Autores y Contribuidores
                <Badge variant="secondary" class="ml-2">Requerido</Badge>
              </div>
              <Badge variant="outline" class="text-sm border-border text-muted-foreground">
                {{ selectedContributorsCount }} contribuidor(es)
              </Badge>
            </CardTitle>
            <CardDescription class="text-muted-foreground text-base">
              Actualiza autores, editores, traductores y otros contribuidores del libro
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4 pt-4">
            <div 
              v-for="(contributor, index) in form.contributors" 
              :key="index"
              class="flex items-end gap-4 p-6 border-2 rounded-xl transition-all duration-200 group/contributor border-border bg-background hover:bg-accent/30"
            >
              <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nombre -->
                <div>
                  <Label :for="`contributor-name-${index}`" class="font-semibold text-card-foreground">Nombre Completo</Label>
                  <Input 
                    :id="`contributor-name-${index}`" 
                    v-model="contributor.full_name"
                    placeholder="Nombre completo del contribuidor" 
                    class="h-11 mt-2 bg-background text-foreground border-input"
                  />
                </div>

                <!-- Tipo -->
                <div>
                  <Label :for="`contributor-type-${index}`" class="font-semibold text-card-foreground">Tipo de Contribución</Label>
                  <Select v-model="contributor.contributor_type">
                    <SelectTrigger class="h-11 mt-2 bg-background text-foreground border-input">
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent class="bg-popover text-popover-foreground border-border">
                      <SelectItem value="author" class="focus:bg-accent focus:text-accent-foreground">Autor</SelectItem>
                      <SelectItem value="editor" class="focus:bg-accent focus:text-accent-foreground">Editor</SelectItem>
                      <SelectItem value="translator" class="focus:bg-accent focus:text-accent-foreground">Traductor</SelectItem>
                      <SelectItem value="illustrator" class="focus:bg-accent focus:text-accent-foreground">Ilustrador</SelectItem>
                      <SelectItem value="prologuist" class="focus:bg-accent focus:text-accent-foreground">Prologuista</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <!-- Orden -->
                <div>
                  <Label :for="`contributor-order-${index}`" class="font-semibold text-card-foreground">Orden de Aparición</Label>
                  <Input 
                    :id="`contributor-order-${index}`" 
                    v-model="contributor.sequence_number" 
                    type="number"
                    min="1" 
                    class="h-11 mt-2 bg-background text-foreground border-input"
                  />
                </div>

                <!-- Badge -->
                <div class="flex items-end">
                  <Badge 
                    variant="outline" 
                    class="text-sm px-3 py-1.5 h-fit transition-colors border-border bg-accent text-accent-foreground"
                  >
                    {{ contributorTypes[contributor.contributor_type as keyof typeof contributorTypes] }}
                  </Badge>
                </div>
              </div>

              <!-- Remove Button -->
              <Button 
                v-if="form.contributors.length > 1" 
                type="button" 
                variant="outline" 
                size="sm"
                class="h-9 w-9 p-0 border-destructive/20 text-destructive hover:bg-destructive/10 hover:border-destructive/30 transition-all duration-200 hover:scale-110"
                @click="removeContributor(index)"
              >
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>

            <!-- Add Contributor Button -->
            <Button 
              type="button" 
              variant="outline" 
              @click="addContributor"
              class="w-full h-12 border-dashed border-border hover:border-solid hover:bg-accent hover:text-accent-foreground transition-all duration-200 hover:scale-[1.02]"
            >
              <Plus class="h-5 w-5 mr-2" />
              Agregar Contribuidor
            </Button>
          </CardContent>
        </Card>

        <!-- Descripción y Metadatos -->
        <Card class="group hover:shadow-md transition-all duration-300 border-border bg-card">
          <CardHeader class="pb-4">
            <CardTitle class="flex items-center gap-3 text-xl text-card-foreground">
              <div class="p-2 bg-primary/10 rounded-lg">
                <FileText class="h-6 w-6 text-primary" />
              </div>
              Descripción y Metadatos
              <Button 
                type="button" 
                variant="ghost" 
                size="sm" 
                @click="showDescriptionPreview = !showDescriptionPreview"
                class="ml-auto hover:bg-accent hover:text-accent-foreground"
              >
                <component :is="showDescriptionPreview ? EyeOff : Eye" class="h-4 w-4 mr-1" />
                {{ showDescriptionPreview ? 'Ocultar' : 'Vista previa' }}
              </Button>
            </CardTitle>
            <CardDescription class="text-muted-foreground text-base">
              Información adicional y palabras clave para mejorar la búsqueda y descubrimiento
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-6 pt-4">
            <!-- Vista previa de descripción -->
            <div 
              v-if="showDescriptionPreview && form.description"
              class="p-4 border-2 border-green-200 rounded-xl bg-green-50/50 dark:bg-green-950/20 dark:border-green-800 space-y-3 animate-in fade-in-50"
            >
              <Label class="text-sm font-semibold text-green-700 dark:text-green-300">Vista Previa de la Descripción:</Label>
              <p class="text-sm text-green-800 dark:text-green-200 leading-relaxed whitespace-pre-wrap">{{ form.description }}</p>
            </div>

            <!-- Descripción -->
            <div class="space-y-3">
              <Label for="description" class="font-semibold text-card-foreground">Descripción del Libro</Label>
              <Textarea 
                id="description" 
                v-model="form.description" 
                @blur="saveDescriptionDraft"
                placeholder="Proporciona una descripción detallada del contenido, temática y objetivos del libro. Incluye información sobre el público objetivo y aspectos destacados..."
                rows="6" 
                class="resize-none text-base leading-relaxed min-h-[150px] bg-background text-foreground border-input"
              />
              <div class="flex justify-between items-center text-sm text-muted-foreground">
                <span>La descripción se guarda automáticamente como borrador</span>
                <span>{{ form.description.length }}/2000 caracteres</span>
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <!-- Edición -->
              <div class="space-y-3">
                <Label for="edition" class="font-semibold text-card-foreground">Edición</Label>
                <Input 
                  id="edition" 
                  v-model="form.edition"
                  placeholder="Ej: 1ra Edición, 2da Edición Revisada, Edición Especial, etc." 
                  class="h-11 bg-background text-foreground border-input"
                />
              </div>

              <!-- Palabras Clave -->
              <div class="space-y-3">
                <Label for="keywords" class="font-semibold text-card-foreground">Palabras Clave</Label>
                <Input 
                  id="keywords" 
                  v-model="form.keywords"
                  placeholder="Separadas por comas: programación, python, desarrollo web, algoritmos" 
                  class="h-11 bg-background text-foreground border-input"
                />
                <p class="text-xs text-muted-foreground">
                  Usa palabras clave relevantes para mejorar la búsqueda
                </p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Configuración -->
        <Card class="group hover:shadow-md transition-all duration-300 border-border bg-card">
          <CardHeader class="pb-4">
            <CardTitle class="flex items-center gap-3 text-xl text-card-foreground">
              <div class="p-2 bg-primary/10 rounded-lg">
                <Save class="h-6 w-6 text-primary" />
              </div>
              Configuración
            </CardTitle>
            <CardDescription class="text-muted-foreground text-base">
              Configura las opciones de visibilidad, descarga y promoción del libro
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4 pt-4">
            <!-- Checkboxes con mejor diseño -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
              <div
                class="flex items-start space-x-3 p-4 border-2 rounded-xl transition-all duration-200 group/checkbox cursor-pointer border-border hover:border-primary/50 hover:bg-accent/50"
                :class="form.downloadable ? 'border-primary bg-accent' : ''"
                @click="form.downloadable = !form.downloadable">
                <Checkbox id="downloadable" v-model="form.downloadable" class="mt-0.5" @click.stop />
                <Label for="downloadable" class="text-sm font-normal cursor-pointer flex-1 space-y-1">
                  <div class="font-semibold text-card-foreground">Descarga Habilitada</div>
                  <div class="text-xs text-muted-foreground">Permitir descarga del libro PDF</div>
                </Label>
              </div>

              <div
                class="flex items-start space-x-3 p-4 border-2 rounded-xl transition-all duration-200 group/checkbox cursor-pointer border-border hover:border-primary/50 hover:bg-accent/50"
                :class="form.featured ? 'border-primary bg-accent' : ''"
                @click="form.featured = !form.featured">
                <Checkbox id="featured" v-model="form.featured" class="mt-0.5" @click.stop />
                <Label for="featured" class="text-sm font-normal cursor-pointer flex-1 space-y-1">
                  <div class="font-semibold text-card-foreground">Libro Destacado</div>
                  <div class="text-xs text-muted-foreground">Mostrar en sección destacada</div>
                </Label>
              </div>

              <div
                class="flex items-start space-x-3 p-4 border-2 rounded-xl transition-all duration-200 group/checkbox cursor-pointer border-border hover:border-primary/50 hover:bg-accent/50"
                :class="form.is_active ? 'border-primary bg-accent' : ''"
                @click="form.is_active = !form.is_active">
                <Checkbox id="is_active" v-model="form.is_active" class="mt-0.5" @click.stop />
                <Label for="is_active" class="text-sm font-normal cursor-pointer flex-1 space-y-1">
                  <div class="font-semibold text-card-foreground">Activo en Catálogo</div>
                  <div class="text-xs text-muted-foreground">Visible para los usuarios</div>
                </Label>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Actions -->
        <Card class="bg-accent border-border">
          <CardFooter class="flex flex-col sm:flex-row justify-between items-center gap-4 p-6">
            <div class="flex items-center gap-3 text-sm text-muted-foreground">
              <div class="flex items-center gap-2">
                <div class="w-2 h-2 rounded-full bg-primary animate-pulse"></div>
                <span>Formulario listo</span>
              </div>
              <span>•</span>
              <span>{{ progress }}% completado</span>
            </div>

            <div class="flex gap-3">
              <Button 
                variant="outline" 
                type="button" 
                as-child
                class="border-2 h-11 px-6 transition-all duration-200 hover:scale-105 border-border hover:bg-accent hover:text-accent-foreground"
              >
                <a href="/admin/books">Cancelar</a>
              </Button>
              <Button 
                type="submit" 
                :disabled="hasEmptyRequiredFields() || isLoading"
                class="h-11 px-8 bg-primary text-primary-foreground hover:bg-primary/90 transition-all duration-200 hover:scale-105 shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:transform-none"
              >
                <Loader2 v-if="isLoading" class="h-4 w-4 mr-2 animate-spin" />
                <Save v-else class="h-4 w-4 mr-2" />
                {{ isLoading ? 'Actualizando...' : 'Actualizar Libro' }}
              </Button>
            </div>
          </CardFooter>
        </Card>
      </form>
    </div>
  </AppLayout>
</template>

<style scoped>
.required::after {
  content: " *";
  color: hsl(var(--destructive));
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.custom-scrollbar::-webkit-scrollbar {
  width: 6px;
}

.custom-scrollbar::-webkit-scrollbar-track {
  background: hsl(var(--muted));
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb {
  background: hsl(var(--muted-foreground) / 0.3);
  border-radius: 3px;
}

.custom-scrollbar::-webkit-scrollbar-thumb:hover {
  background: hsl(var(--muted-foreground) / 0.5);
}
</style>