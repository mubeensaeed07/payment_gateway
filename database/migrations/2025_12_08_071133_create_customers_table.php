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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('email');
            $table->string('reference_id'); // Admin-provided reference ID
            $table->string('user_number')->unique(); // Auto-generated unique number starting from 1000
            $table->decimal('balance', 10, 2)->default(0);
            $table->timestamps();
            
            // Indexes for better performance
            $table->index('admin_id');
            $table->index('user_number');
            $table->index('email');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
