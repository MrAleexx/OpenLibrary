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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->unsignedBigInteger('publisher_id')->nullable();
            $table->string('isbn', 20)->unique();
            $table->string('language_code', 5)->default('es');
            $table->integer('pages');
            $table->year('publication_year')->nullable();
            $table->string('cover_image');
            $table->string('pdf_file')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('downloadable')->default(true);
            $table->enum('book_type', ['digital', 'physical', 'both'])->default('digital');
            
            // Contadores
            $table->integer('total_downloads')->default(0);
            $table->integer('total_physical_copies')->default(0);
            $table->integer('available_physical_copies')->default(0);
            $table->integer('total_loans')->default(0);
            $table->integer('total_views')->default(0);
            
            // Características
            $table->boolean('featured')->default(false);
            $table->enum('access_level', ['free', 'premium', 'institutional'])->default('free');
            $table->enum('copyright_status', ['copyrighted', 'public_domain', 'creative_commons'])->default('copyrighted');
            $table->string('license_type')->nullable();
            
            // Búsqueda y metadatos
            $table->text('search_metadata')->nullable();
            $table->enum('acquisition_type', ['purchase', 'donation', 'exchange'])->default('purchase');
            $table->decimal('acquisition_cost', 8, 2)->nullable();
            $table->string('source')->nullable();
            
            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            $table->foreign('publisher_id')->references('id')->on('publishers')->onDelete('set null');
            $table->foreign('language_code')->references('code')->on('languages');
            
            // Índices para optimización
            $table->index(['is_active', 'featured', 'book_type']);
            $table->index(['access_level', 'is_active']);
            $table->index(['copyright_status', 'downloadable']);
            $table->fullText(['title', 'search_metadata']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
