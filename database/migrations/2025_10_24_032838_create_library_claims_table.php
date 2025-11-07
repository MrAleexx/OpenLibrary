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
        Schema::create('library_claims', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->enum('document_type', ['DNI', 'CE', 'PASSPORT'])->default('DNI');
            $table->string('document_number');
            $table->string('email');
            $table->string('address')->nullable();
            $table->enum('claim_type', ['complaint', 'claim', 'suggestion'])->default('complaint');
            $table->string('subject');
            $table->text('description');
            $table->text('claimant_request');
            $table->enum('status', ['received', 'in_process', 'resolved', 'closed'])->default('received');
            $table->text('admin_response')->nullable();
            $table->timestamp('response_date')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
            $table->index(['claim_type', 'status']);
            $table->index(['document_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('library_claims');
    }
};
