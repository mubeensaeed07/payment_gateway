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
        Schema::table('invoices', function (Blueprint $table) {
            $table->date('due_date')->nullable()->after('amount');
            $table->date('expiry_date')->nullable()->after('due_date');
            $table->decimal('amount_after_due_date', 10, 2)->nullable()->after('expiry_date');
            $table->text('description')->nullable()->after('amount_after_due_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['due_date', 'expiry_date', 'amount_after_due_date', 'description']);
        });
    }
};
