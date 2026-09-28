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
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (!Schema::hasColumn('products', 'driver_price_per_day')) {
                    $table->decimal('driver_price_per_day', 15, 2)->default(500000)->after('rent_price_per_day');
                }
            });
        }

        if (Schema::hasTable('rentals')) {
            Schema::table('rentals', function (Blueprint $table) {
                if (!Schema::hasColumn('rentals', 'rental_type')) {
                    $table->string('rental_type')->default('self_drive')->after('rental_code'); // self_drive, with_driver
                }
                if (!Schema::hasColumn('rentals', 'driver_fee_per_day')) {
                    $table->decimal('driver_fee_per_day', 15, 2)->default(0)->after('daily_price');
                }
                if (!Schema::hasColumn('rentals', 'total_driver_fee')) {
                    $table->decimal('total_driver_fee', 15, 2)->default(0)->after('total_rental_fee');
                }
                if (!Schema::hasColumn('rentals', 'destination_address')) {
                    $table->text('destination_address')->nullable()->after('customer_address');
                }
                if (!Schema::hasColumn('rentals', 'pickup_lat')) {
                    $table->string('pickup_lat')->nullable()->after('pickup_location');
                }
                if (!Schema::hasColumn('rentals', 'pickup_lng')) {
                    $table->string('pickup_lng')->nullable()->after('pickup_lat');
                }
                if (!Schema::hasColumn('rentals', 'driver_name')) {
                    $table->string('driver_name')->nullable()->after('admin_note');
                }
                if (!Schema::hasColumn('rentals', 'driver_phone')) {
                    $table->string('driver_phone')->nullable()->after('driver_name');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if (Schema::hasColumn('products', 'driver_price_per_day')) {
                    $table->dropColumn('driver_price_per_day');
                }
            });
        }

        if (Schema::hasTable('rentals')) {
            Schema::table('rentals', function (Blueprint $table) {
                $cols = ['rental_type', 'driver_fee_per_day', 'total_driver_fee', 'destination_address', 'pickup_lat', 'pickup_lng', 'driver_name', 'driver_phone'];
                foreach ($cols as $col) {
                    if (Schema::hasColumn('rentals', $col)) {
                        $table->dropColumn($col);
                    }
                }
            });
        }
    }
};
