<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            
            // 1. TẠO KHÓA NGOẠI LIÊN KẾT VỚI BẢNG CATEGORIES
            $table->foreignId('category_id')->constrained('categories')->cascadeOnDelete();
            
            // 2. CÁC THÔNG TIN CỦA Ô TÔ
            $table->string('name'); // Tên xe (VD: Ford Ranger, Toyota Camry)
            $table->decimal('price', 15, 2); // Giá xe (để số lớn vì ô tô nhiều tiền)
            $table->string('color')->nullable(); // Màu xe
            $table->text('description')->nullable(); // Mô tả chi tiết xe
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};