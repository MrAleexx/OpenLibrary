// resources/types/index.d.ts

import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    last_name: string;
    email: string;
    institutional_email?: string | null;
    microsoft_id?: string | null;
    email_verified_at: string | null;
    password: string;
    is_temp_password: boolean;
    temp_password_expires_at?: string | null;
    remember_token?: string | null;
    dni: string;
    phone: string;
    user_type: 'student' | 'teacher' | 'external' | 'staff';
    institutional_id?: string | null;
    membership_expires_at?: string | null;
    max_concurrent_loans: number;
    can_download: boolean;
    downloads_today: number;
    last_download_reset?: string | null;
    created_by?: number | null;
    is_active: boolean;
    last_login_at?: string | null;
    created_at: string;
    updated_at: string;
    avatar?: string | null;
    // Campos de Fortify
    two_factor_secret?: string | null;
    two_factor_recovery_codes?: string | null;
    two_factor_confirmed_at?: string | null;
    // Relaciones (si las necesitas en frontend)
    roles?: Array<{ id: number; name: string; guard_name: string }>;
    permissions?: Array<{ id: number; name: string; guard_name: string }>;
}

export type BreadcrumbItemType = BreadcrumbItem;

// Tipos para las props de las páginas
export interface AdminDashboardProps {
    stats: {
        total_books: number;
        total_users: number;
        total_downloads: number;
        active_loans: number;
        pending_reservations: number;
    };
    userRole: string;
}

export interface UserDashboardProps {
    recentDownloads: Array<{
        id: number;
        downloaded_at: string;
        book: {
            id: number;
            title: string;
            cover_image?: string;
        };
    }>;
    activeLoans: Array<{
        id: number;
        due_date: string;
        status: string;
        physical_copy: {
            id: number;
            book: {
                id: number;
                title: string;
            };
        };
    }>;
}

// Tipos para formularios de autenticación
export interface RegisterFormData {
    name: string;
    last_name: string;
    email: string;
    dni: string;
    phone: string;
    password: string;
    password_confirmation: string;
}

export interface LoginFormData {
    email: string;
    password: string;
    remember?: boolean;
}

export interface ProfileUpdateFormData {
    name: string;
    last_name: string;
    email: string;
    dni: string;
    phone: string;
}

// Tipos para estadísticas
export interface LibraryStats {
    total_books: number;
    total_users: number;
    total_downloads: number;
    active_loans: number;
    pending_reservations: number;
    downloads_today: number;
    max_daily_downloads: number;
}

// Tipos para las props del perfil
export interface ProfileProps {
    mustVerifyEmail: boolean;
    status?: string;
    verificationStatus?: string;
}

// Declaraciones de módulos para rutas
declare module '@/routes/verification' {
    export function send(): string;
    export function verify(): string;
    export function notice(): string;
}

// Declaraciones para otros módulos que puedas necesitar
declare module '@/routes' {
    export function route(name: string, params?: any): string;
}

declare module '@/actions/App/Http/Controllers/Settings/ProfileController' {
    export const update: {
        form(): any;
    };
    export default {
        update: {
            form(): any;
        }
    };
}

export interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    parent_id: number | null;
    parent_name: string | null;
    sort_order: number;
    is_active: boolean;
    books_count: number;
    children_count: number;
    created_at: string;
    updated_at: string;
    children?: Category[];
}

export interface CategoryHistory {
    id: number;
    action: string;
    description: string;
    user: {
        name: string;
    };
    created_at: string;
}

export interface AdminCategoriesProps {
    categories: Category[];
    viewType?: 'table' | 'tree';
}

export interface AdminCategoryCreateProps {
    parentCategories: Array<{ id: number; name: string }>;
    nextSortOrder: number;
}

export interface AdminCategoryEditProps {
    category: Category;
    parentCategories: Array<{ id: number; name: string }>;
    availableOrders: number[];
    maxSortOrder: number;
}

interface FlashMessages {
  success?: string;
  error?: string;
  import_results?: {
    total_rows: number;
    imported: number;
    skipped: number;
    errors: Array<{
      row: number;
      field: string;
      error: string;
      value: string;
      type: string;
    }>;
    has_errors: boolean;
  };
  import_errors?: Array<{
    row: number;
    field: string;
    error: string;
    value: string;
  }>;
}

interface PageProps {
  flash?: FlashMessages;
}