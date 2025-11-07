<!-- resources/js/pages/Loans/Index.vue -->
<script setup lang="ts">
import { computed } from 'vue'
import { Head, Link } from '@inertiajs/vue3'
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card'
import { Badge } from '@/components/ui/badge'
import { Button } from '@/components/ui/button'
import { Alert, AlertDescription } from '@/components/ui/alert'
import { BookOpen, Calendar, AlertCircle, Clock, CheckCircle, XCircle } from 'lucide-vue-next'
import AppLayout from '@/layouts/AppLayout.vue'

interface PhysicalCopy {
  id: number
  barcode: string
  book: {
    id: number
    title: string
    cover_image?: string
  }
}

interface BookLoan {
  id: number
  physical_copy: PhysicalCopy
  loan_date: string
  due_date: string
  actual_return_date: string | null
  status: 'active' | 'overdue' | 'returned'
  renewal_count: number
  notes: string | null
}

interface Props {
  activeLoans: BookLoan[]
  loanHistory: BookLoan[]
  stats: {
    total_loans: number
    active_loans: number
    overdue_loans: number
  }
}

const props = defineProps<Props>()

const breadcrumbs = [
  { title: 'Dashboard', href: '/dashboard' },
  { title: 'Mis Préstamos', href: '#' },
]

// Computed
const hasActiveLoans = computed(() => props.activeLoans.length > 0)
const hasOverdueLoans = computed(() => props.stats.overdue_loans > 0)

// Helpers
function formatDate(date: string): string {
  return new Date(date).toLocaleDateString('es-ES', {
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}

function getDaysRemaining(dueDate: string): number {
  const due = new Date(dueDate)
  const today = new Date()
  const diffTime = due.getTime() - today.getTime()
  return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
}

function getLoanStatusBadge(loan: BookLoan) {
  if (loan.status === 'returned') {
    return {
      variant: 'outline' as const,
      class: 'bg-green-500/10 text-green-600 border-green-200 dark:bg-green-500/20 dark:text-green-400 dark:border-green-800',
      label: 'Devuelto',
      icon: CheckCircle
    }
  } else if (loan.status === 'overdue') {
    return {
      variant: 'destructive' as const,
      class: 'bg-red-500/10 text-red-600 border-red-200 dark:bg-red-500/20 dark:text-red-400 dark:border-red-800',
      label: 'Vencido',
      icon: XCircle
    }
  } else {
    const daysLeft = getDaysRemaining(loan.due_date)
    if (daysLeft <= 3) {
      return {
        variant: 'outline' as const,
        class: 'bg-orange-500/10 text-orange-600 border-orange-200 dark:bg-orange-500/20 dark:text-orange-400 dark:border-orange-800',
        label: 'Por vencer',
        icon: AlertCircle
      }
    }
    return {
      variant: 'outline' as const,
      class: 'bg-blue-500/10 text-blue-600 border-blue-200 dark:bg-blue-500/20 dark:text-blue-400 dark:border-blue-800',
      label: 'Activo',
      icon: Clock
    }
  }
}

function getBookCover(loan: BookLoan): string {
  return loan.physical_copy.book.cover_image || '/images/default-book-cover.jpg'
}
</script>

<template>
  <AppLayout>
    <Head title="Mis Préstamos" />

    <div class="container mx-auto py-6 px-4 sm:px-6 lg:px-8">
      <!-- Header -->
      <div class="mb-6">
        <h1 class="text-3xl font-bold tracking-tight">Mis Préstamos</h1>
        <p class="text-muted-foreground mt-2">
          Gestiona y revisa el estado de tus préstamos de libros
        </p>
      </div>

      <!-- Stats Cards -->
      <div class="grid gap-4 md:grid-cols-3 mb-6">
        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Total Préstamos</CardTitle>
            <BookOpen class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.total_loans }}</div>
            <p class="text-xs text-muted-foreground">Préstamos históricos</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Préstamos Activos</CardTitle>
            <Clock class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold">{{ stats.active_loans }}</div>
            <p class="text-xs text-muted-foreground">Libros en tu poder</p>
          </CardContent>
        </Card>

        <Card>
          <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">Préstamos Vencidos</CardTitle>
            <AlertCircle class="h-4 w-4 text-muted-foreground" />
          </CardHeader>
          <CardContent>
            <div class="text-2xl font-bold" :class="hasOverdueLoans ? 'text-red-600' : ''">
              {{ stats.overdue_loans }}
            </div>
            <p class="text-xs text-muted-foreground">Requieren devolución urgente</p>
          </CardContent>
        </Card>
      </div>

      <!-- Overdue Alert -->
      <Alert v-if="hasOverdueLoans" variant="destructive" class="mb-6">
        <AlertCircle class="h-4 w-4" />
        <AlertDescription>
          Tienes {{ stats.overdue_loans }} préstamo(s) vencido(s). Por favor, devuelve los libros lo antes posible.
        </AlertDescription>
      </Alert>

      <!-- Active Loans -->
      <Card class="mb-6">
        <CardHeader>
          <CardTitle>Préstamos Activos</CardTitle>
          <CardDescription>
            Libros que actualmente tienes en préstamo
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="hasActiveLoans" class="space-y-4">
            <div
              v-for="loan in activeLoans"
              :key="loan.id"
              class="flex flex-col sm:flex-row gap-4 p-4 border rounded-lg hover:bg-accent/50 transition-colors"
            >
              <!-- Book Cover -->
              <div class="flex-shrink-0">
                <img
                  :src="getBookCover(loan)"
                  :alt="loan.physical_copy.book.title"
                  class="w-20 h-28 object-cover rounded-md shadow-sm"
                />
              </div>

              <!-- Loan Info -->
              <div class="flex-1 min-w-0">
                <div class="flex items-start justify-between gap-2 mb-2">
                  <div class="flex-1">
                    <Link
                      :href="`/books/${loan.physical_copy.book.id}`"
                      class="text-lg font-semibold hover:underline line-clamp-2"
                    >
                      {{ loan.physical_copy.book.title }}
                    </Link>
                    <p class="text-sm text-muted-foreground">
                      Código: {{ loan.physical_copy.barcode }}
                    </p>
                  </div>
                  <Badge :class="getLoanStatusBadge(loan).class">
                    <component :is="getLoanStatusBadge(loan).icon" class="w-3 h-3 mr-1" />
                    {{ getLoanStatusBadge(loan).label }}
                  </Badge>
                </div>

                <!-- Dates -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm">
                  <div class="flex items-center gap-2 text-muted-foreground">
                    <Calendar class="w-4 h-4" />
                    <span>Prestado: {{ formatDate(loan.loan_date) }}</span>
                  </div>
                  <div class="flex items-center gap-2">
                    <Calendar class="w-4 h-4" :class="loan.status === 'overdue' ? 'text-red-600' : 'text-muted-foreground'" />
                    <span :class="loan.status === 'overdue' ? 'text-red-600 font-semibold' : 'text-muted-foreground'">
                      Vence: {{ formatDate(loan.due_date) }}
                      <span v-if="loan.status !== 'overdue'" class="ml-1">
                        ({{ getDaysRemaining(loan.due_date) }} días)
                      </span>
                    </span>
                  </div>
                </div>

                <!-- Renewals -->
                <div v-if="loan.renewal_count > 0" class="mt-2 text-sm text-muted-foreground">
                  Renovaciones: {{ loan.renewal_count }}
                </div>

                <!-- Notes -->
                <div v-if="loan.notes" class="mt-2 text-sm text-muted-foreground italic">
                  {{ loan.notes }}
                </div>
              </div>
            </div>
          </div>

          <!-- Empty State -->
          <div v-else class="text-center py-12">
            <BookOpen class="mx-auto h-12 w-12 text-muted-foreground/50" />
            <h3 class="mt-4 text-lg font-semibold">No tienes préstamos activos</h3>
            <p class="mt-2 text-sm text-muted-foreground">
              Explora nuestro catálogo y solicita libros para leer
            </p>
            <Button as-child class="mt-4">
              <Link href="/books">
                Ver Catálogo
              </Link>
            </Button>
          </div>
        </CardContent>
      </Card>

      <!-- Loan History -->
      <Card>
        <CardHeader>
          <CardTitle>Historial de Préstamos</CardTitle>
          <CardDescription>
            Todos tus préstamos anteriores
          </CardDescription>
        </CardHeader>
        <CardContent>
          <div v-if="loanHistory.length > 0" class="space-y-3">
            <div
              v-for="loan in loanHistory"
              :key="loan.id"
              class="flex items-start gap-4 p-3 border rounded-lg hover:bg-accent/30 transition-colors"
            >
              <!-- Book Cover Small -->
              <div class="flex-shrink-0">
                <img
                  :src="getBookCover(loan)"
                  :alt="loan.physical_copy.book.title"
                  class="w-12 h-16 object-cover rounded shadow-sm"
                />
              </div>

              <!-- Info -->
              <div class="flex-1 min-w-0">
                <Link
                  :href="`/books/${loan.physical_copy.book.id}`"
                  class="font-medium hover:underline line-clamp-1"
                >
                  {{ loan.physical_copy.book.title }}
                </Link>
                <div class="flex flex-wrap gap-x-4 gap-y-1 text-sm text-muted-foreground mt-1">
                  <span>{{ formatDate(loan.loan_date) }} - {{ formatDate(loan.actual_return_date || loan.due_date) }}</span>
                  <Badge :class="getLoanStatusBadge(loan).class" class="h-5">
                    {{ getLoanStatusBadge(loan).label }}
                  </Badge>
                </div>
              </div>
            </div>
          </div>

          <!-- Empty History -->
          <div v-else class="text-center py-8 text-muted-foreground">
            <p>No tienes historial de préstamos</p>
          </div>
        </CardContent>
      </Card>
    </div>
  </AppLayout>
</template>
