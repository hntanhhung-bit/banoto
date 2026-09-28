<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::with('category')
            ->where(function($q) {
                $q->whereNull('partner_id')
                  ->orWhere('approval_status', 'approved');
            });

        // Logic bộ lọc tìm kiếm
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }
        if ($request->filled('price_min')) {
            $query->where('price', '>=', $request->price_min);
        }
        if ($request->filled('price_max')) {
            $query->where('price', '<=', $request->price_max);
        }

        $products = $query->orderBy('id', 'desc')->get();
        $categories = Category::all();

        return view('home', compact('products', 'categories'));
    }
}