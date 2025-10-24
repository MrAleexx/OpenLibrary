<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class LibrarySettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
       $settings = [
            // Configuración general
            ['key' => 'library_name', 'value' => 'Biblioteca Digital', 'type' => 'string', 'description' => 'Nombre de la biblioteca', 'is_public' => true],
            ['key' => 'library_description', 'value' => 'Sistema de gestión bibliotecaria digital', 'type' => 'string', 'description' => 'Descripción de la biblioteca', 'is_public' => true],
            ['key' => 'max_daily_downloads', 'value' => '5', 'type' => 'integer', 'description' => 'Máximo de descargas diarias por usuario', 'is_public' => true],
            ['key' => 'loan_duration_days', 'value' => '15', 'type' => 'integer', 'description' => 'Duración de préstamos en días', 'is_public' => true],
            ['key' => 'max_renewals', 'value' => '2', 'type' => 'integer', 'description' => 'Máximo de renovaciones por préstamo', 'is_public' => true],
            ['key' => 'reservation_expiry_hours', 'value' => '48', 'type' => 'integer', 'description' => 'Horas para expirar reservas no recogidas', 'is_public' => true],
            
            // Configuración de correo
            ['key' => 'contact_email', 'value' => 'contacto@biblioteca.test', 'type' => 'string', 'description' => 'Email de contacto', 'is_public' => true],
            ['key' => 'admin_email', 'value' => 'admin@biblioteca.test', 'type' => 'string', 'description' => 'Email del administrador', 'is_public' => false],
            
            // Límites
            ['key' => 'max_concurrent_loans_student', 'value' => '3', 'type' => 'integer', 'description' => 'Máximo de préstamos concurrentes para estudiantes', 'is_public' => true],
            ['key' => 'max_concurrent_loans_teacher', 'value' => '5', 'type' => 'integer', 'description' => 'Máximo de préstamos concurrentes para profesores', 'is_public' => true],
            ['key' => 'max_concurrent_loans_staff', 'value' => '8', 'type' => 'integer', 'description' => 'Máximo de préstamos concurrentes para staff', 'is_public' => true],
            
            // Configuración de archivos
            ['key' => 'max_file_size_mb', 'value' => '50', 'type' => 'integer', 'description' => 'Tamaño máximo de archivos en MB', 'is_public' => true],
            ['key' => 'allowed_file_types', 'value' => 'pdf,epub', 'type' => 'string', 'description' => 'Tipos de archivos permitidos', 'is_public' => true],
            
            // Configuración de mantenimiento
            ['key' => 'maintenance_mode', 'value' => 'false', 'type' => 'boolean', 'description' => 'Modo mantenimiento', 'is_public' => true],
        ];

        DB::table('library_settings')->insert($settings);
    }
}
