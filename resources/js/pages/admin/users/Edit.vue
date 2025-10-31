<script setup lang="ts">
import { reactive } from 'vue'
import { router } from '@inertiajs/vue3'
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
  Save,
  User
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'

// Props
const props = defineProps<{
  user: any
  userTypes: any[]
}>()

// Form state
const form = reactive({
  name: props.user.name,
  last_name: props.user.last_name,
  email: props.user.email,
  dni: props.user.dni,
  phone: props.user.phone,
  user_type: props.user.user_type,
  institutional_email: props.user.institutional_email || '',
  institutional_id: props.user.institutional_id || '',
  membership_expires_at: props.user.membership_expires_at || '',
  max_concurrent_loans: props.user.max_concurrent_loans,
  can_download: props.user.can_download,
  is_active: props.user.is_active,
})

// Methods
function submit() {
  router.put(`/admin/users/${props.user.id}`, form)
}
</script>

<template>
  <AppLayout>
    <div class="space-y-6">
      <!-- Header -->
      <div class="flex items-center gap-4">
        <Button variant="outline" size="sm" as-child>
          <a href="/admin/users">
            <ArrowLeft class="h-4 w-4 mr-2" />
            Volver
          </a>
        </Button>
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Editar Usuario</h1>
          <p class="text-muted-foreground mt-1">
            Actualiza la información de {{ user.name }} {{ user.last_name }}
          </p>
        </div>
      </div>

      <form @submit.prevent="submit" class="space-y-6">
        <!-- Información Personal -->
        <Card>
          <CardHeader>
            <CardTitle class="flex items-center gap-2">
              <User class="h-5 w-5" />
              Información Personal
            </CardTitle>
            <CardDescription>
              Información básica del usuario
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Nombre -->
              <div>
                <Label for="name">Nombre *</Label>
                <Input
                  id="name"
                  v-model="form.name"
                  placeholder="Ingresa el nombre"
                  required
                />
              </div>

              <!-- Apellido -->
              <div>
                <Label for="last_name">Apellido *</Label>
                <Input
                  id="last_name"
                  v-model="form.last_name"
                  placeholder="Ingresa el apellido"
                  required
                />
              </div>

              <!-- Email -->
              <div>
                <Label for="email">Email *</Label>
                <Input
                  id="email"
                  v-model="form.email"
                  type="email"
                  placeholder="usuario@ejemplo.com"
                  required
                />
              </div>

              <!-- DNI -->
              <div>
                <Label for="dni">DNI *</Label>
                <Input
                  id="dni"
                  v-model="form.dni"
                  placeholder="12345678"
                  maxlength="8"
                  required
                />
              </div>

              <!-- Teléfono -->
              <div>
                <Label for="phone">Teléfono *</Label>
                <Input
                  id="phone"
                  v-model="form.phone"
                  placeholder="987654321"
                  maxlength="9"
                  required
                />
              </div>

              <!-- Tipo de Usuario -->
              <div>
                <Label for="user_type">Tipo de Usuario *</Label>
                <Select v-model="form.user_type">
                  <SelectTrigger>
                    <SelectValue placeholder="Selecciona el tipo" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem 
                      v-for="type in userTypes" 
                      :key="type.value" 
                      :value="type.value"
                    >
                      {{ type.label }}
                    </SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Información Institucional -->
        <Card>
          <CardHeader>
            <CardTitle>Información Institucional</CardTitle>
            <CardDescription>
              Información adicional para usuarios institucionales
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <!-- Email Institucional -->
              <div>
                <Label for="institutional_email">Email Institucional</Label>
                <Input
                  id="institutional_email"
                  v-model="form.institutional_email"
                  type="email"
                  placeholder="usuario@institucion.edu.pe"
                />
              </div>

              <!-- Código Institucional -->
              <div>
                <Label for="institutional_id">Código Institucional</Label>
                <Input
                  id="institutional_id"
                  v-model="form.institutional_id"
                  placeholder="Código o ID institucional"
                />
              </div>

              <!-- Fecha de Expiración de Membresía -->
              <div>
                <Label for="membership_expires_at">Membresía hasta</Label>
                <Input
                  id="membership_expires_at"
                  v-model="form.membership_expires_at"
                  type="date"
                />
              </div>

              <!-- Máximo de Préstamos -->
              <div>
                <Label for="max_concurrent_loans">Máximo de Préstamos *</Label>
                <Input
                  id="max_concurrent_loans"
                  v-model="form.max_concurrent_loans"
                  type="number"
                  min="1"
                  max="10"
                  required
                />
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Configuración -->
        <Card>
          <CardHeader>
            <CardTitle>Configuración</CardTitle>
            <CardDescription>
              Opciones de acceso y permisos del usuario
            </CardDescription>
          </CardHeader>
          <CardContent class="space-y-4">
            <!-- Checkboxes -->
            <div class="flex flex-col gap-3">
              <div class="flex items-center space-x-2">
                <Checkbox id="can_download" v-model="form.can_download" />
                <Label for="can_download" class="text-sm font-normal">
                  Permitir descarga de libros digitales
                </Label>
              </div>
              <div class="flex items-center space-x-2">
                <Checkbox id="is_active" v-model="form.is_active" />
                <Label for="is_active" class="text-sm font-normal">
                  Usuario activo en el sistema
                </Label>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Actions -->
        <Card>
          <CardFooter class="flex justify-between">
            <Button variant="outline" type="button" as-child>
              <a href="/admin/users">Cancelar</a>
            </Button>
            <Button type="submit">
              <Save class="h-4 w-4 mr-2" />
              Actualizar Usuario
            </Button>
          </CardFooter>
        </Card>
      </form>
    </div>
  </AppLayout>
</template>