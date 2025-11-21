<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Primero eliminar índices que usan estas columnas (necesario para SQLite)
            $table->dropIndex('books_access_level_is_active_index');
            $table->dropIndex('books_copyright_status_downloadable_index');
            
            // Luego eliminar columnas relacionadas con e-commerce/librería comercial
            $table->dropColumn([
                'access_level',
                'copyright_status',
                'license_type',
                'acquisition_type',
                'acquisition_cost',
                'source'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            // Restaurar columnas en caso de rollback
            $table->enum('access_level', ['free', 'premium', 'institutional'])->default('free');
            $table->enum('copyright_status', ['copyrighted', 'public_domain', 'creative_commons'])->default('copyrighted');
            $table->string('license_type')->nullable();
            $table->enum('acquisition_type', ['purchase', 'donation', 'exchange'])->default('purchase');
            $table->decimal('acquisition_cost', 8, 2)->nullable();
            $table->string('source')->nullable();
            
            // Restaurar índices
            $table->index(['access_level', 'is_active']);
            $table->index(['copyright_status', 'downloadable']);
        });
    }
};
