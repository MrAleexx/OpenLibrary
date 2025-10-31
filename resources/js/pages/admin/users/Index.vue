<!-- resources/js/pages/admin/users/Index.vue -->
<script setup lang="ts">
import { ref, computed, watch } from 'vue'
import { router, Head, Link } from '@inertiajs/vue3'
import { 
  Card, 
  CardContent, 
  CardDescription, 
  CardHeader, 
  CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import { Badge } from '@/components/ui/badge'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuTrigger,
  DropdownMenuSeparator
} from '@/components/ui/dropdown-menu'
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select'
import { 
  Search, 
  Plus, 
  MoreHorizontal, 
  Eye, 
  Edit, 
  Trash2,
  Users,
  User,
  Download,
  BookOpen,
  Filter,
  X,
  RefreshCw,
  Calendar,
  UserPlus,
  FileDown,
  UserCheck,
  UserX,
  Clock,
  Shield,
  Mail,
  IdCard,
  Library,
  TrendingUp,
  Zap,
  Hash
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'

// Props with default values
const props = withDefaults(defineProps<{
  users: {
    data: Array<{
      id: number
      name: string
      last_name: string
      email: string
      dni: string
      institutional_id?: string
      user_type: string
      is_active: boolean
      is_temp_password: boolean
      membership_expires_at?: string
      downloads_count: number
      loans_count: number
      reservations_count: number
      created_at: string
    }>
    total: number
    links: Array<{ url: string | null; label: string; active: boolean }>
  }
  filters: {
    search?: string
    user_type?: string
    status?: string
    membership_status?: string
  }
  stats: {
    total_users: number
    active_users: number
    students: number
    teachers: number
  }
}>(), {
  users: () => ({ 
    data: [], 
    total: 0, 
    links: []
  }),
  filters: () => ({}),
  stats: () => ({
    total_users: 0,
    active_users: 0,
    students: 0,
    teachers: 0
  })
})

// Breadcrumbs
const breadcrumbs = [
  { title: 'Dashboard', href: '/admin/dashboard' },
  { title: 'Usuarios', href: '/admin/users' },
]

// Refs
const search = ref(props.filters.search || '')
const selectedUserType = ref(props.filters.user_type || '')
const selectedStatus = ref(props.filters.status || '')
const selectedMembershipStatus = ref(props.filters.membership_status || '')

// Computed
const activeFiltersCount = computed(() => {
  let count = 0
  if (search.value) count++
  if (selectedUserType.value) count++
  if (selectedStatus.value) count++
  if (selectedMembershipStatus.value) count++
  return count
})

// Watchers with debounce
const handleFilterChange = (newFilters: any) => {
  if (router) {
    router.get('/admin/users', {
      search: newFilters.search,
      user_type: newFilters.userType,
      status: newFilters.status,
      membership_status: newFilters.membershipStatus
    }, {
      preserveState: true,
      replace: true,
      preserveScroll: true,
      onError: () => {
        // Manejar errores silenciosamente para evitar problemas durante el desmontaje
      }
    })
  }
}

// Debounce function
function debounce(fn: Function, wait: number) {
  let timeout: number | undefined
  return function(this: any, ...args: any[]) {
    clearTimeout(timeout)
    timeout = window.setTimeout(() => fn.apply(this, args), wait)
  }
}

// Debounced filter change
const debouncedFilterChange = debounce(handleFilterChange, 300)

watch([search, selectedUserType, selectedStatus, selectedMembershipStatus], () => {
  debouncedFilterChange({
    search: search.value,
    userType: selectedUserType.value,
    status: selectedStatus.value,
    membershipStatus: selectedMembershipStatus.value
  })
}, { 
  deep: true,
  flush: 'post'
})

// Methods
function clearFilters() {
  search.value = ''
  selectedUserType.value = ''
  selectedStatus.value = ''
  selectedMembershipStatus.value = ''
}

function toggleActive(user: any) {
  router.patch(`/admin/users/${user.id}/toggle-active`)
}

function resetPassword(user: any) {
  if (confirm(`¿Estás seguro de que quieres resetear la contraseña de ${user.name}?`)) {
    router.patch(`/admin/users/${user.id}/reset-password`, {}, {
      onSuccess: (page) => {
        if (page.props.temp_password) {
          // Mostrar modal con contraseña temporal
          alert(`Contraseña temporal generada: ${page.props.temp_password}\n\nEsta contraseña expirará en 7 días.`)
        }
      }
    })
  }
}

// User type labels and colors usando colores CSS personalizados
const userTypeLabels: any = {
  student: 'Estudiante',
  teacher: 'Docente',
  external: 'Externo',
  staff: 'Staff'
}

const userTypeColors: any = {
  student: 'bg-blue-500/10 text-blue-600 border-blue-200 dark:bg-blue-500/20 dark:text-blue-400 dark:border-blue-800',
  teacher: 'bg-emerald-500/10 text-emerald-600 border-emerald-200 dark:bg-emerald-500/20 dark:text-emerald-400 dark:border-emerald-800',
  external: 'bg-orange-500/10 text-orange-600 border-orange-200 dark:bg-orange-500/20 dark:text-orange-400 dark:border-orange-800',
  staff: 'bg-purple-500/10 text-purple-600 border-purple-200 dark:bg-purple-500/20 dark:text-purple-400 dark:border-purple-800'
}

const userTypeIcons: any = {
  student: Users,
  teacher: Shield,
  external: User,
  staff: IdCard
}

// Format date
function formatDate(date: string) {
  return new Date(date).toLocaleDateString('es-ES')
}

// Check if membership is expired
function isMembershipExpired(user: any) {
  if (!user.membership_expires_at) return false
  return new Date(user.membership_expires_at) < new Date()
}
</script>

<template>
  <Head>
    <title>Gestión de Usuarios</title>
  </Head>
  
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-8">
      <!-- Header Mejorado - Consistente con Books -->
      <div class="mb-8">
        <div class="flex items-center justify-between">
          <div>
            <h1 class="text-3xl font-bold text-foreground">
              Gestión de Usuarios
            </h1>
            <p class="text-muted-foreground mt-2 flex items-center gap-2">
              <Users class="w-4 h-4 text-primary" />
              Administra los usuarios del sistema bibliotecario
            </p>
          </div>
          <div class="flex items-center gap-4">
            <Button as-child class="bg-primary text-primary-foreground hover:bg-primary/90">
              <Link href="/admin/users/create" class="flex items-center gap-2">
                <UserPlus class="w-4 h-4" />
                Nuevo Usuario
              </Link>
            </Button>
            <div class="flex items-center gap-2 px-4 py-2 bg-primary/10 text-primary rounded-lg border border-primary/20">
              <Zap class="w-4 h-4 animate-pulse" />
              <span class="text-sm font-medium">{{ users.data.length }} Usuarios</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Stats Cards - Consistente con Books -->
      <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4 mb-8">
        <!-- Total Usuarios -->
        <div class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-primary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground mb-2">
                  Total Usuarios
                </p>
                <p class="text-3xl font-bold text-foreground">
                  {{ stats.total_users }}
                </p>
                <div class="flex items-center gap-1 mt-2 text-xs text-success">
                  <TrendingUp class="w-3 h-3" />
                  <span>+8% este mes</span>
                </div>
              </div>
              <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <Users class="w-6 h-6 text-primary" />
              </div>
            </div>
          </div>
          <div class="h-1 bg-gradient-to-r from-primary to-primary/60 w-0 group-hover:w-full transition-all duration-500"></div>
        </div>

        <!-- Usuarios Activos -->
        <div class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-secondary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground mb-2">
                  Usuarios Activos
                </p>
                <p class="text-3xl font-bold text-foreground">
                  {{ stats.active_users }}
                </p>
                <div class="flex items-center gap-1 mt-2 text-xs text-success">
                  <TrendingUp class="w-3 h-3" />
                  <span>+5% este mes</span>
                </div>
              </div>
              <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <UserCheck class="w-6 h-6 text-secondary" />
              </div>
            </div>
          </div>
          <div class="h-1 bg-gradient-to-r from-secondary to-secondary/60 w-0 group-hover:w-full transition-all duration-500"></div>
        </div>

        <!-- Estudiantes -->
        <div class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-primary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground mb-2">
                  Estudiantes
                </p>
                <p class="text-3xl font-bold text-foreground">
                  {{ stats.students }}
                </p>
                <div class="flex items-center gap-1 mt-2 text-xs text-muted-foreground">
                  <Library class="w-3 h-3" />
                  <span>Principal grupo</span>
                </div>
              </div>
              <div class="w-12 h-12 bg-primary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <Users class="w-6 h-6 text-primary" />
              </div>
            </div>
          </div>
          <div class="h-1 bg-gradient-to-r from-primary to-primary/60 w-0 group-hover:w-full transition-all duration-500"></div>
        </div>

        <!-- Docentes -->
        <div class="group bg-card overflow-hidden shadow-lg rounded-xl border border-border hover:border-secondary/30 transition-all duration-500 hover:shadow-xl hover:-translate-y-1">
          <div class="p-6">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-medium text-muted-foreground mb-2">
                  Docentes
                </p>
                <p class="text-3xl font-bold text-foreground">
                  {{ stats.teachers }}
                </p>
                <div class="flex items-center gap-1 mt-2 text-xs text-muted-foreground">
                  <Shield class="w-3 h-3" />
                  <span>Personal académico</span>
                </div>
              </div>
              <div class="w-12 h-12 bg-secondary/10 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                <Shield class="w-6 h-6 text-secondary" />
              </div>
            </div>
          </div>
          <div class="h-1 bg-gradient-to-r from-secondary to-secondary/60 w-0 group-hover:w-full transition-all duration-500"></div>
        </div>
      </div>

      <!-- Filters Card - Consistente con Books -->
      <Card class="bg-card rounded-xl border border-border shadow-lg">
        <CardHeader class="pb-4">
          <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div class="flex items-center gap-2">
              <Filter class="h-4 w-4 text-muted-foreground" />
              <CardTitle class="text-lg text-foreground">Filtros y Búsqueda</CardTitle>
              <Badge v-if="activeFiltersCount > 0" variant="secondary" class="bg-primary/10 text-primary border-primary/20">
                {{ activeFiltersCount }} activos
              </Badge>
            </div>
            <Button 
              v-if="activeFiltersCount > 0" 
              variant="outline" 
              size="sm" 
              @click="clearFilters"
              class="border-primary/20 text-primary hover:bg-primary hover:text-primary-foreground"
            >
              <X class="h-4 w-4 mr-1" />
              Limpiar
            </Button>
          </div>
        </CardHeader>
        <CardContent class="space-y-4">
          <!-- Search -->
          <div class="relative">
            <Search class="absolute left-3 top-3 h-4 w-4 text-muted-foreground" />
            <Input
              v-model="search"
              placeholder="Buscar por nombre, email, DNI o código institucional..."
              class="pl-10 bg-background border-border focus:border-primary"
            />
          </div>

          <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- User Type Filter -->
            <Select v-model="selectedUserType">
              <SelectTrigger class="bg-background border-border focus:border-primary">
                <SelectValue placeholder="Todos los tipos" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="">Todos los tipos</SelectItem>
                <SelectItem value="student">Estudiante</SelectItem>
                <SelectItem value="teacher">Docente</SelectItem>
                <SelectItem value="external">Externo</SelectItem>
                <SelectItem value="staff">Staff</SelectItem>
              </SelectContent>
            </Select>

            <!-- Status Filter -->
            <Select v-model="selectedStatus">
              <SelectTrigger class="bg-background border-border focus:border-primary">
                <SelectValue placeholder="Todos los estados" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="">Todos los estados</SelectItem>
                <SelectItem value="active">Activos</SelectItem>
                <SelectItem value="inactive">Inactivos</SelectItem>
              </SelectContent>
            </Select>

            <!-- Membership Status Filter -->
            <Select v-model="selectedMembershipStatus">
              <SelectTrigger class="bg-background border-border focus:border-primary">
                <SelectValue placeholder="Estado membresía" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="">Todos</SelectItem>
                <SelectItem value="active">Membresía Activa</SelectItem>
                <SelectItem value="expired">Membresía Expirada</SelectItem>
              </SelectContent>
            </Select>
          </div>

          <!-- Active Filters Info -->
          <div v-if="activeFiltersCount > 0" class="pt-4 border-t border-border">
            <div class="flex items-center gap-2 text-sm text-muted-foreground">
              <Filter class="w-4 h-4" />
              <span>Filtros activos:</span>
              <span v-if="selectedUserType" class="bg-primary/10 text-primary px-2 py-1 rounded text-xs">
                Tipo: {{ userTypeLabels[selectedUserType] }}
              </span>
              <span v-if="selectedStatus" class="bg-secondary/10 text-secondary px-2 py-1 rounded text-xs">
                Estado: {{ selectedStatus === 'active' ? 'Activos' : 'Inactivos' }}
              </span>
              <span v-if="selectedMembershipStatus" class="bg-primary/10 text-primary px-2 py-1 rounded text-xs">
                Membresía: {{ selectedMembershipStatus === 'active' ? 'Activa' : 'Expirada' }}
              </span>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Results Count - Consistente con Books -->
      <div class="text-sm text-muted-foreground">
        Mostrando {{ users.data.length }} de {{ users.total }} usuarios
      </div>

      <!-- Users Table - Versión Lista/Table -->
      <Card class="bg-card rounded-xl border border-border shadow-lg">
        <CardHeader>
          <CardTitle class="text-foreground">Lista de Usuarios</CardTitle>
          <CardDescription>
            {{ users.total }} usuarios encontrados
          </CardDescription>
        </CardHeader>
        <CardContent class="p-0">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr class="border-b border-border bg-muted/50">
                  <th class="text-left py-4 px-6 font-semibold text-foreground">Usuario</th>
                  <th class="text-left py-4 px-6 font-semibold text-foreground">Tipo</th>
                  <th class="text-left py-4 px-6 font-semibold text-foreground">Contacto</th>
                  <th class="text-left py-4 px-6 font-semibold text-foreground">Estado</th>
                  <th class="text-left py-4 px-6 font-semibold text-foreground">Estadísticas</th>
                  <th class="text-left py-4 px-6 font-semibold text-foreground">Acciones</th>
                </tr>
              </thead>
              <tbody>
                <tr 
                  v-for="user in users.data" 
                  :key="user.id"
                  class="border-b border-border hover:bg-accent/50 transition-colors group"
                >
                  <!-- Información del Usuario -->
                  <td class="py-4 px-6">
                    <div class="flex items-center gap-3">
                      <div class="w-10 h-10 rounded-full bg-primary/10 flex items-center justify-center">
                        <User class="w-5 h-5 text-primary" />
                      </div>
                      <div>
                        <p class="font-semibold text-foreground group-hover:text-primary transition-colors">
                          {{ user.name }} {{ user.last_name }}
                        </p>
                        <div class="flex items-center gap-2 text-xs text-muted-foreground mt-1">
                          <IdCard class="w-3 h-3" />
                          <span>DNI: {{ user.dni }}</span>
                          <span v-if="user.institutional_id" class="flex items-center gap-1">
                            <Hash class="w-3 h-3" />
                            {{ user.institutional_id }}
                          </span>
                        </div>
                      </div>
                    </div>
                  </td>

                  <!-- Tipo de Usuario -->
                  <td class="py-4 px-6">
                    <Badge :class="userTypeColors[user.user_type]" class="border font-medium">
                      <component :is="userTypeIcons[user.user_type]" class="w-3 h-3 mr-1" />
                      {{ userTypeLabels[user.user_type] }}
                    </Badge>
                  </td>

                  <!-- Contacto -->
                  <td class="py-4 px-6">
                    <div class="space-y-1">
                      <div class="flex items-center gap-2 text-sm text-muted-foreground">
                        <Mail class="w-3 h-3" />
                        <span class="truncate max-w-[200px]">{{ user.email }}</span>
                      </div>
                      <div class="text-xs text-muted-foreground">
                        Registro: {{ formatDate(user.created_at) }}
                      </div>
                    </div>
                  </td>

                  <!-- Estado -->
                  <td class="py-4 px-6">
                    <div class="flex flex-wrap gap-1">
                      <Badge v-if="!user.is_active" variant="destructive" class="text-xs">
                        <UserX class="w-3 h-3 mr-1" />
                        Inactivo
                      </Badge>
                      <Badge v-else variant="outline" class="text-xs bg-green-500/10 text-green-600 border-green-200">
                        <UserCheck class="w-3 h-3 mr-1" />
                        Activo
                      </Badge>
                      <Badge v-if="isMembershipExpired(user)" variant="destructive" class="text-xs">
                        <Clock class="w-3 h-3 mr-1" />
                        Expirado
                      </Badge>
                      <Badge v-if="user.is_temp_password" variant="outline" class="text-xs bg-amber-500/10 text-amber-600 border-amber-200">
                        <Clock class="w-3 h-3 mr-1" />
                        Temporal
                      </Badge>
                    </div>
                  </td>

                  <!-- Estadísticas -->
                  <td class="py-4 px-6">
                    <div class="flex items-center gap-4 text-sm text-muted-foreground">
                      <div class="text-center">
                        <Download class="h-4 w-4 mx-auto mb-1 text-blue-500" />
                        <span class="font-semibold text-foreground">{{ user.downloads_count }}</span>
                        <p class="text-xs">Descargas</p>
                      </div>
                      <div class="text-center">
                        <BookOpen class="h-4 w-4 mx-auto mb-1 text-emerald-500" />
                        <span class="font-semibold text-foreground">{{ user.loans_count }}</span>
                        <p class="text-xs">Préstamos</p>
                      </div>
                      <div class="text-center">
                        <Calendar class="h-4 w-4 mx-auto mb-1 text-purple-500" />
                        <span class="font-semibold text-foreground">{{ user.reservations_count }}</span>
                        <p class="text-xs">Reservas</p>
                      </div>
                    </div>
                  </td>

                  <!-- Acciones -->
                  <td class="py-4 px-6">
                    <DropdownMenu>
                      <DropdownMenuTrigger as-child>
                        <Button variant="ghost" size="sm" class="h-8 w-8 p-0 opacity-0 group-hover:opacity-100 transition-opacity duration-200">
                          <MoreHorizontal class="h-4 w-4" />
                        </Button>
                      </DropdownMenuTrigger>
                      <DropdownMenuContent align="end" class="w-48">
                        <DropdownMenuItem as-child>
                          <Link :href="`/admin/users/${user.id}`" class="flex items-center cursor-pointer text-foreground">
                            <Eye class="w-4 h-4 mr-2 text-blue-500" />
                            Ver detalles
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                          <Link :href="`/admin/users/${user.id}/edit`" class="flex items-center cursor-pointer text-foreground">
                            <Edit class="w-4 h-4 mr-2 text-emerald-500" />
                            Editar usuario
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem @click="toggleActive(user)" class="cursor-pointer">
                          <component :is="user.is_active ? UserX : UserCheck" class="w-4 h-4 mr-2 text-orange-500" />
                          {{ user.is_active ? 'Desactivar' : 'Activar' }}
                        </DropdownMenuItem>
                        <DropdownMenuItem @click="resetPassword(user)" class="cursor-pointer">
                          <RefreshCw class="w-4 h-4 mr-2 text-purple-500" />
                          Resetear Contraseña
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem as-child>
                          <Link :href="`/admin/users/${user.id}/download-history`" class="flex items-center cursor-pointer text-foreground">
                            <Download class="w-4 h-4 mr-2 text-blue-500" />
                            Historial Descargas
                          </Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem as-child>
                          <Link :href="`/admin/users/${user.id}/loan-history`" class="flex items-center cursor-pointer text-foreground">
                            <BookOpen class="w-4 h-4 mr-2 text-emerald-500" />
                            Historial Préstamos
                          </Link>
                        </DropdownMenuItem>
                      </DropdownMenuContent>
                    </DropdownMenu>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Empty State -->
          <div 
            v-if="users.data.length === 0" 
            class="text-center py-16 border-2 border-dashed border-border rounded-xl m-6"
          >
            <div class="max-w-md mx-auto">
              <div class="p-4 bg-primary/10 rounded-full w-20 h-20 mx-auto mb-6 flex items-center justify-center">
                <Users class="w-10 h-10 text-primary" />
              </div>
              <h3 class="text-2xl font-bold text-foreground mb-3">No se encontraron usuarios</h3>
              <p class="text-muted-foreground text-lg mb-6">
                {{ activeFiltersCount > 0 ? 'Intenta ajustar tus filtros de búsqueda' : 'Comienza importando usuarios o creando uno manualmente' }}
              </p>
              <div class="flex gap-3 justify-center">
                <Button as-child variant="outline"
                  class="border-primary/20 text-primary hover:bg-primary hover:text-primary-foreground">
                  <Link href="/admin/users/import" class="flex items-center gap-2">
                    <FileDown class="w-4 h-4" />
                    Importar Usuarios
                  </Link>
                </Button>
                <Button as-child v-if="activeFiltersCount === 0"
                  class="bg-primary text-primary-foreground px-6 py-3 rounded-lg inline-flex items-center gap-2 hover:bg-primary/90 transition-colors">
                  <Link href="/admin/users/create" class="flex items-center gap-2">
                    <UserPlus class="w-5 h-5" />
                    Crear Usuario
                  </Link>
                </Button>
                <Button v-else variant="outline" @click="clearFilters"
                  class="border-primary/20 text-primary hover:bg-primary hover:text-primary-foreground">
                  <X class="w-4 h-4 mr-2" />
                  Limpiar Filtros
                </Button>
              </div>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Pagination - Consistente con Books -->
      <div v-if="users.data.length > 0" class="flex justify-center">
        <div class="flex gap-2">
          <Link v-for="(link, index) in users.links" :key="index"
                  :href="link.url ?? ''"
                  :disabled="!link.url"
                  :class="['rounded-lg font-medium px-3 py-1.5 text-sm', link.active ? 'bg-primary text-primary-foreground' : 'bg-background border border-input hover:bg-accent hover:text-accent-foreground', !link.url ? 'opacity-50 cursor-not-allowed' : '']"
                  v-html="link.label"
                  preserve-scroll
           />
        </div>
      </div>
    </div>
  </AppLayout>
</template>