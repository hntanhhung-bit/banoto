<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        foreach (['Toyota', 'Mazda', 'Hyundai', 'Honda', 'Mercedes-Benz', 'Kia', 'Ford', 'BMW'] as $name) {
            Category::firstOrCreate(['name' => $name]);
        }
    }
}
