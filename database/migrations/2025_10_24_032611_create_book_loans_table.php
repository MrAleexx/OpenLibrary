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
        Schema::create('book_loans', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('physical_copy_id');
            $table->unsignedBigInteger('reservation_id')->nullable();
            $table->datetime('loan_date');
            $table->datetime('due_date');
            $table->datetime('actual_return_date')->nullable();
            $table->integer('renewal_count')->default(0);
            $table->enum('status', ['active', 'returned', 'overdue'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('physical_copy_id')->references('id')->on('physical_copies')->onDelete('cascade');
            $table->foreign('reservation_id')->references('id')->on('book_reservations')->onDelete('set null');
            
            $table->index(['status']);
            $table->index(['due_date']);
            $table->index(['user_id', 'status']);
            $table->index(['physical_copy_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('book_loans');
    }
};
