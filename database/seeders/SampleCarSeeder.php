<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductColor;

class SampleCarSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Mercedes-Benz',
            'Toyota',
            'Hyundai',
            'Mazda',
            'Honda',
        ];

        $catModels = [];
        foreach ($categories as $catName) {
            $catModels[$catName] = Category::firstOrCreate(['name' => $catName]);
        }

        $cars = [
            [
                'category' => 'Hyundai',
                'name' => 'Hyundai Accent 2024 Bản Đặc Biệt',
                'price' => 540000000,
                'rent_price_per_day' => 800000,
                'driver_price_per_day' => 500000,
                'rental_deposit' => 5000000,
                'color' => 'Trắng ngọc trai',
                'description' => 'Xe đời 2024 mới 100%, nội thất da cao cấp, cửa sổ trời, phanh đĩa 4 bánh, camera lùi full HD.',
                'quantity' => 10,
                'rental_status' => 'available',
            ],
            [
                'category' => 'Mercedes-Benz',
                'name' => 'Mercedes-Benz C300 AMG 2024',
                'price' => 2099000000,
                'rent_price_per_day' => 2500000,
                'driver_price_per_day' => 800000,
                'rental_deposit' => 20000000,
                'color' => 'Đen ánh kim',
                'description' => 'Sedan hạng sang cao cấp, động cơ 2.0L tăng áp Mild-Hybrid 258hp, gói ngoại thất thể thao AMG line.',
                'quantity' => 5,
                'rental_status' => 'available',
            ],
            [
                'category' => 'Mazda',
                'name' => 'Mazda CX-5 2.0L Premium 2024',
                'price' => 829000000,
                'rent_price_per_day' => 1200000,
                'driver_price_per_day' => 600000,
                'rental_deposit' => 8000000,
                'color' => 'Đỏ thể thao',
                'description' => 'Crossover 5 chỗ đẳng cấp, gói an toàn cao cấp i-Activsense, loa Bose 10 loa, màn hình HUD kính lái.',
                'quantity' => 8,
                'rental_status' => 'available',
            ],
            [
                'category' => 'Toyota',
                'name' => 'Toyota Camry 2.5Q 2024',
                'price' => 1405000000,
                'rent_price_per_day' => 1800000,
                'driver_price_per_day' => 700000,
                'rental_deposit' => 15000000,
                'color' => 'Đen ánh kim',
                'description' => 'Sedan doanh nhân lịch lãm, ghế chỉnh điện hàng 2, gói Toyota Safety Sense, cửa sổ trời toàn cảnh.',
                'quantity' => 6,
                'rental_status' => 'available',
            ]
        ];

        foreach ($cars as $carData) {
            $cat = $catModels[$carData['category']] ?? null;
            $product = Product::firstOrCreate(
                ['name' => $carData['name']],
                [
                    'category_id' => $cat ? $cat->id : 1,
                    'price' => $carData['price'],
                    'rent_price_per_day' => $carData['rent_price_per_day'],
                    'driver_price_per_day' => $carData['driver_price_per_day'],
                    'rental_deposit' => $carData['rental_deposit'],
                    'color' => $carData['color'],
                    'description' => $carData['description'],
                    'quantity' => $carData['quantity'],
                    'rental_status' => $carData['rental_status'],
                ]
            );

            if ($product->colors()->count() == 0) {
                // 3 Popular Color Variants with price differentiation
                ProductColor::create([
                    'product_id' => $product->id,
                    'color_name' => 'Trắng ngọc trai',
                    'color_hex' => '#FFFFFF',
                    'extra_rent_price' => 50000,
                    'rent_price_per_day' => $product->rent_price_per_day + 50000,
                    'extra_sale_price' => 10000000,
                    'quantity' => 4,
                    'is_default' => false,
                ]);

                ProductColor::create([
                    'product_id' => $product->id,
                    'color_name' => 'Đen ánh kim',
                    'color_hex' => '#111111',
                    'extra_rent_price' => 0,
                    'rent_price_per_day' => $product->rent_price_per_day,
                    'extra_sale_price' => 0,
                    'quantity' => 5,
                    'is_default' => true,
                ]);

                ProductColor::create([
                    'product_id' => $product->id,
                    'color_name' => 'Đỏ thể thao',
                    'color_hex' => '#D0021B',
                    'extra_rent_price' => 100000,
                    'rent_price_per_day' => $product->rent_price_per_day + 100000,
                    'extra_sale_price' => 15000000,
                    'quantity' => 3,
                    'is_default' => false,
                ]);
            }
        }
    }
}
