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

        // Permisos de Usuarios
        Permission::create(['name' => 'view users', 'group' => 'users']);
        Permission::create(['name' => 'create users', 'group' => 'users']);
        Permission::create(['name' => 'edit users', 'group' => 'users']);
        Permission::create(['name' => 'delete users', 'group' => 'users']);
        Permission::create(['name' => 'import users', 'group' => 'users']);

        // Permisos de Libros
        Permission::create(['name' => 'view books', 'group' => 'books']);
        Permission::create(['name' => 'create books', 'group' => 'books']);
        Permission::create(['name' => 'edit books', 'group' => 'books']);
        Permission::create(['name' => 'delete books', 'group' => 'books']);
        Permission::create(['name' => 'manage featured', 'group' => 'books']);

        // Permisos de Categorías
        Permission::create(['name' => 'view categories', 'group' => 'categories']);
        Permission::create(['name' => 'create categories', 'group' => 'categories']);
        Permission::create(['name' => 'edit categories', 'group' => 'categories']);
        Permission::create(['name' => 'delete categories', 'group' => 'categories']);

        // Permisos de Préstamos
        Permission::create(['name' => 'view loans', 'group' => 'loans']);
        Permission::create(['name' => 'create loans', 'group' => 'loans']);
        Permission::create(['name' => 'edit loans', 'group' => 'loans']);
        Permission::create(['name' => 'delete loans', 'group' => 'loans']);

        // Permisos de Reservas
        Permission::create(['name' => 'view reservations', 'group' => 'reservations']);
        Permission::create(['name' => 'create reservations', 'group' => 'reservations']);
        Permission::create(['name' => 'edit reservations', 'group' => 'reservations']);
        Permission::create(['name' => 'delete reservations', 'group' => 'reservations']);

        // Permisos de Reportes
        Permission::create(['name' => 'view reports', 'group' => 'reports']);
        Permission::create(['name' => 'export reports', 'group' => 'reports']);

        // Permisos de Configuración
        Permission::create(['name' => 'view settings', 'group' => 'settings']);
        Permission::create(['name' => 'edit settings', 'group' => 'settings']);

        // Crear roles y asignar permisos
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        $librarian = Role::create(['name' => 'librarian']);
        $librarian->givePermissionTo([
            'view books', 'create books', 'edit books',
            'view categories', 'create categories', 'edit categories',
            'view loans', 'create loans', 'edit loans',
            'view reservations', 'create reservations', 'edit reservations',
            'view users', 'edit users',
            'view reports',
        ]);

        $user = Role::create(['name' => 'user']);
        $user->givePermissionTo([
            'view books',
            'view categories',
        ]);
    }
}
