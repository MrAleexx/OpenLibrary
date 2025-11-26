<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import LandingHeader from '@/components/landing/LandingHeader.vue';
import LandingFooter from '@/components/landing/LandingFooter.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { ref } from 'vue';
import { ChevronRight, Upload, Home } from 'lucide-vue-next';

const form = useForm({
    email: '',
    service_type: '',
    first_name: '',
    last_name: '',
    document_number: '',
    phone: '',
    subject: '',
    description: '',
    file: null as File | null,
});

const fileInput = ref<HTMLInputElement | null>(null);

const submit = () => {
    form.post('/claims', {
        onSuccess: () => {
            form.reset();
        },
    });
};

const handleFileChange = (e: Event) => {
    const target = e.target as HTMLInputElement;
    if (target.files && target.files.length > 0) {
        form.file = target.files[0];
    }
};

const handleDrop = (e: DragEvent) => {
    e.preventDefault();
    if (e.dataTransfer?.files && e.dataTransfer.files.length > 0) {
        form.file = e.dataTransfer.files[0];
    }
};
</script>

<template>

    <Head title="Libro de Reclamaciones" />

    <div class="min-h-screen bg-background flex flex-col">
        <LandingHeader :can-register="true" />

        <main class="flex-grow container mx-auto px-4 py-8">
            <div class="max-w-3xl mx-auto">
                <!-- Breadcrumbs -->
                <nav class="flex items-center text-sm text-muted-foreground mb-8">
                    <a href="/" class="hover:text-foreground transition-colors flex items-center gap-1">
                        <Home class="w-4 h-4" />
                        Inicio
                    </a>
                    <ChevronRight class="w-4 h-4 mx-2" />
                    <span class="text-foreground font-medium">Libro de Reclamaciones</span>
                </nav>
                <Card>
                    <CardHeader>
                        <CardTitle class="text-2xl">Libro de Reclamaciones</CardTitle>
                        <CardDescription>
                            Por favor, ingresa un correo electrónico válido para que podamos contactarte.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-6">

                            <div class="space-y-2">
                                <Label for="email">Correo electrónico *</Label>
                                <Input id="email" v-model="form.email" type="email" required
                                    placeholder="ejemplo@correo.com" />
                                <p v-if="form.errors.email" class="text-sm text-destructive">{{ form.errors.email }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="service_type">Tipo de servicio o producto *</Label>
                                <Select v-model="form.service_type">
                                    <SelectTrigger>
                                        <SelectValue placeholder="--Seleccionar--" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="biblioteca_virtual">Biblioteca Virtual</SelectItem>
                                        <SelectItem value="prestamos">Préstamos</SelectItem>
                                        <SelectItem value="atencion_cliente">Atención al Cliente</SelectItem>
                                        <SelectItem value="infraestructura">Infraestructura</SelectItem>
                                        <SelectItem value="otros">Otros</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.service_type" class="text-sm text-destructive">{{
                                    form.errors.service_type }}</p>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="last_name">Apellidos *</Label>
                                    <Input id="last_name" v-model="form.last_name" required />
                                    <p v-if="form.errors.last_name" class="text-sm text-destructive">{{
                                        form.errors.last_name }}</p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="first_name">Nombres *</Label>
                                    <Input id="first_name" v-model="form.first_name" required />
                                    <p v-if="form.errors.first_name" class="text-sm text-destructive">{{
                                        form.errors.first_name }}</p>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <div class="space-y-2">
                                    <Label for="document_number">Número de DNI *</Label>
                                    <Input id="document_number" v-model="form.document_number" required />
                                    <p v-if="form.errors.document_number" class="text-sm text-destructive">{{
                                        form.errors.document_number }}</p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="phone">Número de celular de contacto *</Label>
                                    <Input id="phone" v-model="form.phone" required />
                                    <p v-if="form.errors.phone" class="text-sm text-destructive">{{ form.errors.phone }}
                                    </p>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="subject">Asunto *</Label>
                                <Input id="subject" v-model="form.subject" required />
                                <p v-if="form.errors.subject" class="text-sm text-destructive">{{ form.errors.subject }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="description">Descripción *</Label>
                                <Textarea id="description" v-model="form.description" required rows="5" />
                                <p v-if="form.errors.description" class="text-sm text-destructive">{{
                                    form.errors.description }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label>Archivos adjuntos (opcional)</Label>
                                <div class="border-2 border-dashed border-muted-foreground/25 rounded-lg p-8 text-center hover:bg-muted/50 transition-colors cursor-pointer"
                                    @dragover.prevent @drop="handleDrop" @click="fileInput?.click()">
                                    <input type="file" ref="fileInput" class="hidden" @change="handleFileChange">
                                    <div class="flex flex-col items-center gap-2">
                                        <Upload class="w-8 h-8 text-muted-foreground" />
                                        <p class="text-sm text-muted-foreground">
                                            <span v-if="form.file">{{ form.file.name }}</span>
                                            <span v-else>Agregue un archivo o suelte archivos aquí</span>
                                        </p>
                                    </div>
                                </div>
                                <p v-if="form.errors.file" class="text-sm text-destructive">{{ form.errors.file }}</p>
                            </div>

                            <div class="flex justify-end">
                                <Button type="submit" :disabled="form.processing">
                                    Enviar Reclamo
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </main>
    </div>
</template>
