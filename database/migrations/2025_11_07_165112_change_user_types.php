<?php
// migrations/2025_11_07_165112_change_user_types.php

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
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', [
                'student',
                'teacher',
                'external',
                'librarian',
                'admin'
            ])->default('student')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->enum('user_type', [
                'student',
                'teacher',
                'external',
                'staff',  // ← volver a incluir staff si necesitas revertir
                'librarian',
                'admin'
            ])->default('student')->change();
        });
    }
};
