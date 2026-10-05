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
        Schema::table('appointments', function (Blueprint $table) {
            if (!Schema::hasColumn('appointments', 'commission_proof')) {
                $table->string('commission_proof')->nullable()->after('commission_status');
            }
            if (!Schema::hasColumn('appointments', 'commission_paid_at')) {
                $table->timestamp('commission_paid_at')->nullable()->after('commission_proof');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            if (Schema::hasColumn('appointments', 'commission_proof')) {
                $table->dropColumn(['commission_proof', 'commission_paid_at']);
            }
        });
    }
};
