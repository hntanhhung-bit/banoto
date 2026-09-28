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
        Schema::table('rentals', function (Blueprint $table) {
            if (!Schema::hasColumn('rentals', 'handover_code')) {
                $table->string('handover_code', 20)->nullable()->after('rental_code');
                $table->string('handover_status', 30)->default('pending')->after('handover_code'); // pending, verified, returned
                $table->timestamp('handover_verified_at')->nullable()->after('handover_status');
                $table->string('handover_verified_by')->nullable()->after('handover_verified_at');
                $table->integer('handover_odo')->nullable()->after('handover_verified_by');
                $table->integer('handover_fuel')->nullable()->after('handover_odo'); // % xăng
                $table->text('handover_notes')->nullable()->after('handover_fuel');
                $table->integer('return_odo')->nullable()->after('handover_notes');
                $table->integer('return_fuel')->nullable()->after('return_odo');
                $table->timestamp('return_verified_at')->nullable()->after('return_fuel');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('rentals', function (Blueprint $table) {
            $table->dropColumn([
                'handover_code',
                'handover_status',
                'handover_verified_at',
                'handover_verified_by',
                'handover_odo',
                'handover_fuel',
                'handover_notes',
                'return_odo',
                'return_fuel',
                'return_verified_at',
            ]);
        });
    }
};
