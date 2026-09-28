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
        Schema::table('payment_transactions', function (Blueprint $table) {
            $table->foreignId('order_id')->nullable()->change();
            if (!Schema::hasColumn('payment_transactions', 'rental_id')) {
                $table->foreignId('rental_id')->nullable()->after('order_id')->constrained('rentals')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_transactions', function (Blueprint $table) {
            if (Schema::hasColumn('payment_transactions', 'rental_id')) {
                $table->dropForeign(['rental_id']);
                $table->dropColumn('rental_id');
            }
            $table->foreignId('order_id')->nullable(false)->change();
        });
    }
};
