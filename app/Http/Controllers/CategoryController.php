<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request)
    {
        $query = Category::withCount('products');

        if ($request->filled('keyword')) {
            $keyword = trim($request->input('keyword'));
            $query->where('name', 'like', "%{$keyword}%");
        }

        $categories = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ], [
            'name.required' => 'Vui lòng nhập tên hãng xe.',
            'name.unique' => 'Tên hãng xe này đã tồn tại trong hệ thống.',
        ]);

        Category::create($request->all());

        return redirect()->route('admin.categories.index')->with('success', 'Đã thêm hãng xe mới thành công!');
    }

    public function show(Category $category)
    {
        // Lấy danh sách các xe thuộc hãng này (kèm phân trang)
        $products = $category->products()->orderBy('id', 'desc')->paginate(8);
        return view('admin.categories.show', compact('category', 'products'));
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,' . $category->id,
        ], [
            'name.required' => 'Vui lòng nhập tên hãng xe.',
            'name.unique' => 'Tên hãng xe này đã tồn tại trong hệ thống.',
        ]);

        $category->update($request->all());

        return redirect()->route('admin.categories.index')->with('success', 'Đã cập nhật tên hãng xe thành công!');
    }

    public function destroy(Category $category)
    {
        $count = $category->products()->count();
        if ($count > 0) {
            return redirect()->route('admin.categories.index')->with('error', 'Không thể xóa hãng xe "' . $category->name . '" vì đang có ' . $count . ' chiếc xe thuộc hãng này. Vui lòng chuyển hoặc xóa các xe trước!');
        }

        $category->delete();

        return redirect()->route('admin.categories.index')->with('success', 'Đã xóa hãng xe "' . $category->name . '" khỏi hệ thống.');
    }
}