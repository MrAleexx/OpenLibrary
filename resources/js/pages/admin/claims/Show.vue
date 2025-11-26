<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { ArrowLeft, FileText, Download } from 'lucide-vue-next';

defineProps<{
    claim: {
        id: number;
        created_at: string;
        email: string;
        service_type: string;
        first_name: string;
        last_name: string;
        document_number: string;
        phone: string;
        subject: string;
        description: string;
        file_path: string | null;
        status: string;
    };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Reclamos', href: '/admin/claims' },
    { title: 'Detalle', href: '#' },
];

const statusColors: Record<string, string> = {
    pending: 'bg-yellow-500/10 text-yellow-600 border-yellow-200',
    reviewed: 'bg-blue-500/10 text-blue-600 border-blue-200',
    resolved: 'bg-green-500/10 text-green-600 border-green-200',
};

const statusLabels: Record<string, string> = {
    pending: 'Pendiente',
    reviewed: 'Revisado',
    resolved: 'Resuelto',
};
</script>

<template>

    <Head title="Detalle de Reclamo" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-6">
            <div class="flex items-center gap-4">
                <Button variant="outline" size="icon" as-child>
                    <Link href="/admin/claims">
                    <ArrowLeft class="w-4 h-4" />
                    </Link>
                </Button>
                <div>
                    <h1 class="text-3xl font-bold text-foreground">Reclamo #{{ claim.id }}</h1>
                    <p class="mt-2 text-muted-foreground">Detalles del reclamo recibido</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Main Content -->
                <div class="md:col-span-2 space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Información del Reclamo</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">Asunto</h3>
                                <p class="text-lg font-medium">{{ claim.subject }}</p>
                            </div>

                            <div>
                                <h3 class="text-sm font-medium text-muted-foreground mb-1">Descripción</h3>
                                <p class="text-base whitespace-pre-wrap p-4 bg-muted/30 rounded-lg">{{ claim.description
                                    }}</p>
                            </div>

                            <div v-if="claim.file_path">
                                <h3 class="text-sm font-medium text-muted-foreground mb-2">Archivo Adjunto</h3>
                                <a :href="`/storage/${claim.file_path}`" target="_blank"
                                    class="inline-flex items-center gap-2 p-3 border rounded-lg hover:bg-muted transition-colors">
                                    <FileText class="w-5 h-5 text-primary" />
                                    <span class="text-sm font-medium">Ver archivo adjunto</span>
                                    <Download class="w-4 h-4 text-muted-foreground ml-2" />
                                </a>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar Info -->
                <div class="space-y-6">
                    <Card>
                        <CardHeader>
                            <CardTitle>Estado</CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Badge
                                :class="['w-full justify-center py-1.5 text-base', statusColors[claim.status] || 'bg-gray-100']">
                                {{ statusLabels[claim.status] || claim.status }}
                            </Badge>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>Información del Usuario</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div>
                                <h3 class="text-xs font-medium text-muted-foreground uppercase">Nombre Completo</h3>
                                <p class="text-sm font-medium">{{ claim.first_name }} {{ claim.last_name }}</p>
                            </div>
                            <div>
                                <h3 class="text-xs font-medium text-muted-foreground uppercase">DNI</h3>
                                <p class="text-sm font-medium">{{ claim.document_number }}</p>
                            </div>
                            <div>
                                <h3 class="text-xs font-medium text-muted-foreground uppercase">Correo Electrónico</h3>
                                <p class="text-sm font-medium">{{ claim.email }}</p>
                            </div>
                            <div>
                                <h3 class="text-xs font-medium text-muted-foreground uppercase">Teléfono</h3>
                                <p class="text-sm font-medium">{{ claim.phone }}</p>
                            </div>
                            <div>
                                <h3 class="text-xs font-medium text-muted-foreground uppercase">Tipo de Servicio</h3>
                                <p class="text-sm font-medium">{{ claim.service_type }}</p>
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
