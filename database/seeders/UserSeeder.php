<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;


class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Admin user
        $admin = User::create([
            'name' => 'Admin',
            'last_name' => 'Principal',
            'email' => 'admin@biblioteca.test',
            'institutional_email' => 'admin@institution.edu.pe',
            'password' => Hash::make('password'),
            'is_temp_password' => false,
            'dni' => '00000000',
            'phone' => '999999999',
            'user_type' => 'staff',
            'institutional_id' => 'ADMIN001',
            'membership_expires_at' => null,
            'max_concurrent_loans' => 10,
            'can_download' => true,
            'is_active' => true,
        ]);
        $admin->assignRole('admin');

        // Librarian user
        $librarian = User::create([
            'name' => 'Bibliotecario',
            'last_name' => 'Principal',
            'email' => 'librarian@biblioteca.test',
            'institutional_email' => 'librarian@institution.edu.pe',
            'password' => Hash::make('password'),
            'is_temp_password' => false,
            'dni' => '00000001',
            'phone' => '999999998',
            'user_type' => 'staff',
            'institutional_id' => 'LIBR001',
            'membership_expires_at' => null,
            'max_concurrent_loans' => 8,
            'can_download' => true,
            'is_active' => true,
        ]);
        $librarian->assignRole('librarian');

        // Regular user
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
            'institutional_id' => 'STU001',
            'membership_expires_at' => now()->addYear(),
            'max_concurrent_loans' => 3,
            'can_download' => true,
            'is_active' => true,
        ]);
        $user->assignRole('user');

        // Create multiple test users
        for ($i = 3; $i <= 10; $i++) {
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
                'user_type' => ['student', 'teacher', 'external'][array_rand(['student', 'teacher', 'external'])],
                'institutional_id' => 'USER' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'membership_expires_at' => now()->addYear(),
                'max_concurrent_loans' => 3,
                'can_download' => true,
                'is_active' => true,
                'created_by' => $admin->id,
            ]);
            $testUser->assignRole('user');
        }
    }
}
