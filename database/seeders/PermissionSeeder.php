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
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);
        Permission::create(['name' => 'import users']);
        Permission::create(['name' => 'export users']);
        Permission::create(['name' => 'view user profiles']);
        Permission::create(['name' => 'manage user passwords']);
        Permission::create(['name' => 'toggle user status']);
        Permission::create(['name' => 'view user statistics']);

        // ==================== PERMISOS DE LIBROS ====================
        Permission::create(['name' => 'view books']);
        Permission::create(['name' => 'create books']);
        Permission::create(['name' => 'edit books']);
        Permission::create(['name' => 'delete books']);
        Permission::create(['name' => 'download books']);
        Permission::create(['name' => 'manage featured']);
        Permission::create(['name' => 'publish books']);
        Permission::create(['name' => 'view book statistics']);
        Permission::create(['name' => 'manage book downloads']);

        // ==================== PERMISOS DE CATEGORÍAS ====================
        Permission::create(['name' => 'view categories']);
        Permission::create(['name' => 'create categories']);
        Permission::create(['name' => 'edit categories']);
        Permission::create(['name' => 'delete categories']);

        // ==================== PERMISOS DE PRÉSTAMOS ====================
        Permission::create(['name' => 'view loans']);
        Permission::create(['name' => 'create loans']);
        Permission::create(['name' => 'edit loans']);
        Permission::create(['name' => 'delete loans']);
        Permission::create(['name' => 'renew loans']);
        Permission::create(['name' => 'manage overdue loans']);

        // ==================== PERMISOS DE RESERVAS ====================
        Permission::create(['name' => 'view reservations']);
        Permission::create(['name' => 'create reservations']);
        Permission::create(['name' => 'edit reservations']);
        Permission::create(['name' => 'delete reservations']);
        Permission::create(['name' => 'manage reservation queue']);

        // ==================== PERMISOS DE COPIAS FÍSICAS ====================
        Permission::create(['name' => 'view physical copies']);
        Permission::create(['name' => 'create physical copies']);
        Permission::create(['name' => 'edit physical copies']);
        Permission::create(['name' => 'delete physical copies']);
        Permission::create(['name' => 'manage copy status']);

        // ==================== PERMISOS DE REPORTES ====================
        Permission::create(['name' => 'view reports']);
        Permission::create(['name' => 'export reports']);
        Permission::create(['name' => 'view usage statistics']);
        Permission::create(['name' => 'view audit logs']);

        // ==================== PERMISOS DE CONFIGURACIÓN ====================
        Permission::create(['name' => 'view settings']);
        Permission::create(['name' => 'edit settings']);
        Permission::create(['name' => 'view dashboard']);
        Permission::create(['name' => 'manage system logs']);

        // ==================== ROLES Y ASIGNACIÓN ====================

        // 👑 ADMINISTRADOR - Acceso total
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // 📚 BIBLIOTECARIO - Gestión operativa
        $librarian = Role::create(['name' => 'librarian']);
        $librarian->givePermissionTo([
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
        $user = Role::create(['name' => 'user']);
        $user->givePermissionTo([
            'view books',
            'view categories',
            'download books',
            'view user profiles', // Para ver su propio perfil
        ]);
    }
}
