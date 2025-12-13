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
        Schema::create('slabs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->integer('slab_number')->comment('Slab sequence number (1-6)');
            $table->decimal('from_amount', 15, 2)->default(0);
            $table->decimal('to_amount', 15, 2)->nullable()->comment('Null for last slab (unlimited)');
            $table->decimal('charge', 15, 2)->default(0);
            $table->timestamps();
            
            // Ensure unique slab number per admin
            $table->unique(['admin_id', 'slab_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('slabs');
    }
};
