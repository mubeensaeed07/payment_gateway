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
        Schema::table('slabs', function (Blueprint $table) {
            $table->decimal('onelink_fee', 15, 2)->default(0)->after('charge')->comment('Fixed 1Link fee for this slab');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('slabs', function (Blueprint $table) {
            $table->dropColumn('onelink_fee');
        });
    }
};
