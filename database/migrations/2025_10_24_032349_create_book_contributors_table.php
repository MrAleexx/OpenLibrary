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
        Schema::create('book_contributors', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->enum('contributor_type', ['author', 'editor', 'translator', 'illustrator', 'prologuist'])->default('author');
            $table->string('full_name');
            $table->string('email')->nullable();
            $table->integer('sequence_number')->default(1);
            $table->text('biographical_note')->nullable();
            $table->timestamps();

            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->index(['book_id', 'sequence_number']);
            $table->index(['contributor_type', 'full_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_contributors');
    }
};
