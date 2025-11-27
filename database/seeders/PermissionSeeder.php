<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // ==================== PERMISOS DE USUARIOS ====================
        Permission::firstOrCreate(['name' => 'view users']);
        Permission::firstOrCreate(['name' => 'create users']);
        Permission::firstOrCreate(['name' => 'edit users']);
        Permission::firstOrCreate(['name' => 'delete users']);
        Permission::firstOrCreate(['name' => 'import users']);
        Permission::firstOrCreate(['name' => 'export users']);
        Permission::firstOrCreate(['name' => 'view user profiles']);
        Permission::firstOrCreate(['name' => 'manage user passwords']);
        Permission::firstOrCreate(['name' => 'toggle user status']);
        Permission::firstOrCreate(['name' => 'view user statistics']);

        // ==================== PERMISOS DE LIBROS ====================
        Permission::firstOrCreate(['name' => 'view books']);
        Permission::firstOrCreate(['name' => 'create books']);
        Permission::firstOrCreate(['name' => 'edit books']);
        Permission::firstOrCreate(['name' => 'delete books']);
        Permission::firstOrCreate(['name' => 'download books']);
        Permission::firstOrCreate(['name' => 'manage featured']);
        Permission::firstOrCreate(['name' => 'publish books']);
        Permission::firstOrCreate(['name' => 'view book statistics']);
        Permission::firstOrCreate(['name' => 'manage book downloads']);

        // ==================== PERMISOS DE CATEGORÍAS ====================
        Permission::firstOrCreate(['name' => 'view categories']);
        Permission::firstOrCreate(['name' => 'create categories']);
        Permission::firstOrCreate(['name' => 'edit categories']);
        Permission::firstOrCreate(['name' => 'delete categories']);

        // ==================== PERMISOS DE PRÉSTAMOS ====================
        Permission::firstOrCreate(['name' => 'view loans']);
        Permission::firstOrCreate(['name' => 'create loans']);
        Permission::firstOrCreate(['name' => 'edit loans']);
        Permission::firstOrCreate(['name' => 'delete loans']);
        Permission::firstOrCreate(['name' => 'renew loans']);
        Permission::firstOrCreate(['name' => 'manage overdue loans']);

        // ==================== PERMISOS DE RESERVAS ====================
        Permission::firstOrCreate(['name' => 'view reservations']);
        Permission::firstOrCreate(['name' => 'create reservations']);
        Permission::firstOrCreate(['name' => 'edit reservations']);
        Permission::firstOrCreate(['name' => 'delete reservations']);
        Permission::firstOrCreate(['name' => 'manage reservation queue']);

        // ==================== PERMISOS DE COPIAS FÍSICAS ====================
        Permission::firstOrCreate(['name' => 'view physical copies']);
        Permission::firstOrCreate(['name' => 'create physical copies']);
        Permission::firstOrCreate(['name' => 'edit physical copies']);
        Permission::firstOrCreate(['name' => 'delete physical copies']);
        Permission::firstOrCreate(['name' => 'manage copy status']);

        // ==================== PERMISOS DE REPORTES ====================
        Permission::firstOrCreate(['name' => 'view reports']);
        Permission::firstOrCreate(['name' => 'export reports']);
        Permission::firstOrCreate(['name' => 'view usage statistics']);
        Permission::firstOrCreate(['name' => 'view audit logs']);

        // ==================== PERMISOS DE CONFIGURACIÓN ====================
        Permission::firstOrCreate(['name' => 'view settings']);
        Permission::firstOrCreate(['name' => 'edit settings']);
        Permission::firstOrCreate(['name' => 'view dashboard']);
        Permission::firstOrCreate(['name' => 'manage system logs']);

        // ==================== ROLES Y ASIGNACIÓN ====================

        // 👑 ADMINISTRADOR - Acceso total
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(Permission::all());

        // 📚 BIBLIOTECARIO - Gestión operativa
        $librarian = Role::firstOrCreate(['name' => 'librarian']);
        $librarian->syncPermissions([
            // Usuarios
            'view users',
            'edit users',
            'view user profiles',
            'manage user passwords',
            'toggle user status',

            // Libros
            'view books',
            'create books',
            'edit books',
            'download books',
            'manage featured',
            'publish books',
            'view book statistics',
            'manage book downloads',

            // Categorías
            'view categories',
            'create categories',
            'edit categories',
            'delete categories',

            // Préstamos
            'view loans',
            'create loans',
            'edit loans',
            'renew loans',
            'manage overdue loans',

            // Reservas
            'view reservations',
            'create reservations',
            'edit reservations',
            'manage reservation queue',

            // Copias físicas
            'view physical copies',
            'create physical copies',
            'edit physical copies',
            'manage copy status',

            // Reportes
            'view reports',
            'export reports',
            'view usage statistics',

            // Dashboard
            'view dashboard',
        ]);

        // 👤 USUARIO REGULAR - Acceso básico (Estudiantes, Externos, Docentes)
        $user = Role::firstOrCreate(['name' => 'user']);
        $user->syncPermissions([
            'view books',
            'view categories',
            'download books',
            'view user profiles', // Para ver su propio perfil
        ]);
    }
}
