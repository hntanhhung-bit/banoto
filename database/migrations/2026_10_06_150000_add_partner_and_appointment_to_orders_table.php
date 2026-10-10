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
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'partner_id')) {
                $table->foreignId('partner_id')->nullable()->after('user_id')->constrained('users')->nullOnDelete();
            }
            if (!Schema::hasColumn('orders', 'appointment_id')) {
                $table->foreignId('appointment_id')->nullable()->after('partner_id')->constrained('appointments')->nullOnDelete();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'appointment_id')) {
                $table->dropForeign(['appointment_id']);
                $table->dropColumn('appointment_id');
            }
            if (Schema::hasColumn('orders', 'partner_id')) {
                $table->dropForeign(['partner_id']);
                $table->dropColumn('partner_id');
            }
        });
    }
};
