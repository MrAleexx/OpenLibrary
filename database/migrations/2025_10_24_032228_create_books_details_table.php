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
        Schema::create('book_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->text('description')->nullable();
            $table->string('edition')->default('1ra');
            $table->string('file_format')->default('PDF');
            $table->string('file_size')->nullable();
            $table->string('reading_age')->nullable();
            $table->string('deposito_legal')->nullable();
            $table->text('restrictions')->nullable();
            $table->text('notes')->nullable();
            
            // RF-035: Información extendida
            $table->string('original_language_code', 5)->nullable();
            $table->string('translation_language_code', 5)->nullable();
            $table->string('translator_name')->nullable();
            $table->string('edition_number')->nullable();
            $table->string('volume')->nullable();
            $table->string('series')->nullable();
            $table->string('physical_location')->nullable();
            $table->text('keywords')->nullable();
            $table->date('acquisition_date')->nullable();
            $table->string('supplier')->nullable();
            
            $table->timestamps();

            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->foreign('original_language_code')->references('code')->on('languages')->onDelete('set null');
            $table->foreign('translation_language_code')->references('code')->on('languages')->onDelete('set null');
            
            $table->unique(['book_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books_details');
    }
};
