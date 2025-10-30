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
        // Solo agregar contadores cacheados a books (NO eliminar columnas que ya no existen)
        Schema::table('books', function (Blueprint $table) {
            if (!Schema::hasColumn('books', 'physical_copies_count')) {
                $table->integer('physical_copies_count')->default(0)->after('book_type');
            }
            if (!Schema::hasColumn('books', 'available_copies_count')) {
                $table->integer('available_copies_count')->default(0)->after('physical_copies_count');
            }
        });

        // Mejorar la tabla physical_copies
        Schema::table('physical_copies', function (Blueprint $table) {
            if (!Schema::hasColumn('physical_copies', 'location_section')) {
                $table->string('location_section')->nullable()->after('location');
            }
            if (!Schema::hasColumn('physical_copies', 'location_shelf')) {
                $table->string('location_shelf')->nullable()->after('location_section');
            }
            if (!Schema::hasColumn('physical_copies', 'acquisition_date')) {
                $table->date('acquisition_date')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('physical_copies', 'acquisition_cost')) {
                $table->decimal('acquisition_cost', 8, 2)->nullable()->after('acquisition_date');
            }
            if (!Schema::hasColumn('physical_copies', 'condition')) {
                $table->enum('condition', ['excellent', 'good', 'fair', 'poor'])->default('good')->after('acquisition_cost');
            }
            if (!Schema::hasColumn('physical_copies', 'condition_notes')) {
                $table->text('condition_notes')->nullable()->after('condition');
            }
        });
    }


    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revertir books
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn(['physical_copies_count', 'available_copies_count']);
        });

        // Revertir physical_copies
        Schema::table('physical_copies', function (Blueprint $table) {
            $table->dropColumn([
                'location_section',
                'location_shelf',
                'acquisition_date',
                'acquisition_cost',
                'condition',
                'condition_notes'
            ]);
        });
    }
};
