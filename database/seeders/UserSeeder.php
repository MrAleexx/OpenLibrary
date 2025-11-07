<?php
// database/seeders/UserSeeder.php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Carbon\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Admin user - CON EMAIL VERIFICADO
        $admin = User::create([
            'name' => 'Admin',
            'last_name' => 'Principal',
            'email' => 'admin@biblioteca.test',
            'institutional_email' => 'admin@institution.edu.pe',
            'password' => Hash::make('password'),
            'is_temp_password' => false,
            'dni' => '00000000',
            'phone' => '999999999',
            'user_type' => 'admin', // ✅ CORREGIDO: 'admin' en lugar de 'staff'
            'institutional_id' => 'ADM' . date('y') . '001', // ✅ FORMATO AUTOMÁTICO
            'membership_expires_at' => null,
            'max_concurrent_loans' => 10,
            'can_download' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $admin->assignRole('admin');

        // Librarian user - CON EMAIL VERIFICADO
        $librarian = User::create([
            'name' => 'Bibliotecario',
            'last_name' => 'Principal',
            'email' => 'librarian@biblioteca.test',
            'institutional_email' => 'librarian@institution.edu.pe',
            'password' => Hash::make('password'),
            'is_temp_password' => false,
            'dni' => '00000001',
            'phone' => '999999998',
            'user_type' => 'librarian', // ✅ CORREGIDO: 'librarian' en lugar de 'staff'
            'institutional_id' => 'BIB' . date('y') . '001', // ✅ FORMATO AUTOMÁTICO
            'membership_expires_at' => null,
            'max_concurrent_loans' => 8,
            'can_download' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $librarian->assignRole('librarian');

        // Regular user - CON EMAIL VERIFICADO
        $user = User::create([
            'name' => 'Usuario',
            'last_name' => 'Ejemplo',
            'email' => 'user@biblioteca.test',
            'institutional_email' => 'user@institution.edu.pe',
            'password' => Hash::make('password'),
            'is_temp_password' => false,
            'dni' => '00000002',
            'phone' => '999999997',
            'user_type' => 'student',
            'institutional_id' => 'EST' . date('y') . '001', // ✅ FORMATO AUTOMÁTICO
            'membership_expires_at' => now()->addYear(),
            'max_concurrent_loans' => 3,
            'can_download' => true,
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $user->assignRole('user');

        // Crear múltiples usuarios de prueba también verificados
        for ($i = 3; $i <= 10; $i++) {
            $userTypes = ['student', 'teacher', 'external'];
            $selectedType = $userTypes[array_rand($userTypes)];

            $testUser = User::create([
                'name' => 'Usuario',
                'last_name' => "Test {$i}",
                'email' => "user{$i}@biblioteca.test",
                'institutional_email' => "user{$i}@institution.edu.pe",
                'password' => Hash::make('password'),
                'is_temp_password' => true,
                'temp_password_expires_at' => now()->addDays(7),
                'dni' => str_pad($i, 8, '0', STR_PAD_LEFT),
                'phone' => '999999' . str_pad(100 - $i, 3, '0', STR_PAD_LEFT),
                'user_type' => $selectedType,
                'institutional_id' => $this->generateInstitutionalId($selectedType, $i), // ✅ FORMATO AUTOMÁTICO
                'membership_expires_at' => now()->addYear(),
                'max_concurrent_loans' => 3,
                'can_download' => true,
                'is_active' => true,
                'created_by' => $admin->id,
                'email_verified_at' => now(),
            ]);
            $testUser->assignRole('user');
        }

        // Crear algunos bibliotecarios y administradores adicionales para testing
        $this->createAdditionalStaffUsers($admin);
    }

    /**
     * Generar ID institucional automático
     */
    private function generateInstitutionalId(string $userType, int $sequence): string
    {
        $prefixes = [
            'admin' => 'ADM',
            'librarian' => 'BIB',
            'student' => 'EST',
            'teacher' => 'DOC',
            'external' => 'EXT',
        ];

        $prefix = $prefixes[$userType] ?? 'USU';
        $year = date('y');
        $sequential = str_pad($sequence, 3, '0', STR_PAD_LEFT);

        return "{$prefix}{$year}{$sequential}";
    }

    /**
     * Crear usuarios staff adicionales para testing
     */
    private function createAdditionalStaffUsers(User $admin): void
    {
        // Bibliotecario adicional
        User::create([
            'name' => 'Ana',
            'last_name' => 'Bibliotecaria',
            'email' => 'ana.librarian@biblioteca.test',
            'institutional_email' => 'ana.librarian@institution.edu.pe',
            'password' => Hash::make('password'),
            'is_temp_password' => false,
            'dni' => '00000011',
            'phone' => '999999989',
            'user_type' => 'librarian',
            'institutional_id' => 'BIB' . date('y') . '002',
            'membership_expires_at' => null,
            'max_concurrent_loans' => 8,
            'can_download' => true,
            'is_active' => true,
            'email_verified_at' => now(),
            'created_by' => $admin->id,
        ])->assignRole('librarian');

        // Administrador adicional
        User::create([
            'name' => 'Carlos',
            'last_name' => 'Administrador',
            'email' => 'carlos.admin@biblioteca.test',
            'institutional_email' => 'carlos.admin@institution.edu.pe',
            'password' => Hash::make('password'),
            'is_temp_password' => false,
            'dni' => '00000012',
            'phone' => '999999988',
            'user_type' => 'admin',
            'institutional_id' => 'ADM' . date('y') . '002',
            'membership_expires_at' => null,
            'max_concurrent_loans' => 10,
            'can_download' => true,
            'is_active' => true,
            'email_verified_at' => now(),
            'created_by' => $admin->id,
        ])->assignRole('admin');
    }
}
