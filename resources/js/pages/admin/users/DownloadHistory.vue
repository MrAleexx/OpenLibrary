<!-- resources/js/pages/admin/users/DownloadHistory.vue -->
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
import { Head, Link } from '@inertiajs/vue3';
import {
    ArrowLeft,
    BookOpen,
    Calendar,
    ChevronDown,
    ChevronUp,
    Download,
    Eye,
    FileText,
    Globe,
    Hash,
    TrendingUp,
    User,
} from 'lucide-vue-next';
import { computed, ref } from 'vue';

// Props
const props = defineProps<{
    user: any;
    downloads: {
        data: Array<{
            id: number;
            downloaded_at: string;
            ip_address: string;
            user_agent?: string;
            book: {
                id: number;
                title: string;
                isbn: string;
                book_type: string;
                cover_image?: string;
            };
        }>;
        total: number;
        links: Array<{ url: string | null; label: string; active: boolean }>;
    };
}>();

// Breadcrumbs
const breadcrumbs = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Usuarios', href: '/admin/users' },
    {
        title: `${props.user.name} ${props.user.last_name}`,
        href: `/admin/users/${props.user.id}`,
    },
    { title: 'Historial de Descargas', href: '#' },
];

// State for expanded items
const expandedItems = ref<Set<number>>(new Set());

// Format date
function formatDateTime(date: string) {
    return new Date(date).toLocaleString('es-ES', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function formatDate(date: string) {
    return new Date(date).toLocaleDateString('es-ES', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
}

// Toggle expanded state
function toggleExpand(downloadId: number) {
    if (expandedItems.value.has(downloadId)) {
        expandedItems.value.delete(downloadId);
    } else {
        expandedItems.value.add(downloadId);
    }
}

// Book type badges
const bookTypeColors: any = {
    digital:
        'bg-blue-500/10 text-blue-600 border-blue-200 dark:bg-blue-500/20 dark:text-blue-400 dark:border-blue-800',
    physical:
        'bg-green-500/10 text-green-600 border-green-200 dark:bg-green-500/20 dark:text-green-400 dark:border-green-800',
    both: 'bg-purple-500/10 text-purple-600 border-purple-200 dark:bg-purple-500/20 dark:text-purple-400 dark:border-purple-800',
};

const bookTypeLabels: any = {
    digital: 'Digital',
    physical: 'Físico',
    both: 'Ambos',
};

// Computed for stats
const totalDownloads = computed(() => props.downloads.total);
const todayDownloads = computed(() => {
    const today = new Date().toDateString();
    return props.downloads.data.filter(
        (download) => new Date(download.downloaded_at).toDateString() === today,
    ).length;
});

const thisWeekDownloads = computed(() => {
    const oneWeekAgo = new Date();
    oneWeekAgo.setDate(oneWeekAgo.getDate() - 7);
    return props.downloads.data.filter(
        (download) => new Date(download.downloaded_at) >= oneWeekAgo,
    ).length;
});
</script>

<template>
    <Head>
        <title>Historial de Descargas - {{ user.name }}</title>
    </Head>

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-6 p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-foreground">
                        Historial de Descargas
                    </h1>
                    <p
                        class="mt-2 flex items-center gap-2 text-muted-foreground"
                    >
                        <Download class="h-4 w-4 text-primary" />
                        Todas las descargas realizadas por {{ user.name }}
                        {{ user.last_name }}
                    </p>
                </div>
                <div class="flex items-center gap-3">
                    <Button variant="outline" as-child>
                        <Link :href="`/admin/users/${user.id}`">
                            <ArrowLeft class="mr-2 h-4 w-4" />
                            Volver al Usuario
                        </Link>
                    </Button>
                    <Button variant="outline" as-child>
                        <Link :href="`/admin/users/${user.id}/loan-history`">
                            <BookOpen class="mr-2 h-4 w-4" />
                            Ver Préstamos
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
                <Card
                    class="border-blue-200 bg-gradient-to-br from-blue-500/10 to-blue-600/5"
                >
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="mb-1 text-sm font-medium text-blue-600"
                                >
                                    Total Descargas
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ totalDownloads }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Historial completo
                                </p>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-blue-500/20"
                            >
                                <Download class="h-6 w-6 text-blue-500" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="border-green-200 bg-gradient-to-br from-green-500/10 to-green-600/5"
                >
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="mb-1 text-sm font-medium text-green-600"
                                >
                                    Descargas Hoy
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ todayDownloads }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    {{ formatDate(new Date().toISOString()) }}
                                </p>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-green-500/20"
                            >
                                <Calendar class="h-6 w-6 text-green-500" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card
                    class="border-purple-200 bg-gradient-to-br from-purple-500/10 to-purple-600/5"
                >
                    <CardContent class="p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <p
                                    class="mb-1 text-sm font-medium text-purple-600"
                                >
                                    Esta Semana
                                </p>
                                <p class="text-3xl font-bold text-foreground">
                                    {{ thisWeekDownloads }}
                                </p>
                                <p class="mt-1 text-xs text-muted-foreground">
                                    Últimos 7 días
                                </p>
                            </div>
                            <div
                                class="flex h-12 w-12 items-center justify-center rounded-xl bg-purple-500/20"
                            >
                                <TrendingUp class="h-6 w-6 text-purple-500" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Downloads List -->
            <Card class="border-border bg-card shadow-lg">
                <CardHeader class="border-b border-border bg-muted/50">
                    <CardTitle class="flex items-center gap-2 text-foreground">
                        <Download class="h-5 w-5 text-primary" />
                        Lista de Descargas
                    </CardTitle>
                    <CardDescription class="text-muted-foreground">
                        {{ downloads.total }} descargas encontradas en el
                        historial
                    </CardDescription>
                </CardHeader>
                <CardContent class="p-0">
                    <div
                        v-if="downloads.data.length > 0"
                        class="divide-y divide-border"
                    >
                        <div
                            v-for="download in downloads.data"
                            :key="download.id"
                            class="group p-6 transition-colors hover:bg-accent/50"
                        >
                            <!-- Main Download Info -->
                            <div class="flex items-start justify-between">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-start gap-4">
                                        <!-- Book Icon/Placeholder -->
                                        <div
                                            class="mt-1 flex h-16 w-12 flex-shrink-0 items-center justify-center rounded-lg bg-primary/10"
                                        >
                                            <BookOpen
                                                class="h-6 w-6 text-primary"
                                            />
                                        </div>

                                        <div class="min-w-0 flex-1">
                                            <!-- Book Title -->
                                            <h3
                                                class="mb-2 text-lg font-semibold text-foreground transition-colors group-hover:text-primary"
                                            >
                                                {{ download.book.title }}
                                            </h3>

                                            <!-- Metadata Row -->
                                            <div
                                                class="mb-3 flex flex-wrap items-center gap-4 text-sm text-muted-foreground"
                                            >
                                                <div
                                                    class="flex items-center gap-1"
                                                >
                                                    <Hash class="h-3 w-3" />
                                                    <span
                                                        >ISBN:
                                                        {{
                                                            download.book.isbn
                                                        }}</span
                                                    >
                                                </div>
                                                <div
                                                    class="flex items-center gap-1"
                                                >
                                                    <Calendar class="h-3 w-3" />
                                                    <span>{{
                                                        formatDateTime(
                                                            download.downloaded_at,
                                                        )
                                                    }}</span>
                                                </div>
                                                <div
                                                    class="flex items-center gap-1"
                                                >
                                                    <Globe class="h-3 w-3" />
                                                    <span>{{
                                                        download.ip_address
                                                    }}</span>
                                                </div>
                                            </div>

                                            <!-- Badges -->
                                            <div class="flex flex-wrap gap-2">
                                                <Badge
                                                    :class="
                                                        bookTypeColors[
                                                            download.book
                                                                .book_type
                                                        ]
                                                    "
                                                    class="border font-medium"
                                                >
                                                    {{
                                                        bookTypeLabels[
                                                            download.book
                                                                .book_type
                                                        ]
                                                    }}
                                                </Badge>
                                                <Badge
                                                    variant="outline"
                                                    class="border-gray-200 bg-gray-500/10 text-gray-600"
                                                >
                                                    <FileText
                                                        class="mr-1 h-3 w-3"
                                                    />
                                                    Descarga #{{ download.id }}
                                                </Badge>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Expanded Details -->
                                    <div
                                        v-if="expandedItems.has(download.id)"
                                        class="mt-4 border-t border-border pt-4 pl-16"
                                    >
                                        <div
                                            class="grid grid-cols-1 gap-4 text-sm md:grid-cols-2"
                                        >
                                            <div>
                                                <h4
                                                    class="mb-2 flex items-center gap-2 font-medium text-foreground"
                                                >
                                                    <Globe
                                                        class="h-4 w-4 text-blue-500"
                                                    />
                                                    Información de Conexión
                                                </h4>
                                                <div
                                                    class="space-y-1 text-muted-foreground"
                                                >
                                                    <p>
                                                        <strong>IP:</strong>
                                                        {{
                                                            download.ip_address
                                                        }}
                                                    </p>
                                                    <p>
                                                        <strong
                                                            >Fecha y
                                                            Hora:</strong
                                                        >
                                                        {{
                                                            formatDateTime(
                                                                download.downloaded_at,
                                                            )
                                                        }}
                                                    </p>
                                                    <p
                                                        v-if="
                                                            download.user_agent
                                                        "
                                                        class="break-all"
                                                    >
                                                        <strong
                                                            >User Agent:</strong
                                                        >
                                                        {{
                                                            download.user_agent
                                                        }}
                                                    </p>
                                                </div>
                                            </div>
                                            <div>
                                                <h4
                                                    class="mb-2 flex items-center gap-2 font-medium text-foreground"
                                                >
                                                    <BookOpen
                                                        class="h-4 w-4 text-green-500"
                                                    />
                                                    Información del Libro
                                                </h4>
                                                <div
                                                    class="space-y-1 text-muted-foreground"
                                                >
                                                    <p>
                                                        <strong>Título:</strong>
                                                        {{
                                                            download.book.title
                                                        }}
                                                    </p>
                                                    <p>
                                                        <strong>ISBN:</strong>
                                                        {{ download.book.isbn }}
                                                    </p>
                                                    <p>
                                                        <strong>Tipo:</strong>
                                                        {{
                                                            bookTypeLabels[
                                                                download.book
                                                                    .book_type
                                                            ]
                                                        }}
                                                    </p>
                                                    <Button
                                                        variant="outline"
                                                        size="sm"
                                                        as-child
                                                        class="mt-2"
                                                    >
                                                        <Link
                                                            :href="`/admin/books/${download.book.id}`"
                                                        >
                                                            <Eye
                                                                class="mr-1 h-3 w-3"
                                                            />
                                                            Ver libro
                                                        </Link>
                                                    </Button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Actions -->
                                <div
                                    class="ml-4 flex flex-shrink-0 items-center gap-2"
                                >
                                    <Button
                                        variant="ghost"
                                        size="sm"
                                        @click="toggleExpand(download.id)"
                                        class="h-8 w-8 p-0 opacity-50 transition-opacity group-hover:opacity-100"
                                    >
                                        <component
                                            :is="
                                                expandedItems.has(download.id)
                                                    ? ChevronUp
                                                    : ChevronDown
                                            "
                                            class="h-4 w-4"
                                        />
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Empty State -->
                    <div
                        v-else
                        class="m-6 rounded-xl border-2 border-dashed border-border py-16 text-center"
                    >
                        <div class="mx-auto max-w-md">
                            <div
                                class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-primary/10 p-4"
                            >
                                <Download class="h-10 w-10 text-primary" />
                            </div>
                            <h3 class="mb-3 text-2xl font-bold text-foreground">
                                No se encontraron descargas
                            </h3>
                            <p class="mb-6 text-lg text-muted-foreground">
                                {{ user.name }} no ha realizado ninguna descarga
                                aún
                            </p>
                            <div class="flex justify-center gap-3">
                                <Button
                                    variant="outline"
                                    as-child
                                    class="border-primary/20 text-primary hover:bg-primary hover:text-primary-foreground"
                                >
                                    <Link :href="`/admin/users/${user.id}`">
                                        <User class="mr-2 h-4 w-4" />
                                        Volver al Usuario
                                    </Link>
                                </Button>
                                <Button
                                    as-child
                                    class="bg-primary text-primary-foreground hover:bg-primary/90"
                                >
                                    <Link href="/admin/books">
                                        <BookOpen class="mr-2 h-4 w-4" />
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
                            'rounded-lg px-3 py-1.5 text-sm font-medium transition-colors',
                            link.active
                                ? 'bg-primary text-primary-foreground hover:bg-primary/90'
                                : 'border border-input bg-background hover:bg-accent hover:text-accent-foreground',
                            !link.url
                                ? 'cursor-not-allowed opacity-50'
                                : 'cursor-pointer',
                        ]"
                        v-html="link.label"
                        preserve-scroll
                    />
                </div>
            </div>

            <!-- Quick Stats Footer -->
            <Card class="border-border bg-muted/50">
                <CardContent class="p-6">
                    <div
                        class="grid grid-cols-1 gap-6 text-center md:grid-cols-4"
                    >
                        <div>
                            <p class="text-2xl font-bold text-foreground">
                                {{ totalDownloads }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                Total Descargas
                            </p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-foreground">
                                {{ todayDownloads }}
                            </p>
                            <p class="text-sm text-muted-foreground">Hoy</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-foreground">
                                {{ thisWeekDownloads }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                Esta Semana
                            </p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-foreground">
                                {{ downloads.data.length }}
                            </p>
                            <p class="text-sm text-muted-foreground">
                                En esta página
                            </p>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
