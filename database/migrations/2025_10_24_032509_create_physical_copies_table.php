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
        Schema::create('physical_copies', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('book_id');
            $table->string('barcode')->unique();
            $table->integer('copy_number')->default(1);
            $table->enum('status', ['available', 'reserved', 'loaned', 'maintenance'])->default('available');
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('book_id')->references('id')->on('books')->onDelete('cascade');
            $table->index(['status']);
            $table->index(['book_id', 'status']);
            $table->index(['barcode']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('physical_copies');
    }
};
