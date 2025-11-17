<script setup lang="ts">
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import {
    Archive,
    ArrowLeft,
    BookCopy,
    BookOpen,
    Calendar,
    Download,
    Edit,
    Eye,
    FileDigit,
    FileText,
    Globe,
    Hash,
    Library,
    Star,
    TrendingUp,
    Users,
    Zap,
} from 'lucide-vue-next';

// Props
const props = defineProps<{
    book: any;
    stats: any;
}>();

// Breadcrumbs
const breadcrumbs = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Libros', href: '/admin/books' },
    { title: props.book.title, href: `/admin/books/${props.book.id}` },
];

// Methods
function editBook() {
    router.get(`/admin/books/${props.book.id}/edit`);
}

// Contributor type labels
const contributorTypes = {
    author: 'Autor',
    editor: 'Editor',
    translator: 'Traductor',
    illustrator: 'Ilustrador',
    prologuist: 'Prologuista',
};

// Book type labels and styling
const bookTypeLabels: Record<string, string> = {
    digital: 'Digital',
    physical: 'Físico',
    both: 'Híbrido',
};

const bookTypeColors: Record<string, string> = {
    digital:
        'bg-blue-500/10 text-blue-600 border-blue-200 dark:bg-blue-500/20 dark:text-blue-400 dark:border-blue-800',
    physical:
        'bg-emerald-500/10 text-emerald-600 border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-400 dark:border-emerald-800',
    both: 'bg-purple-500/10 text-purple-600 border-purple-200 dark:bg-purple-500/20 dark:text-purple-400 dark:border-purple-800',
};

const bookTypeIcons: Record<string, any> = {
    digital: FileDigit,
    physical: BookCopy,
    both: Globe,
};

// Format date
const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

// Get status badge
const getStatusBadge = (book: any) => {
    if (!book.is_active) {
        return {
            label: 'Inactivo',
            variant: 'destructive' as const,
            icon: Archive,
        };
    }
    if (book.featured) {
        return { label: 'Destacado', variant: 'default' as const, icon: Star };
    }
    return { label: 'Activo', variant: 'default' as const, icon: Eye };
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-6">
            <!-- Header Mejorado -->
            <div
                class="flex flex-col items-start justify-between gap-6 lg:flex-row lg:items-center"
            >
                <div class="flex flex-1 items-start gap-4">
                    <Button
                        variant="outline"
                        size="sm"
                        as-child
                        class="flex-shrink-0"
                    >
                        <a href="/admin/books">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Volver
                        </a>
                    </Button>
                    <div class="min-w-0 flex-1">
                        <div class="mb-2 flex items-center gap-3">
                            <Badge
                                :class="bookTypeColors[book.book_type]"
                                class="font-medium"
                            >
                                <component
                                    :is="bookTypeIcons[book.book_type]"
                                    class="mr-1 h-3 w-3"
                                />
                                {{ bookTypeLabels[book.book_type] }}
                            </Badge>
                            <Badge
                                :variant="getStatusBadge(book).variant"
                                class="font-medium"
                            >
                                <component
                                    :is="getStatusBadge(book).icon"
                                    class="mr-1 h-3 w-3"
                                />
                                {{ getStatusBadge(book).label }}
                            </Badge>
                        </div>
                        <h1
                            class="line-clamp-2 text-3xl font-bold tracking-tight text-foreground"
                        >
                            {{ book.title }}
                        </h1>
                        <p
                            class="mt-2 flex items-center gap-2 text-muted-foreground"
                        >
                            <Library class="h-4 w-4" />
                            Detalles completos del libro en el catálogo
                        </p>
                    </div>
                </div>
                <Button
                    @click="editBook"
                    class="bg-primary text-primary-foreground hover:bg-primary/90"
                >
                    <Edit class="mr-2 h-4 w-4" />
                    Editar Libro
                </Button>
            </div>

            <!-- Stats Cards Mejoradas -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4">
                <!-- Descargas -->
                <Card
                    class="group overflow-hidden rounded-xl border border-border bg-card shadow-lg transition-all duration-500 hover:-translate-y-1 hover:border-primary/30 hover:shadow-xl"
                >
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="mb-2 text-sm font-medium text-muted-foreground"
                                >
                                    Total Descargas
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.total_downloads || 0 }}
                                </p>
                                <div
                                    class="text-success mt-2 flex items-center gap-1 text-xs"
                                >
                                    <TrendingUp class="h-3 w-3" />
                                    <span>+15% este mes</span>
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <Download class="h-6 w-6 text-primary" />
                            </div>
                        </div>
                    </CardContent>
                    <div
                        class="h-1 w-0 bg-gradient-to-r from-primary to-primary/60 transition-all duration-500 group-hover:w-full"
                    ></div>
                </Card>

                <!-- Préstamos -->
                <Card
                    class="group overflow-hidden rounded-xl border border-border bg-card shadow-lg transition-all duration-500 hover:-translate-y-1 hover:border-secondary/30 hover:shadow-xl"
                >
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="mb-2 text-sm font-medium text-muted-foreground"
                                >
                                    Total Préstamos
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.total_loans || 0 }}
                                </p>
                                <div
                                    class="text-success mt-2 flex items-center gap-1 text-xs"
                                >
                                    <TrendingUp class="h-3 w-3" />
                                    <span>+8% este mes</span>
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <Users class="h-6 w-6 text-secondary" />
                            </div>
                        </div>
                    </CardContent>
                    <div
                        class="h-1 w-0 bg-gradient-to-r from-secondary to-secondary/60 transition-all duration-500 group-hover:w-full"
                    ></div>
                </Card>

                <!-- Vistas -->
                <Card
                    class="group overflow-hidden rounded-xl border border-border bg-card shadow-lg transition-all duration-500 hover:-translate-y-1 hover:border-primary/30 hover:shadow-xl"
                >
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="mb-2 text-sm font-medium text-muted-foreground"
                                >
                                    Total Vistas
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ stats.total_views || 0 }}
                                </p>
                                <div
                                    class="text-success mt-2 flex items-center gap-1 text-xs"
                                >
                                    <TrendingUp class="h-3 w-3" />
                                    <span>+12% este mes</span>
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-primary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <Eye class="h-6 w-6 text-primary" />
                            </div>
                        </div>
                    </CardContent>
                    <div
                        class="h-1 w-0 bg-gradient-to-r from-primary to-primary/60 transition-all duration-500 group-hover:w-full"
                    ></div>
                </Card>

                <!-- Copias Disponibles -->
                <Card
                    class="group overflow-hidden rounded-xl border border-border bg-card shadow-lg transition-all duration-500 hover:-translate-y-1 hover:border-secondary/30 hover:shadow-xl"
                >
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="mb-2 text-sm font-medium text-muted-foreground"
                                >
                                    Copias Disponibles
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ book.available_copies_count || 0 }}
                                </p>
                                <div
                                    class="mt-2 flex items-center gap-1 text-xs text-muted-foreground"
                                >
                                    <BookCopy class="h-3 w-3" />
                                    <span
                                        >{{
                                            book.physical_copies_count || 0
                                        }}
                                        totales</span
                                    >
                                </div>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-secondary/10 transition-transform duration-300 group-hover:scale-110"
                            >
                                <BookCopy class="h-6 w-6 text-secondary" />
                            </div>
                        </div>
                    </CardContent>
                    <div
                        class="h-1 w-0 bg-gradient-to-r from-secondary to-secondary/60 transition-all duration-500 group-hover:w-full"
                    ></div>
                </Card>
            </div>

            <div class="grid grid-cols-1 gap-8 lg:grid-cols-3">
                <!-- Información Principal -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Información Básica Mejorada -->
                    <Card
                        class="rounded-xl border border-border bg-card shadow-lg"
                    >
                        <CardHeader class="pb-4">
                            <CardTitle class="flex items-center gap-2 text-xl">
                                <BookOpen class="h-5 w-5 text-primary" />
                                Información Básica del Libro
                            </CardTitle>
                            <CardDescription>
                                Información principal y detalles de
                                identificación
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <!-- Título y ISBN -->
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        class="flex items-center gap-2 text-sm font-medium text-muted-foreground"
                                    >
                                        <FileText class="h-4 w-4" />
                                        Título del Libro
                                    </Label>
                                    <p
                                        class="text-lg font-medium text-foreground"
                                    >
                                        {{ book.title }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="flex items-center gap-2 text-sm font-medium text-muted-foreground"
                                    >
                                        <Hash class="h-4 w-4" />
                                        ISBN
                                    </Label>
                                    <p
                                        class="rounded-lg bg-muted/30 p-2 font-mono font-medium text-foreground"
                                    >
                                        {{ book.isbn }}
                                    </p>
                                </div>
                            </div>

                            <!-- Editorial e Idioma -->
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        class="flex items-center gap-2 text-sm font-medium text-muted-foreground"
                                    >
                                        <Library class="h-4 w-4" />
                                        Editorial
                                    </Label>
                                    <div class="flex items-center gap-2">
                                        <Badge
                                            variant="outline"
                                            class="bg-secondary/10"
                                        >
                                            {{
                                                book.publisher?.name ||
                                                'No especificada'
                                            }}
                                        </Badge>
                                        <span
                                            v-if="book.publisher?.city"
                                            class="text-sm text-muted-foreground"
                                        >
                                            • {{ book.publisher.city }},
                                            {{ book.publisher.country }}
                                        </span>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="flex items-center gap-2 text-sm font-medium text-muted-foreground"
                                    >
                                        <Globe class="h-4 w-4" />
                                        Idioma
                                    </Label>
                                    <Badge
                                        variant="outline"
                                        class="bg-primary/10"
                                    >
                                        {{
                                            book.language?.native_name ||
                                            book.language_code
                                        }}
                                    </Badge>
                                </div>
                            </div>

                            <!-- Páginas y Año -->
                            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label
                                        class="flex items-center gap-2 text-sm font-medium text-muted-foreground"
                                    >
                                        <FileText class="h-4 w-4" />
                                        Número de Páginas
                                    </Label>
                                    <div class="flex items-center gap-2">
                                        <Badge
                                            variant="outline"
                                            class="font-mono"
                                        >
                                            {{ book.pages }} páginas
                                        </Badge>
                                    </div>
                                </div>
                                <div class="space-y-2">
                                    <Label
                                        class="flex items-center gap-2 text-sm font-medium text-muted-foreground"
                                    >
                                        <Calendar class="h-4 w-4" />
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
                    <Card
                        v-if="book.details?.description"
                        class="rounded-xl border border-border bg-card shadow-lg"
                    >
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
                                <p
                                    class="leading-relaxed whitespace-pre-line text-foreground"
                                >
                                    {{ book.details.description }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Contribuidores Mejorados -->
                    <Card
                        class="rounded-xl border border-border bg-card shadow-lg"
                    >
                        <CardHeader class="pb-4">
                            <CardTitle class="flex items-center gap-2 text-xl">
                                <Users class="h-5 w-5 text-primary" />
                                Autores y Contribuidores
                            </CardTitle>
                            <CardDescription>
                                Personas que participaron en la creación de este
                                libro
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                                <div
                                    v-for="contributor in book.contributors"
                                    :key="contributor.id"
                                    class="group rounded-xl border border-border p-4 transition-all duration-300 hover:border-primary/30 hover:shadow-md"
                                >
                                    <div
                                        class="flex items-start justify-between"
                                    >
                                        <div class="flex-1 space-y-2">
                                            <h4
                                                class="font-semibold text-foreground transition-colors group-hover:text-primary"
                                            >
                                                {{ contributor.full_name }}
                                            </h4>
                                            <Badge
                                                variant="outline"
                                                class="bg-secondary/10"
                                            >
                                                {{
                                                    contributorTypes[
                                                        contributor.contributor_type as keyof typeof contributorTypes
                                                    ]
                                                }}
                                            </Badge>
                                        </div>
                                        <Badge
                                            variant="secondary"
                                            class="ml-2 flex-shrink-0"
                                        >
                                            #{{ contributor.sequence_number }}
                                        </Badge>
                                    </div>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Categorías Mejoradas -->
                    <Card
                        class="rounded-xl border border-border bg-card shadow-lg"
                    >
                        <CardHeader class="pb-4">
                            <CardTitle class="flex items-center gap-2 text-xl">
                                <Library class="h-5 w-5 text-primary" />
                                Categorías y Temáticas
                            </CardTitle>
                            <CardDescription>
                                Áreas de conocimiento y clasificaciones del
                                libro
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-2">
                                <Badge
                                    v-for="category in book.categories"
                                    :key="category.id"
                                    variant="secondary"
                                    class="border-primary/20 bg-primary/10 text-primary transition-colors hover:bg-primary/20"
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
                    <Card
                        class="rounded-xl border border-border bg-card shadow-lg"
                    >
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
                                <Label
                                    class="text-sm font-medium text-muted-foreground"
                                    >Edición</Label
                                >
                                <p class="font-medium text-foreground">
                                    {{ book.details.edition }}
                                </p>
                            </div>

                            <div
                                v-if="book.details?.keywords"
                                class="space-y-2"
                            >
                                <Label
                                    class="text-sm font-medium text-muted-foreground"
                                    >Palabras Clave</Label
                                >
                                <div class="flex flex-wrap gap-1">
                                    <Badge
                                        v-for="keyword in book.details.keywords.split(
                                            ',',
                                        )"
                                        :key="keyword.trim()"
                                        variant="outline"
                                        class="text-xs"
                                    >
                                        {{ keyword.trim() }}
                                    </Badge>
                                </div>
                            </div>

                            <div class="space-y-2 border-t border-border pt-4">
                                <Label
                                    class="text-sm font-medium text-muted-foreground"
                                    >Fecha de Creación</Label
                                >
                                <p class="text-sm text-foreground">
                                    {{ formatDate(book.created_at) }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label
                                    class="text-sm font-medium text-muted-foreground"
                                    >Última Actualización</Label
                                >
                                <p class="text-sm text-foreground">
                                    {{ formatDate(book.updated_at) }}
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Copias Físicas Mejoradas -->
                    <Card
                        v-if="
                            book.book_type !== 'digital' &&
                            book.physicalCopies &&
                            book.physicalCopies.length > 0
                        "
                        class="rounded-xl border border-border bg-card shadow-lg"
                    >
                        <CardHeader class="pb-4">
                            <CardTitle class="flex items-center gap-2">
                                <BookCopy class="h-5 w-5 text-primary" />
                                Copias Físicas
                            </CardTitle>
                            <CardDescription>
                                {{ book.physical_copies_count }} copias totales,
                                {{ book.available_copies_count }} disponibles
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="space-y-3">
                                <div
                                    v-for="copy in book.physicalCopies"
                                    :key="copy.id"
                                    class="flex items-center justify-between rounded-lg border border-border p-3 transition-colors hover:border-primary/30"
                                >
                                    <div class="space-y-1">
                                        <p class="text-sm font-medium">
                                            {{ copy.barcode }}
                                        </p>
                                        <p
                                            class="text-xs text-muted-foreground"
                                        >
                                            Copia #{{ copy.copy_number }}
                                        </p>
                                    </div>
                                    <Badge
                                        :variant="
                                            copy.status === 'available'
                                                ? 'default'
                                                : 'secondary'
                                        "
                                        :class="
                                            copy.status === 'available'
                                                ? 'bg-success/10 text-success border-success/20'
                                                : 'bg-warning/10 text-warning border-warning/20'
                                        "
                                    >
                                        {{
                                            copy.status === 'available'
                                                ? 'Disponible'
                                                : 'Prestado'
                                        }}
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Acciones Rápidas -->
                    <Card
                        class="rounded-xl border border-border bg-card shadow-lg"
                    >
                        <CardHeader class="pb-4">
                            <CardTitle class="flex items-center gap-2">
                                <Zap class="h-5 w-5 text-primary" />
                                Acciones Rápidas
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Button
                                @click="editBook"
                                variant="outline"
                                class="w-full justify-start"
                            >
                                <Edit class="mr-2 h-4 w-4" />
                                Editar Información
                            </Button>
                            <Button
                                variant="outline"
                                class="w-full justify-start"
                                as-child
                            >
                                <a :href="`/admin/books/${book.id}/loans`">
                                    <Users class="mr-2 h-4 w-4" />
                                    Ver Préstamos
                                </a>
                            </Button>
                            <Button
                                variant="outline"
                                class="w-full justify-start"
                                as-child
                            >
                                <a :href="`/admin/books/${book.id}/downloads`">
                                    <Download class="mr-2 h-4 w-4" />
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
