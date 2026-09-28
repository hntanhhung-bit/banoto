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
        // 1. Thêm cột delivery_type vào orders nếu chưa có
        Schema::table('orders', function (Blueprint $table) {
            if (!Schema::hasColumn('orders', 'delivery_type')) {
                $table->string('delivery_type')->default('showroom')->after('ghn_total_fee'); // 'showroom', 'garage_delivery'
            }
        });

        // 2. Tạo bảng payment_transactions theo tài liệu hướng dẫn
        if (!Schema::hasTable('payment_transactions')) {
            Schema::create('payment_transactions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('order_id')->constrained()->cascadeOnDelete();
                $table->string('gateway'); // momo, cod, bank_transfer
                $table->string('gateway_order_id')->nullable()->index();
                $table->string('transaction_id')->nullable()->index();
                $table->decimal('amount', 15, 2);
                $table->string('status')->default('pending'); // pending, initiated, paid, failed, canceled
                $table->integer('result_code')->nullable();
                $table->string('message')->nullable();
                $table->json('request_payload')->nullable();
                $table->json('response_payload')->nullable();
                $table->timestamp('paid_at')->nullable();
                $table->timestamps();

                $table->unique(['gateway', 'gateway_order_id']);
                $table->index(['order_id', 'status']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');

        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'delivery_type')) {
                $table->dropColumn('delivery_type');
            }
        });
    }
};
