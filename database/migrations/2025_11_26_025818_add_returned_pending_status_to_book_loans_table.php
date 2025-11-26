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
            // Modificar la columna status para incluir 'returned_pending'
            // Nota: En MySQL, modificar un ENUM requiere redefinir todas las opciones
            DB::statement("ALTER TABLE book_loans MODIFY COLUMN status ENUM('pending_pickup', 'ready_for_pickup', 'active', 'overdue', 'returned', 'returned_pending', 'cancelled') NOT NULL DEFAULT 'active'");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_loans', function (Blueprint $table) {
            // Revertir a los estados anteriores (sin 'returned_pending')
            // Nota: Si hay registros con 'returned_pending', esto podría fallar o convertir a string vacío dependiendo de la configuración SQL mode
            // Para seguridad, podríamos mover 'returned_pending' a 'active' u 'overdue' antes de revertir, pero para este ejemplo simple solo revertimos la definición.
            DB::statement("ALTER TABLE book_loans MODIFY COLUMN status ENUM('pending_pickup', 'ready_for_pickup', 'active', 'overdue', 'returned', 'cancelled') NOT NULL DEFAULT 'active'");
        });
    }
};
