<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Eye } from 'lucide-vue-next';

defineProps<{
    claims: {
        data: Array<{
            id: number;
            created_at: string;
            first_name: string;
            last_name: string;
            subject: string;
            status: string;
            service_type: string;
        }>;
        links: Array<any>;
    };
}>();

const breadcrumbs = [
    { title: 'Dashboard', href: '/admin/dashboard' },
    { title: 'Reclamos', href: '/admin/claims' },
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

    <Head title="Gestión de Reclamos" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="space-y-8 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-foreground">Gestión de Reclamos</h1>
                    <p class="mt-2 text-muted-foreground">Administra los reclamos y sugerencias de los usuarios</p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Listado de Reclamos</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="relative overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="text-xs text-muted-foreground uppercase bg-muted/50">
                                <tr>
                                    <th class="px-6 py-3">ID</th>
                                    <th class="px-6 py-3">Fecha</th>
                                    <th class="px-6 py-3">Usuario</th>
                                    <th class="px-6 py-3">Asunto</th>
                                    <th class="px-6 py-3">Servicio</th>
                                    <th class="px-6 py-3">Estado</th>
                                    <th class="px-6 py-3">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="claim in claims.data" :key="claim.id"
                                    class="bg-background border-b hover:bg-muted/50 transition-colors">
                                    <td class="px-6 py-4 font-medium">#{{ claim.id }}</td>
                                    <td class="px-6 py-4">{{ new Date(claim.created_at).toLocaleDateString() }}</td>
                                    <td class="px-6 py-4">{{ claim.first_name }} {{ claim.last_name }}</td>
                                    <td class="px-6 py-4">{{ claim.subject }}</td>
                                    <td class="px-6 py-4">{{ claim.service_type }}</td>
                                    <td class="px-6 py-4">
                                        <Badge :class="statusColors[claim.status] || 'bg-gray-100'">
                                            {{ statusLabels[claim.status] || claim.status }}
                                        </Badge>
                                    </td>
                                    <td class="px-6 py-4">
                                        <Button variant="ghost" size="icon" as-child>
                                            <Link :href="`/admin/claims/${claim.id}`">
                                            <Eye class="w-4 h-4" />
                                            </Link>
                                        </Button>
                                    </td>
                                </tr>
                                <tr v-if="claims.data.length === 0">
                                    <td colspan="7" class="px-6 py-8 text-center text-muted-foreground">
                                        No hay reclamos registrados.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>
            <!-- Pagination -->
            <div v-if="claims.data.length > 0" class="flex justify-center mt-4">
                <div class="flex gap-2">
                    <Button v-for="(link, index) in claims.links" :key="index" :disabled="!link.url"
                        :variant="link.active ? 'default' : 'outline'" size="sm" class="rounded-lg font-medium"
                        as-child>
                        <Link v-if="link.url" :href="link.url" v-html="link.label" />
                        <span v-else v-html="link.label" />
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
