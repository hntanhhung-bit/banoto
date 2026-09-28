<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $products = [
            ['Toyota Camry 2.5Q 2024', 'Toyota', 1405000000, 10, 1800000, 600000, 10000000, 'Đen ánh kim', 'Sedan hạng D cao cấp nhập khẩu Thái Lan, động cơ 2.5L Dynamic Force 207 mã lực.'],
            ['Toyota Corolla Cross 1.8V 2024', 'Toyota', 860000000, 15, 1100000, 500000, 7000000, 'Trắng ngọc trai', 'SUV đô thị 5 chỗ hiện đại, gói an toàn Toyota Safety Sense.'],
            ['Mazda CX-5 2.0L Premium 2024', 'Mazda', 829000000, 12, 1200000, 600000, 8000000, 'Đỏ pha lê Soul Red', 'Crossover 5 chỗ đẳng cấp, gói an toàn i-Activsense, hệ thống 10 loa Bose.'],
            ['Mazda 3 1.5L Luxury 2024', 'Mazda', 619000000, 10, 900000, 500000, 6000000, 'Xám ánh kim Machine Grey', 'Thiết kế KODO quyến rũ, màn hình giải trí 8.8 inch, 7 túi khí an toàn.'],
            ['Hyundai Accent 2024 Bản Đặc Biệt', 'Hyundai', 540000000, 20, 800000, 500000, 5000000, 'Trắng ngọc trai', 'Sedan cỡ B rộng rãi nhất phân khúc, cửa sổ trời, sạc không dây.'],
            ['Hyundai SantaFe 2.5 Xăng Cao Cấp', 'Hyundai', 1269000000, 8, 1700000, 700000, 12000000, 'Đen Obsidian', 'SUV 7 chỗ rộng rãi, động cơ Smartstream 2.5L thế hệ mới.'],
            ['Honda CR-V L 2024', 'Honda', 1159000000, 8, 1600000, 600000, 10000000, 'Xanh đậm Canyon River Blue', 'SUV 7 chỗ linh hoạt, động cơ 1.5L VTEC Turbo, gói Honda SENSING.'],
            ['Honda City RS 2024', 'Honda', 609000000, 15, 850000, 500000, 5000000, 'Đỏ cá tính', 'Sedan phong cách thể thao RS, ghế bọc da pha da lộn cao cấp.'],
            ['Mercedes-Benz C300 AMG 2024', 'Mercedes-Benz', 2099000000, 5, 2500000, 800000, 20000000, 'Đen ánh kim', 'Sedan hạng sang, động cơ 2.0L tăng áp Mild-Hybrid 258hp, gói thể thao AMG.'],
            ['Mercedes-Benz GLC 300 4MATIC 2024', 'Mercedes-Benz', 2799000000, 4, 3000000, 1000000, 25000000, 'Trắng Polar', 'SUV hạng sang bán chạy nhất, dẫn động 4 bánh toàn thời gian 4MATIC.'],
        ];

        foreach ($products as [$name, $categoryName, $price, $quantity, $rentPrice, $driverPrice, $deposit, $color, $desc]) {
            $category = Category::firstOrCreate(['name' => $categoryName]);
            Product::firstOrCreate(
                ['name' => $name, 'category_id' => $category->id],
                [
                    'price' => $price,
                    'quantity' => $quantity,
                    'is_for_rent' => 1,
                    'rent_price_per_day' => $rentPrice,
                    'driver_price_per_day' => $driverPrice,
                    'rental_deposit' => $deposit,
                    'color' => $color,
                    'description' => $desc,
                    'rental_status' => 'available',
                    'approval_status' => 'approved',
                ]
            );
        }
    }
}
