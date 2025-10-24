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
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('institutional_email')->nullable()->unique();
            $table->string('microsoft_id')->nullable()->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->boolean('is_temp_password')->default(true);
            $table->timestamp('temp_password_expires_at')->nullable();
            $table->rememberToken();
            
            // Campos para biblioteca
            $table->string('dni', 8)->unique();
            $table->string('phone', 9);
            $table->enum('user_type', ['student', 'teacher', 'external', 'staff'])->default('student');
            $table->string('institutional_id')->nullable();
            $table->date('membership_expires_at')->nullable();
            $table->integer('max_concurrent_loans')->default(3);
            $table->boolean('can_download')->default(true);
            
            // Contadores de descargas
            $table->integer('downloads_today')->default(0);
            $table->date('last_download_reset')->nullable();
            
            // Auditoría
            $table->unsignedBigInteger('created_by')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_login_at')->nullable();
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->index(['is_active', 'user_type']);
            $table->index(['email_verified_at', 'is_active']);
        });

        // Agregar las columnas de Fortify si no existen
        if (!Schema::hasColumn('users', 'two_factor_secret')) {
            Schema::table('users', function (Blueprint $table) {
                $table->text('two_factor_secret')->after('password')->nullable();
                $table->text('two_factor_recovery_codes')->after('two_factor_secret')->nullable();
                $table->timestamp('two_factor_confirmed_at')->after('two_factor_recovery_codes')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
