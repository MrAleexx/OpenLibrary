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
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);
        Permission::create(['name' => 'import users']);

        // Permisos de Libros
        Permission::create(['name' => 'view books']);
        Permission::create(['name' => 'create books']);
        Permission::create(['name' => 'edit books']);
        Permission::create(['name' => 'delete books']);
        Permission::create(['name' => 'manage featured']);

        // Permisos de Categorías
        Permission::create(['name' => 'view categories']);
        Permission::create(['name' => 'create categories']);
        Permission::create(['name' => 'edit categories']);
        Permission::create(['name' => 'delete categories']);

        // Permisos de Préstamos
        Permission::create(['name' => 'view loans']);
        Permission::create(['name' => 'create loans']);
        Permission::create(['name' => 'edit loans']);
        Permission::create(['name' => 'delete loans']);

        // Permisos de Reservas
        Permission::create(['name' => 'view reservations']);
        Permission::create(['name' => 'create reservations']);
        Permission::create(['name' => 'edit reservations']);
        Permission::create(['name' => 'delete reservations']);

        // Permisos de Reportes
        Permission::create(['name' => 'view reports']);
        Permission::create(['name' => 'export reports']);

        // Permisos de Configuración
        Permission::create(['name' => 'view settings']);
        Permission::create(['name' => 'edit settings']);

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
