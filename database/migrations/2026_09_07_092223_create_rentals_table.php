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
        Schema::create('rentals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('rental_code')->unique();
            $table->string('customer_name');
            $table->string('customer_phone');
            $table->string('customer_email')->nullable();
            $table->text('customer_address')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->integer('total_days')->default(1);
            $table->decimal('daily_price', 15, 2);
            $table->decimal('total_rental_fee', 15, 2);
            $table->decimal('deposit_amount', 15, 2);
            $table->decimal('total_amount', 15, 2);
            $table->string('pickup_location')->default('at_showroom'); // at_showroom, delivery_home
            $table->string('payment_method')->default('bank_transfer'); // bank_transfer, cod
            $table->string('payment_status')->default('unpaid'); // unpaid, deposit_paid, fully_paid
            $table->string('rental_status')->default('pending'); // pending, confirmed, in_progress, returned, cancelled
            $table->text('note')->nullable();
            $table->text('admin_note')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
