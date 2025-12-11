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
        Schema::table('customers', function (Blueprint $table) {
            // Drop the existing unique constraint on user_number
            $table->dropUnique(['user_number']);
            
            // Add composite unique constraint on admin_id + user_number
            // This ensures customer numbers are unique per admin, but different admins can have the same customer number
            $table->unique(['admin_id', 'user_number'], 'customers_admin_user_number_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('customers_admin_user_number_unique');
            
            // Restore the global unique constraint on user_number
            $table->unique('user_number');
        });
    }
};

