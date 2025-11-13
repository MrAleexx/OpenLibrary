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
        Schema::table('book_loans', function (Blueprint $table) {
            // Modificar la columna status para incluir los nuevos estados
            // Nota: En MySQL, ALTER COLUMN no soporta ENUM directamente,
            // así que necesitamos usar DB::statement
        });
        
        // Actualizar el ENUM para incluir los nuevos estados
        DB::statement("ALTER TABLE book_loans MODIFY COLUMN status ENUM('pending_pickup', 'ready_for_pickup', 'active', 'overdue', 'returned', 'lost') DEFAULT 'pending_pickup'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir al ENUM original
        DB::statement("ALTER TABLE book_loans MODIFY COLUMN status ENUM('active', 'overdue', 'returned', 'lost') DEFAULT 'active'");
    }
};
