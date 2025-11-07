<!-- resources/js/pages/admin/users/Show.vue -->
<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle,
} from '@/components/ui/card'
import { Button } from '@/components/ui/button'
import { Badge } from '@/components/ui/badge'
import { ArrowLeft, User, Mail, IdCard, Phone, Calendar, Download, BookOpen, Clock, Users, UserCheck, UserX, Shield, TrendingUp,  Building, CreditCard, XCircle, Edit, RefreshCw, History, BookMarked, CalendarDays
} from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'

const props = defineProps<{
  user: any
  recentDownloads: any[]
  activeLoans: any[]
  activeReservations: any[]
  loanHistory: any[]
  stats: any
}>()

const breadcrumbs = [
  { title: 'Dashboard', href: '/admin/dashboard' },
  { title: 'Usuarios', href: '/admin/users' },
  { title: `Detalles de ${props.user.name}`, href: '#' },
]

// User type labels and colors
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
  staff: Building
}

// Format date
function formatDate(date: string) {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

function formatDateTime(date: string) {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'short',
    day: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// Check if membership is expired
function isMembershipExpired() {
  if (!props.user.membership_expires_at) return false
  return new Date(props.user.membership_expires_at) < new Date()
}

// Check if temp password is expired
function isTempPasswordExpired() {
  if (!props.user.temp_password_expires_at) return false
  return new Date(props.user.temp_password_expires_at) < new Date()
}

// Get loan status badge
function getLoanStatusBadge(loan: any) {
  if (loan.status === 'returned') {
    return { variant: 'outline', class: 'bg-green-500/10 text-green-600 border-green-200', label: 'Devuelto' }
  } else if (loan.status === 'overdue') {
    return { variant: 'destructive', class: 'bg-red-500/10 text-red-600 border-red-200', label: 'Vencido' }
  } else {
    return { variant: 'outline', class: 'bg-blue-500/10 text-blue-600 border-blue-200', label: 'Activo' }
  }
}

// Get reservation status badge
function getReservationStatusBadge(reservation: any) {
  const statusMap: any = {
    pending: { variant: 'outline', class: 'bg-yellow-500/10 text-yellow-600 border-yellow-200', label: 'Pendiente' },
    ready_for_pickup: { variant: 'outline', class: 'bg-blue-500/10 text-blue-600 border-blue-200', label: 'Listo para recoger' },
    picked_up: { variant: 'outline', class: 'bg-green-500/10 text-green-600 border-green-200', label: 'Recogido' },
    cancelled: { variant: 'destructive', class: 'bg-red-500/10 text-red-600 border-red-200', label: 'Cancelado' },
    expired: { variant: 'destructive', class: 'bg-red-500/10 text-red-600 border-red-200', label: 'Expirado' }
  }
  return statusMap[reservation.status] || { variant: 'outline', label: reservation.status }
}
</script>

<template>
  <Head>
    <title>Detalles de Usuario - {{ user.name }}</title>
  </Head>

  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="p-6 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold text-foreground">
            Detalles de Usuario
          </h1>
          <p class="text-muted-foreground mt-2 flex items-center gap-2">
            <User class="w-4 h-4 text-primary" />
            Información completa de {{ user.name }} {{ user.last_name }}
          </p>
        </div>
        <div class="flex items-center gap-3">
          <Button variant="outline" as-child>
            <Link href="/admin/users">
              <ArrowLeft class="h-4 w-4 mr-2" />
              Volver a Usuarios
            </Link>
          </Button>
          <Button as-child>
            <Link :href="`/admin/users/${user.id}/edit`">
              <Edit class="h-4 w-4 mr-2" />
              Editar Usuario
            </Link>
          </Button>
        </div>
      </div>

      <!-- User Info & Stats Grid -->
      <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
        <!-- Main User Info -->
        <Card class="lg:col-span-2">
          <CardHeader class="bg-muted/50 border-b border-border">
            <CardTitle class="flex items-center gap-2 text-foreground">
              <User class="h-5 w-5 text-primary" />
              Información Personal
            </CardTitle>
            <CardDescription>
              Datos básicos y de contacto del usuario
            </CardDescription>
          </CardHeader>
          <CardContent class="pt-6 space-y-6">
            <!-- Basic Info -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
              <div class="space-y-1">
                <p class="text-sm font-medium text-muted-foreground">Nombre completo</p>
                <p class="text-lg font-semibold text-foreground">{{ user.name }} {{ user.last_name }}</p>
              </div>
              
              <div class="space-y-1">
                <p class="text-sm font-medium text-muted-foreground">Email principal</p>
                <p class="font-medium flex items-center gap-2 text-foreground">
                  <Mail class="h-4 w-4 text-blue-500" />
                  {{ user.email }}
                </p>
              </div>

              <div class="space-y-1">
                <p class="text-sm font-medium text-muted-foreground">DNI</p>
                <p class="font-medium flex items-center gap-2 text-foreground">
                  <IdCard class="h-4 w-4 text-green-500" />
                  {{ user.dni }}
                </p>
              </div>

              <div class="space-y-1">
                <p class="text-sm font-medium text-muted-foreground">Teléfono</p>
                <p class="font-medium flex items-center gap-2 text-foreground">
                  <Phone class="h-4 w-4 text-purple-500" />
                  {{ user.phone }}
                </p>
              </div>
            </div>

            <!-- Institutional Info -->
            <div class="border-t border-border pt-6">
              <h4 class="font-semibold text-foreground mb-4 flex items-center gap-2">
                <Building class="h-4 w-4 text-orange-500" />
                Información Institucional
              </h4>
              <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="space-y-1">
                  <p class="text-sm font-medium text-muted-foreground">Email Institucional</p>
                  <p class="font-medium flex items-center gap-2 text-foreground">
                    <Mail class="h-4 w-4 text-blue-500" />
                    {{ user.institutional_email || 'No especificado' }}
                  </p>
                </div>
                
                <div class="space-y-1">
                  <p class="text-sm font-medium text-muted-foreground">Código Institucional</p>
                  <p class="font-medium flex items-center gap-2 text-foreground">
                    <CreditCard class="h-4 w-4 text-green-500" />
                    {{ user.institutional_id || 'No especificado' }}
                  </p>
                </div>

                <div class="space-y-1">
                  <p class="text-sm font-medium text-muted-foreground">Membresía hasta</p>
                  <p class="font-medium flex items-center gap-2" :class="isMembershipExpired() ? 'text-destructive' : 'text-foreground'">
                    <Calendar class="h-4 w-4" :class="isMembershipExpired() ? 'text-destructive' : 'text-green-500'" />
                    {{ user.membership_expires_at ? formatDate(user.membership_expires_at) : 'Sin fecha de expiración' }}
                    <Badge v-if="isMembershipExpired()" variant="destructive" class="ml-2">
                      Expirado
                    </Badge>
                  </p>
                </div>

                <div class="space-y-1">
                  <p class="text-sm font-medium text-muted-foreground">Máximo de Préstamos</p>
                  <p class="font-medium flex items-center gap-2 text-foreground">
                    <BookOpen class="h-4 w-4 text-purple-500" />
                    {{ user.max_concurrent_loans }} libros
                  </p>
                </div>
              </div>
            </div>

            <!-- Status & Type -->
            <div class="border-t border-border pt-6">
              <h4 class="font-semibold text-foreground mb-4">Estado y Tipo</h4>
              <div class="flex flex-wrap gap-3">
                <Badge :class="userTypeColors[user.user_type]" class="border font-medium">
                  <component :is="userTypeIcons[user.user_type]" class="w-3 h-3 mr-1" />
                  {{ userTypeLabels[user.user_type] }}
                </Badge>
                
                <Badge v-if="user.is_active" variant="outline" class="bg-green-500/10 text-green-600 border-green-200">
                  <UserCheck class="w-3 h-3 mr-1" />
                  Activo
                </Badge>
                <Badge v-else variant="destructive">
                  <UserX class="w-3 h-3 mr-1" />
                  Inactivo
                </Badge>

                <Badge v-if="user.can_download" variant="outline" class="bg-blue-500/10 text-blue-600 border-blue-200">
                  <Download class="w-3 h-3 mr-1" />
                  Puede descargar
                </Badge>
                <Badge v-else variant="outline" class="bg-gray-500/10 text-gray-600 border-gray-200">
                  <XCircle class="w-3 h-3 mr-1" />
                  Sin descargas
                </Badge>

                <Badge v-if="user.is_temp_password" variant="outline" 
                  :class="isTempPasswordExpired() ? 'bg-red-500/10 text-red-600 border-red-200' : 'bg-amber-500/10 text-amber-600 border-amber-200'">
                  <Clock class="w-3 h-3 mr-1" />
                  {{ isTempPasswordExpired() ? 'Contraseña temporal expirada' : 'Contraseña temporal' }}
                </Badge>
              </div>
            </div>
          </CardContent>
        </Card>

        <!-- Stats Cards -->
        <div class="lg:col-span-2 space-y-6">
          <!-- Quick Stats -->
          <div class="grid grid-cols-2 gap-4">
            <Card class="bg-gradient-to-br from-blue-500/10 to-blue-600/5 border-blue-200">
              <CardContent class="p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-blue-600 mb-1">Descargas Totales</p>
                    <p class="text-2xl font-bold text-foreground">{{ stats.total_downloads }}</p>
                  </div>
                  <Download class="h-8 w-8 text-blue-500" />
                </div>
              </CardContent>
            </Card>

            <Card class="bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 border-emerald-200">
              <CardContent class="p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-emerald-600 mb-1">Préstamos Totales</p>
                    <p class="text-2xl font-bold text-foreground">{{ stats.total_loans }}</p>
                  </div>
                  <BookOpen class="h-8 w-8 text-emerald-500" />
                </div>
              </CardContent>
            </Card>

            <Card class="bg-gradient-to-br from-purple-500/10 to-purple-600/5 border-purple-200">
              <CardContent class="p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-purple-600 mb-1">Reservas Totales</p>
                    <p class="text-2xl font-bold text-foreground">{{ stats.total_reservations }}</p>
                  </div>
                  <Calendar class="h-8 w-8 text-purple-500" />
                </div>
              </CardContent>
            </Card>

            <Card class="bg-gradient-to-br from-orange-500/10 to-orange-600/5 border-orange-200">
              <CardContent class="p-4">
                <div class="flex items-center justify-between">
                  <div>
                    <p class="text-sm font-medium text-orange-600 mb-1">Descargas Hoy</p>
                    <p class="text-2xl font-bold text-foreground">{{ stats.downloads_today }}</p>
                  </div>
                  <TrendingUp class="h-8 w-8 text-orange-500" />
                </div>
              </CardContent>
            </Card>
          </div>

          <!-- Quick Actions -->
          <Card>
            <CardHeader>
              <CardTitle class="text-foreground">Acciones Rápidas</CardTitle>
              <CardDescription>
                Gestiona rápidamente este usuario
              </CardDescription>
            </CardHeader>
            <CardContent class="space-y-3">
              <Button variant="outline" class="w-full justify-start" as-child>
                <Link :href="`/admin/users/${user.id}/edit`">
                  <Edit class="h-4 w-4 mr-2" />
                  Editar información
                </Link>
              </Button>
              <Button variant="outline" class="w-full justify-start" as-child>
                <Link :href="`/admin/users/${user.id}/download-history`">
                  <Download class="h-4 w-4 mr-2" />
                  Ver historial de descargas
                </Link>
              </Button>
              <Button variant="outline" class="w-full justify-start" as-child>
                <Link :href="`/admin/users/${user.id}/loan-history`">
                  <BookOpen class="h-4 w-4 mr-2" />
                  Ver historial de préstamos
                </Link>
              </Button>
              <Button variant="outline" class="w-full justify-start" as-child>
                <Link :href="`/admin/users/${user.id}/reset-password`" method="patch" as="button">
                  <RefreshCw class="h-4 w-4 mr-2" />
                  Resetear contraseña
                </Link>
              </Button>
            </CardContent>
          </Card>

          <!-- System Info -->
          <Card>
            <CardHeader>
              <CardTitle class="text-foreground">Información del Sistema</CardTitle>
            </CardHeader>
            <CardContent class="space-y-3 text-sm">
              <div class="flex justify-between">
                <span class="text-muted-foreground">Registrado el:</span>
                <span class="font-medium">{{ formatDate(user.created_at) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Última actualización:</span>
                <span class="font-medium">{{ formatDate(user.updated_at) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Último acceso:</span>
                <span class="font-medium">{{ user.last_login_at ? formatDateTime(user.last_login_at) : 'Nunca' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-muted-foreground">Creado por:</span>
                <span class="font-medium">{{ user.creator?.name || 'Sistema' }}</span>
              </div>
            </CardContent>
          </Card>
        </div>
      </div>

      <!-- Recent Activity Sections -->
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Downloads -->
        <Card class="lg:col-span-1">
          <CardHeader>
            <CardTitle class="flex items-center gap-2 text-foreground">
              <Download class="h-5 w-5 text-blue-500" />
              Descargas Recientes
            </CardTitle>
            <CardDescription>
              {{ recentDownloads.length }} descargas recientes
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="recentDownloads.length > 0" class="space-y-3">
              <div v-for="download in recentDownloads" :key="download.id" class="flex items-center justify-between p-3 rounded-lg border border-border hover:bg-accent/50 transition-colors">
                <div class="flex-1 min-w-0">
                  <p class="font-medium text-sm text-foreground truncate">
                    {{ download.book?.title || 'Libro no disponible' }}
                  </p>
                  <p class="text-xs text-muted-foreground">
                    {{ formatDateTime(download.downloaded_at) }}
                  </p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-muted-foreground">
              <Download class="h-12 w-12 mx-auto mb-3 opacity-50" />
              <p>No hay descargas recientes</p>
            </div>
          </CardContent>
        </Card>

        <!-- Active Loans -->
        <Card class="lg:col-span-1">
          <CardHeader>
            <CardTitle class="flex items-center gap-2 text-foreground">
              <BookOpen class="h-5 w-5 text-emerald-500" />
              Préstamos Activos
            </CardTitle>
            <CardDescription>
              {{ activeLoans.length }} préstamos en curso
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="activeLoans.length > 0" class="space-y-3">
              <div v-for="loan in activeLoans" :key="loan.id" class="p-3 rounded-lg border border-border hover:bg-accent/50 transition-colors">
                <div class="flex items-start justify-between mb-2">
                  <p class="font-medium text-sm text-foreground flex-1 min-w-0 pr-2">
                    {{ loan.physical_copy?.book?.title || 'Libro no disponible' }}
                  </p>
                  <Badge :class="getLoanStatusBadge(loan).class" class="text-xs">
                    {{ getLoanStatusBadge(loan).label }}
                  </Badge>
                </div>
                <div class="text-xs text-muted-foreground space-y-1">
                  <p>Vence: {{ formatDate(loan.due_date) }}</p>
                  <p>Préstamo: {{ formatDate(loan.loan_date) }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-muted-foreground">
              <BookOpen class="h-12 w-12 mx-auto mb-3 opacity-50" />
              <p>No hay préstamos activos</p>
            </div>
          </CardContent>
        </Card>

        <!-- Active Reservations -->
        <Card class="lg:col-span-1">
          <CardHeader>
            <CardTitle class="flex items-center gap-2 text-foreground">
              <CalendarDays class="h-5 w-5 text-purple-500" />
              Reservas Activas
            </CardTitle>
            <CardDescription>
              {{ activeReservations.length }} reservas pendientes
            </CardDescription>
          </CardHeader>
          <CardContent>
            <div v-if="activeReservations.length > 0" class="space-y-3">
              <div v-for="reservation in activeReservations" :key="reservation.id" class="p-3 rounded-lg border border-border hover:bg-accent/50 transition-colors">
                <div class="flex items-start justify-between mb-2">
                  <p class="font-medium text-sm text-foreground flex-1 min-w-0 pr-2">
                    {{ reservation.book?.title || 'Libro no disponible' }}
                  </p>
                  <Badge :class="getReservationStatusBadge(reservation).class" class="text-xs">
                    {{ getReservationStatusBadge(reservation).label }}
                  </Badge>
                </div>
                <div class="text-xs text-muted-foreground space-y-1">
                  <p>Reserva: {{ formatDate(reservation.reservation_date) }}</p>
                  <p>Recoger antes: {{ formatDate(reservation.pickup_deadline) }}</p>
                </div>
              </div>
            </div>
            <div v-else class="text-center py-8 text-muted-foreground">
              <CalendarDays class="h-12 w-12 mx-auto mb-3 opacity-50" />
              <p>No hay reservas activas</p>
            </div>
          </CardContent>
        </Card>
      </div>

      <!-- Additional Actions Footer -->
      <Card>
        <CardContent class="p-6">
          <div class="flex flex-wrap gap-4 justify-center">
            <Button variant="outline" as-child>
              <Link :href="`/admin/users/${user.id}/download-history`" class="flex items-center gap-2">
                <History class="h-4 w-4" />
                Historial Completo de Descargas
              </Link>
            </Button>
            <Button variant="outline" as-child>
              <Link :href="`/admin/users/${user.id}/loan-history`" class="flex items-center gap-2">
                <BookMarked class="h-4 w-4" />
                Historial Completo de Préstamos
              </Link>
            </Button>
            <Button variant="outline" as-child>
              <Link href="/admin/users" class="flex items-center gap-2">
                <Users class="h-4 w-4" />
                Volver al Listado
              </Link>
            </Button>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>