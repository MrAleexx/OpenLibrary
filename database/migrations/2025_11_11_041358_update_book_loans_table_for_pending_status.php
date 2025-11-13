<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Actualiza la tabla book_loans para soportar estados intermedios:
     * - Permite NULL en loan_date y due_date (se asignan cuando el bibliotecario activa)
     * - Agrega estados pending_pickup y ready_for_pickup al enum de status
     */
    public function up(): void
    {
        Schema::table('book_loans', function (Blueprint $table) {
            // Permitir NULL en loan_date y due_date
            $table->datetime('loan_date')->nullable()->change();
            $table->datetime('due_date')->nullable()->change();
            
            // Actualizar enum de status con nuevos estados
            $table->enum('status', [
                'pending_pickup',      // Usuario hizo checkout, esperando preparación
                'ready_for_pickup',    // Bibliotecario preparó el libro
                'active',              // Libro entregado al usuario
                'overdue',             // Préstamo vencido
                'returned',            // Libro devuelto
                'cancelled'            // Préstamo cancelado/rechazado por admin
            ])->default('pending_pickup')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_loans', function (Blueprint $table) {
            // Revertir a NOT NULL (solo si no hay registros con NULL)
            $table->datetime('loan_date')->nullable(false)->change();
            $table->datetime('due_date')->nullable(false)->change();
            
            // Revertir enum a estados originales
            $table->enum('status', ['active', 'overdue', 'returned'])
                ->default('active')
                ->change();
        });
    }
};
