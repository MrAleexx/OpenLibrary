<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Solo ejecutar para MySQL (SQLite usa CHECK constraints en lugar de ENUM)
        if (DB::getDriverName() !== 'sqlite') {
            // MySQL no permite modificar ENUMs directamente con ALTER
            // Necesitamos usar SQL raw para modificar la columna
            DB::statement("ALTER TABLE book_reservations MODIFY COLUMN status ENUM('pending', 'ready', 'collected', 'cancelled', 'expired') NOT NULL DEFAULT 'pending'");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Solo ejecutar para MySQL
        if (DB::getDriverName() !== 'sqlite') {
            // Revertir a los valores antiguos
            DB::statement("ALTER TABLE book_reservations MODIFY COLUMN status ENUM('pending', 'ready_for_pickup', 'picked_up', 'cancelled', 'expired') NOT NULL DEFAULT 'pending'");
        }
    }
};
