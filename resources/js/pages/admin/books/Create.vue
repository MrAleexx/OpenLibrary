<script setup lang="ts">
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
import AppLayout from '@/layouts/AppLayout.vue'
import type { BookFormData } from '@/types/book'
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
  AlertCircle
} from 'lucide-vue-next'
import { Alert, AlertDescription } from '@/components/ui/alert'

// Props
const props = defineProps<{
  categories: any[]
  publishers: any[]
  languages: any[]
  book_types: any[]
  errors?: Record<string, string>
}>()

// Breadcrumbs
const breadcrumbs = [
  { title: 'Dashboard', href: '/admin/dashboard' },
  { title: 'Libros', href: '/admin/books' },
  { title: 'Crear Libro', href: '/admin/books/create' }
]

// Form state
const form = reactive<BookFormData>({
  title: '',
  publisher_id: null,
  isbn: '',
  language_code: 'es',
  pages: '',
  publication_year: new Date().getFullYear().toString(),
  book_type: 'digital',
  featured: false,
  is_active: true,
  downloadable: true,
  description: '',
  edition: '1ra',
  keywords: '',
  categories: [],
  contributors: [
    {
      full_name: '',
      contributor_type: 'author' as const,
      sequence_number: 1
    }
  ]
})

// Methods
function addContributor() {
  form.contributors.push({
    full_name: '',
    contributor_type: 'author',
    sequence_number: form.contributors.length + 1
  })
}

function removeContributor(index: number) {
  if (form.contributors.length > 1) {
    form.contributors.splice(index, 1)
    // Reordenar sequence numbers
    for (const [idx, contributor] of form.contributors.entries()) {
      contributor.sequence_number = idx + 1
    }
  }
}

function submit() {
  // Preparar datos para enviar
  const submitData = {
    ...form,
    pages: form.pages ? Number.parseInt(form.pages.toString()) : null,
    publisher_id: form.publisher_id ? Number.parseInt(form.publisher_id.toString()) : null,
    publication_year: form.publication_year ? Number.parseInt(form.publication_year.toString()) : null,
    categories: form.categories.map(id => Number.parseInt(id))
  }

  router.post('/admin/books', submitData)
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
const hasEmptyRequiredFields = () => {
  return !form.title || !form.isbn || !form.pages || form.categories.length === 0
}
</script>

<template>
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Button variant="outline" size="sm" as-child>
          <a href="/admin/books">
            <ArrowLeft class="h-4 w-4 mr-2" />
            Volver
          </a>
        </Button>
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Crear Nuevo Libro</h1>
          <p class="text-muted-foreground mt-1">
            Agrega un nuevo libro al catálogo de la biblioteca
          </p>
        </div>
      </div>

      <!-- Alert de errores -->
      <Alert v-if="props.errors" variant="destructive" class="bg-destructive/10 border-destructive/20">
        <AlertCircle class="h-4 w-4" />
        <AlertDescription>
          Hay errores en el formulario. Por favor, revisa los campos marcados.
        </AlertDescription>
      </Alert>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Información Básica -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <BookOpen class="h-5 w-5" />
              Información Básica
            </CardTitle>
            <CardDescription>
              Información principal del libro
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Título -->
              <div class="md:col-span-2">
                <Label for="title" class="required">Título del Libro</Label>
                <Input id="title" v-model="form.title" placeholder="Ingresa el título completo del libro"
                  :class="errors?.title ? 'border-destructive' : ''" required />
                <p v-if="errors?.title" class="text-destructive text-sm mt-1">{{ errors.title }}</p>
              </div>

              <!-- ISBN -->
              <div>
                <Label for="isbn" class="required">ISBN</Label>
                <Input id="isbn" v-model="form.isbn" placeholder="978-612-00123-4-7"
                  :class="errors?.isbn ? 'border-destructive' : ''" required />
                <p v-if="errors?.isbn" class="text-destructive text-sm mt-1">{{ errors.isbn }}</p>
              </div>

              <!-- Editorial -->
              <div>
                <Label for="publisher_id">Editorial</Label>
                <Select v-model="form.publisher_id" :class="errors?.publisher_id ? 'border-destructive' : ''">
                  <SelectTrigger>
                    <SelectValue placeholder="Selecciona una editorial" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem :value="null">Sin editorial</SelectItem>
                    <SelectItem v-for="publisher in publishers" :key="publisher.id" :value="publisher.id">
                      {{ publisher.name }} - {{ publisher.city }}, {{ publisher.country }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="errors?.publisher_id" class="text-destructive text-sm mt-1">{{ errors.publisher_id }}</p>
              </div>

              <!-- Páginas y Año -->
              <div>
                <Label for="pages" class="required">Número de Páginas</Label>
                <Input id="pages" v-model="form.pages" type="number" min="1"
                  :class="errors?.pages ? 'border-destructive' : ''" required />
                <p v-if="errors?.pages" class="text-destructive text-sm mt-1">{{ errors.pages }}</p>
              </div>

              <div>
                <Label for="publication_year">Año de Publicación</Label>
                <Input id="publication_year" v-model="form.publication_year" type="number" :min="1800"
                  :max="new Date().getFullYear() + 1" :class="errors?.publication_year ? 'border-destructive' : ''" />
                <p v-if="errors?.publication_year" class="text-destructive text-sm mt-1">{{ errors.publication_year }}
                </p>
              </div>

              <!-- Idioma -->
              <div>
                <Label for="language_code" class="required">Idioma</Label>
                <Select v-model="form.language_code" :class="errors?.language_code ? 'border-destructive' : ''">
                  <SelectTrigger>
                    <SelectValue placeholder="Selecciona un idioma" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="language in languages" :key="language.code" :value="language.code">
                      {{ language.native_name }} ({{ language.name }})
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="errors?.language_code" class="text-destructive text-sm mt-1">{{ errors.language_code }}</p>
              </div>

              <!-- Tipo de Libro -->
              <div>
                <Label for="book_type" class="required">Tipo de Libro</Label>
                <Select v-model="form.book_type" :class="errors?.book_type ? 'border-destructive' : ''">
                  <SelectTrigger>
                    <SelectValue placeholder="Selecciona el tipo" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem v-for="type in book_types" :key="type.value" :value="type.value">
                      {{ type.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
                <p v-if="errors?.book_type" class="text-destructive text-sm mt-1">{{ errors.book_type }}</p>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Categorías -->
        <Card>
          <CardHeader>
            <CardTitle>Categorías</CardTitle>
            <CardDescription>
              Selecciona las categorías a las que pertenece este libro
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2">
              <div v-for="category in categories" :key="category.id" class="flex items-center space-x-2">
                <Checkbox :id="`category-${category.id}`" :checked="form.categories.includes(category.id.toString())"
                  @update:checked="(checked: boolean) => {
                    const categoryId = category.id.toString()
                    if (checked && !form.categories.includes(categoryId)) {
                      form.categories.push(categoryId)
                    } else if (!checked) {
                      form.categories = form.categories.filter(id => id !== categoryId)
                    }
                  }" />
                <Label :for="`category-${category.id}`" class="text-sm font-normal">
                  {{ category.name }}
                </Label>
              </div>
            </div>
            <p v-if="form.categories.length === 0" class="text-sm text-muted-foreground mt-2">
              Debes seleccionar al menos una categoría
            </p>
            <div v-else class="flex flex-wrap gap-1 mt-3">
              <Badge v-for="categoryId in form.categories" :key="categoryId" variant="secondary">
                {{categories.find(c => c.id.toString() === categoryId)?.name}}
              </Badge>
            </div>
            <p v-if="errors?.categories" class="text-destructive text-sm mt-1">{{ errors.categories }}</p>
          </CardContent>
        </Card>

        <!-- Contribuidores -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <User class="h-5 w-5" />
              Autores y Contribuidores
            </CardTitle>
            <CardDescription>
              Agrega autores, editores, traductores y otros contribuidores
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div v-for="(contributor, index) in form.contributors" :key="index"
              class="flex items-end gap-4 p-4 border rounded-lg">
              <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Nombre -->
                <div>
                  <Label :for="`contributor-name-${index}`">Nombre Completo</Label>
                  <Input :id="`contributor-name-${index}`" v-model="contributor.full_name"
                    placeholder="Nombre completo del contribuidor" />
                </div>

                <!-- Tipo -->
                <div>
                  <Label :for="`contributor-type-${index}`">Tipo de Contribución</Label>
                  <Select v-model="contributor.contributor_type">
                    <SelectTrigger>
                      <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                      <SelectItem value="author">Autor</SelectItem>
                      <SelectItem value="editor">Editor</SelectItem>
                      <SelectItem value="translator">Traductor</SelectItem>
                      <SelectItem value="illustrator">Ilustrador</SelectItem>
                      <SelectItem value="prologuist">Prologuista</SelectItem>
                    </SelectContent>
                  </Select>
                </div>

                <!-- Orden -->
                <div>
                  <Label :for="`contributor-order-${index}`">Orden de Aparición</Label>
                  <Input :id="`contributor-order-${index}`" v-model="contributor.sequence_number" type="number"
                    min="1" />
                </div>

                <!-- Badge -->
                <div class="flex items-center">
                  <Badge variant="outline">
                    {{ contributorTypes[contributor.contributor_type as keyof typeof contributorTypes] }}
                  </Badge>
                </div>
              </div>

              <!-- Remove Button -->
              <Button v-if="form.contributors.length > 1" type="button" variant="outline" size="sm"
                @click="removeContributor(index)">
                <Trash2 class="h-4 w-4" />
              </Button>
            </div>

            <!-- Add Contributor Button -->
            <Button type="button" variant="outline" @click="addContributor">
              <Plus class="h-4 w-4 mr-2" />
              Agregar Contribuidor
            </Button>
          </CardContent>
        </Card>

        <!-- Configuración -->
        <Card>
          <CardHeader>
            <CardTitle>Configuración</CardTitle>
            <CardDescription>
              Configura las opciones de visibilidad y descarga del libro
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <!-- Checkboxes -->
            <div class="flex flex-col gap-3">
              <div class="flex items-center space-x-2">
                <Checkbox id="downloadable" v-model="form.downloadable" />
                <Label for="downloadable" class="text-sm font-normal">
                  Permitir descarga del libro
                </Label>
              </div>
              <div class="flex items-center space-x-2">
                <Checkbox id="featured" v-model="form.featured" />
                <Label for="featured" class="text-sm font-normal">
                  Marcar como libro destacado
                </Label>
              </div>
              <div class="flex items-center space-x-2">
                <Checkbox id="is_active" v-model="form.is_active" />
                <Label for="is_active" class="text-sm font-normal">
                  Libro activo y visible en el catálogo
                </Label>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Descripción y Metadatos -->
        <Card>
          <CardHeader>
            <CardTitle>Descripción y Metadatos</CardTitle>
            <CardDescription>
              Información adicional y palabras clave para mejorar la búsqueda
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <!-- Descripción -->
            <div>
              <Label for="description">Descripción del Libro</Label>
              <Textarea id="description" v-model="form.description"
                placeholder="Proporciona una descripción detallada del contenido del libro..." rows="4" />
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Edición -->
              <div>
                <Label for="edition">Edición</Label>
                <Input id="edition" v-model="form.edition" placeholder="Ej: 1ra Edición, 2da Edición Revisada, etc." />
              </div>

              <!-- Palabras Clave -->
              <div>
                <Label for="keywords">Palabras Clave</Label>
                <Input id="keywords" v-model="form.keywords"
                  placeholder="Separadas por comas: programación, python, desarrollo" />
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Actions -->
        <Card>
          <CardFooter class="flex justify-between">
            <Button variant="outline" type="button" as-child>
              <a href="/admin/books">Cancelar</a>
            </Button>
            <Button type="submit" :disabled="hasEmptyRequiredFields()">
              <Save class="h-4 w-4 mr-2" />
              Crear Libro
            </Button>
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
</style>