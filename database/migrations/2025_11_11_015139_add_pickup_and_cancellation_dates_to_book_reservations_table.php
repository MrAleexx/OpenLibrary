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
        Schema::table('book_reservations', function (Blueprint $table) {
            $table->timestamp('pickup_date')->nullable()->after('pickup_deadline');
            $table->timestamp('cancellation_date')->nullable()->after('pickup_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('book_reservations', function (Blueprint $table) {
            $table->dropColumn(['pickup_date', 'cancellation_date']);
        });
    }
};
