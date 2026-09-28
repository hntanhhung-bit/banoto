<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')
            ->where(function($q) {
                $q->whereNull('partner_id')
                  ->orWhere('approval_status', 'approved');
            });

        // 1. Tìm kiếm theo từ khóa (Tên xe hoặc Mô tả)
        if ($request->filled('keyword')) {
            $keyword = trim($request->input('keyword'));
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        // 2. Lọc theo Phân loại / Hãng xe (Category)
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // 3. Lọc theo Dịch vụ trọng tâm (Service)
        if ($request->filled('service')) {
            $service = $request->input('service');
            if ($service === 'rent_self' || $service === 'rent') {
                $query->where('is_for_rent', true);
            } elseif ($service === 'rent_driver') {
                $query->where('is_for_rent', true);
            }
        }

        // 4. Lọc theo Khoảng giá thuê hoặc giá xe tùy chỉnh
        if ($request->filled('price_min')) {
            $query->where(function($q) use ($request) {
                $q->where('rent_price_per_day', '>=', (float) $request->input('price_min'))
                  ->orWhere('price', '>=', (float) $request->input('price_min'));
            });
        }
        if ($request->filled('price_max')) {
            $query->where(function($q) use ($request) {
                $q->where('rent_price_per_day', '<=', (float) $request->input('price_max'))
                  ->orWhere('price', '<=', (float) $request->input('price_max'));
            });
        }

        // 5. Lọc theo Khoảng giá chọn nhanh (preset range)
        if ($request->filled('price_range')) {
            switch ($request->input('price_range')) {
                case 'under_1m': // Dưới 1 triệu/ngày
                    $query->where('rent_price_per_day', '<', 1000000);
                    break;
                case '1m_2m': // 1 - 2 triệu/ngày
                    $query->whereBetween('rent_price_per_day', [1000000, 2000000]);
                    break;
                case 'above_2m': // Trên 2 triệu/ngày
                    $query->where('rent_price_per_day', '>', 2000000);
                    break;
                case 'under_500':
                    $query->where('price', '<', 500000000);
                    break;
                case '500_1000':
                    $query->whereBetween('price', [500000000, 1000000000]);
                    break;
                case 'above_1000':
                    $query->where('price', '>', 1000000000);
                    break;
            }
        }

        // 6. Lọc theo Màu sắc
        if ($request->filled('color')) {
            $query->where('color', 'like', '%' . trim($request->input('color')) . '%');
        }

        // 7. Sắp xếp kết quả (Sorting)
        switch ($request->input('sort')) {
            case 'rent_asc':
                $query->orderBy('rent_price_per_day', 'asc');
                break;
            case 'rent_desc':
                $query->orderBy('rent_price_per_day', 'desc');
                break;
            case 'price_asc':
                $query->orderBy('price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('price', 'desc');
                break;
            case 'best_seller':
                $query->withCount('orderItems')->orderBy('order_items_count', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('name', 'desc');
                break;
            default:
                $query->orderBy('id', 'desc');
                break;
        }

        // Lấy danh sách sản phẩm có phân trang (12 xe / trang) và giữ lại query parameters trên link
        $products = $query->paginate(12)->withQueryString();

        // Lấy danh sách Hãng xe kèm số lượng xe của từng hãng
        $categories = Category::withCount('products')->get();

        // Lấy danh mục các màu sắc cơ bản và các màu trong database
        $basicColors = ['Trắng', 'Đen', 'Đỏ', 'Bạc', 'Xám', 'Xanh dương', 'Xanh lá', 'Vàng cát', 'Nâu', 'Cam'];
        $dbColors = Product::whereNotNull('color')
            ->where('color', '!=', '')
            ->distinct()
            ->pluck('color')
            ->toArray();
        $availableColors = array_values(array_unique(array_merge($basicColors, $dbColors)));

        return view('welcome', compact('products', 'categories', 'availableColors'));
    }
}