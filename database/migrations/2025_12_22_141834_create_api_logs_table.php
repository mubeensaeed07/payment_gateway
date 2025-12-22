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
        Schema::create('api_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('admin_id');
            $table->string('api_type'); // 'inquiry' or 'payment'
            $table->string('invoice_number')->nullable(); // Full invoice number or customer number
            $table->string('customer_name')->nullable();
            $table->string('customer_number')->nullable();
            $table->text('request_data'); // Full request payload (JSON)
            $table->text('response_data')->nullable(); // Full response payload (JSON)
            $table->integer('response_status')->nullable(); // HTTP status code
            $table->boolean('is_successful')->default(false); // Whether the request was successful
            $table->text('error_message')->nullable(); // Error message if failed
            $table->timestamps();

            $table->foreign('admin_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['admin_id', 'created_at']);
            $table->index('invoice_number');
            $table->index('customer_number');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('api_logs');
    }
};
