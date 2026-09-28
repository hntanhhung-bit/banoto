<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    // Hiển thị danh sách xe (Admin)
    public function index(Request $request)
    {
        $query = Product::with(['category', 'partner']);

        if ($request->filled('keyword')) {
            $keyword = trim($request->input('keyword'));
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('car_plate', 'like', "%{$keyword}%")
                  ->orWhere('car_condition', 'like', "%{$keyword}%")
                  ->orWhere('description', 'like', "%{$keyword}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        // Lọc theo nguồn xe & kiểm định
        $filter = $request->input('filter', 'all');
        if ($filter === 'pending_approval') {
            $query->whereNotNull('partner_id')->where('approval_status', 'pending');
        } elseif ($filter === 'partner_cars') {
            $query->whereNotNull('partner_id');
        } elseif ($filter === 'admin_cars') {
            $query->whereNull('partner_id');
        } elseif ($filter === 'approved_cars') {
            $query->where(function($q) {
                $q->whereNull('partner_id')->orWhere('approval_status', 'approved');
            });
        }

        $countPendingCars = Product::whereNotNull('partner_id')->where('approval_status', 'pending')->count();
        $countPartnerCars = Product::whereNotNull('partner_id')->count();
        $countAdminCars = Product::whereNull('partner_id')->count();
        $countTotal = Product::count();

        // Ưu tiên xe của đối tác đang chờ duyệt lên đầu danh sách để Admin dễ thấy
        $products = $query->orderByRaw("CASE WHEN partner_id IS NOT NULL AND approval_status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('id', 'desc')
            ->paginate(12)
            ->withQueryString();

        $categories = Category::all();
        return view('admin.products.index', compact(
            'products', 
            'categories',
            'filter',
            'countPendingCars',
            'countPartnerCars',
            'countAdminCars',
            'countTotal'
        ));
    }

    // Giao diện thêm xe mới
    public function create()
    {
        $categories = Category::all();
        return view('admin.products.create', compact('categories'));
    }

    // Xử lý lưu xe mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|integer|min:0',
            'rent_price_per_day' => 'nullable|integer|min:0',
            'driver_price_per_day' => 'nullable|integer|min:0',
            'rental_deposit' => 'nullable|integer|min:0',
            'quantity' => 'nullable|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'color' => 'nullable|string|max:255',
            'rental_status' => 'nullable|string|in:available,rented,maintenance',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'colors.*.extra_rent_price' => 'nullable|integer|min:0',
            'colors.*.rent_price_per_day' => 'nullable|integer|min:0',
            'colors.*.quantity' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'Vui lòng nhập tên mẫu xe.',
            'category_id.required' => 'Vui lòng chọn hãng xe (danh mục).',
            'price.integer' => 'Giá bán xe phải là số nguyên (không được chứa chữ cái hoặc số thập phân).',
            'price.min' => 'Giá bán xe không được là số âm (phải từ 0 trở lên).',
            'rent_price_per_day.integer' => 'Giá thuê tự lái cơ bản phải là số nguyên.',
            'rent_price_per_day.min' => 'Giá thuê tự lái không được là số âm (phải từ 0 trở lên).',
            'driver_price_per_day.integer' => 'Phí tài xế riêng phải là số nguyên.',
            'driver_price_per_day.min' => 'Phí tài xế riêng không được là số âm (phải từ 0 trở lên).',
            'rental_deposit.integer' => 'Tiền cọc giữ xe phải là số nguyên.',
            'rental_deposit.min' => 'Tiền cọc giữ xe không được là số âm (phải từ 0 trở lên).',
            'quantity.integer' => 'Số lượng xe phải là số nguyên.',
            'quantity.min' => 'Số lượng xe không được là số âm.',
            'colors.*.extra_rent_price.integer' => 'Phụ phí màu phải là số nguyên.',
            'colors.*.extra_rent_price.min' => 'Phụ phí màu không được là số âm.',
            'colors.*.rent_price_per_day.integer' => 'Tổng giá thuê theo màu phải là số nguyên.',
            'colors.*.rent_price_per_day.min' => 'Tổng giá thuê theo màu không được là số âm.',
            'colors.*.quantity.integer' => 'Số lượng xe theo màu phải là số nguyên.',
            'colors.*.quantity.min' => 'Số lượng xe theo màu không được là số âm.',
        ]);

        $data = $request->all();
        $data['price'] = $request->filled('price') ? (int) $request->price : (int) ($request->rent_price_per_day ?: 0);
        $data['rent_price_per_day'] = $request->filled('rent_price_per_day') ? (int) $request->rent_price_per_day : 0;
        $data['driver_price_per_day'] = $request->filled('driver_price_per_day') ? (int) $request->driver_price_per_day : 0;
        $data['rental_deposit'] = $request->filled('rental_deposit') ? (int) $request->rental_deposit : 0;
        $data['quantity'] = $request->filled('quantity') ? (int) $request->quantity : 10;

        // Xử lý upload ảnh
        if ($request->hasFile('image')) {
            $imageName = time().'.'.$request->image->extension();  
            $request->image->move(public_path('images'), $imageName);
            $data['image'] = $imageName;
        }

        $product = Product::create($data);

        // Lưu danh sách màu sắc & giá theo màu
        if ($request->has('colors') && is_array($request->colors)) {
            foreach ($request->colors as $c) {
                if (!empty($c['color_name'])) {
                    \App\Models\ProductColor::create([
                        'product_id' => $product->id,
                        'color_name' => $c['color_name'],
                        'color_hex' => $c['color_hex'] ?? '#FFFFFF',
                        'extra_rent_price' => (float) ($c['extra_rent_price'] ?? 0),
                        'rent_price_per_day' => (float) ($c['rent_price_per_day'] ?? ($product->rent_price_per_day + ($c['extra_rent_price'] ?? 0))),
                        'extra_sale_price' => (float) ($c['extra_sale_price'] ?? 0),
                        'quantity' => (int) ($c['quantity'] ?? 5),
                        'is_default' => !empty($c['is_default']),
                    ]);
                }
            }
        } else {
            // Tự động tạo 3 màu phổ biến nhất với giá khác nhau
            $baseRent = $product->rent_price_per_day ?: 800000;
            \App\Models\ProductColor::insert([
                [
                    'product_id' => $product->id,
                    'color_name' => 'Trắng ngọc trai',
                    'color_hex' => '#FFFFFF',
                    'extra_rent_price' => 50000,
                    'rent_price_per_day' => $baseRent + 50000,
                    'extra_sale_price' => 10000000,
                    'quantity' => 5,
                    'is_default' => 1,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'product_id' => $product->id,
                    'color_name' => 'Đen ánh kim',
                    'color_hex' => '#111111',
                    'extra_rent_price' => 0,
                    'rent_price_per_day' => $baseRent,
                    'extra_sale_price' => 0,
                    'quantity' => 5,
                    'is_default' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'product_id' => $product->id,
                    'color_name' => 'Đỏ thể thao',
                    'color_hex' => '#D0021B',
                    'extra_rent_price' => 100000,
                    'rent_price_per_day' => $baseRent + 100000,
                    'extra_sale_price' => 20000000,
                    'quantity' => 5,
                    'is_default' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        // Chú ý: Đã sửa thành admin.products.index
        return redirect()->route('admin.products.index')->with('success', 'Đã thêm xe mới kèm phân loại giá theo màu sắc thành công!');
    }

    // Giao diện sửa thông tin xe
    public function edit(Product $product)
    {
        $product->load('colors');
        $categories = Category::all();
        return view('admin.products.edit', compact('product', 'categories'));
    }

    // Xử lý cập nhật thông tin xe
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'nullable|integer|min:0',
            'rent_price_per_day' => 'nullable|integer|min:0',
            'driver_price_per_day' => 'nullable|integer|min:0',
            'rental_deposit' => 'nullable|integer|min:0',
            'quantity' => 'nullable|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'color' => 'nullable|string|max:255',
            'rental_status' => 'nullable|string|in:available,rented,maintenance',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'colors.*.extra_rent_price' => 'nullable|integer|min:0',
            'colors.*.rent_price_per_day' => 'nullable|integer|min:0',
            'colors.*.quantity' => 'nullable|integer|min:0',
        ], [
            'name.required' => 'Vui lòng nhập tên mẫu xe.',
            'category_id.required' => 'Vui lòng chọn hãng xe (danh mục).',
            'price.integer' => 'Giá bán xe phải là số nguyên (không được chứa chữ cái hoặc số thập phân).',
            'price.min' => 'Giá bán xe không được là số âm (phải từ 0 trở lên).',
            'rent_price_per_day.integer' => 'Giá thuê tự lái cơ bản phải là số nguyên.',
            'rent_price_per_day.min' => 'Giá thuê tự lái không được là số âm (phải từ 0 trở lên).',
            'driver_price_per_day.integer' => 'Phí tài xế riêng phải là số nguyên.',
            'driver_price_per_day.min' => 'Phí tài xế riêng không được là số âm (phải từ 0 trở lên).',
            'rental_deposit.integer' => 'Tiền cọc giữ xe phải là số nguyên.',
            'rental_deposit.min' => 'Tiền cọc giữ xe không được là số âm (phải từ 0 trở lên).',
            'quantity.integer' => 'Số lượng xe phải là số nguyên.',
            'quantity.min' => 'Số lượng xe không được là số âm.',
            'colors.*.extra_rent_price.integer' => 'Phụ phí màu phải là số nguyên.',
            'colors.*.extra_rent_price.min' => 'Phụ phí màu không được là số âm.',
            'colors.*.rent_price_per_day.integer' => 'Tổng giá thuê theo màu phải là số nguyên.',
            'colors.*.rent_price_per_day.min' => 'Tổng giá thuê theo màu không được là số âm.',
            'colors.*.quantity.integer' => 'Số lượng xe theo màu phải là số nguyên.',
            'colors.*.quantity.min' => 'Số lượng xe theo màu không được là số âm.',
        ]);

        $data = $request->all();
        if ($request->filled('price')) {
            $data['price'] = (int) $request->price;
        }
        if ($request->filled('rent_price_per_day')) {
            $data['rent_price_per_day'] = (int) $request->rent_price_per_day;
        }
        if ($request->filled('driver_price_per_day')) {
            $data['driver_price_per_day'] = (int) $request->driver_price_per_day;
        }
        if ($request->filled('rental_deposit')) {
            $data['rental_deposit'] = (int) $request->rental_deposit;
        }
        if ($request->has('quantity')) {
            $data['quantity'] = (int) $request->quantity;
        }

        // Xử lý cập nhật ảnh mới (và xóa ảnh cũ)
        if ($request->hasFile('image')) {
            // Xóa ảnh cũ nếu có
            if($product->image && file_exists(public_path('images/'.$product->image))){
                unlink(public_path('images/'.$product->image));
            }
            
            // Lưu ảnh mới
            $imageName = time().'.'.$request->image->extension();  
            $request->image->move(public_path('images'), $imageName);
            $data['image'] = $imageName;
        }

        $product->update($data);

        // Cập nhật danh sách màu sắc
        if ($request->has('colors') && is_array($request->colors)) {
            $product->colors()->delete();
            foreach ($request->colors as $c) {
                if (!empty($c['color_name'])) {
                    \App\Models\ProductColor::create([
                        'product_id' => $product->id,
                        'color_name' => $c['color_name'],
                        'color_hex' => $c['color_hex'] ?? '#FFFFFF',
                        'extra_rent_price' => (float) ($c['extra_rent_price'] ?? 0),
                        'rent_price_per_day' => (float) ($c['rent_price_per_day'] ?? ($product->rent_price_per_day + ($c['extra_rent_price'] ?? 0))),
                        'extra_sale_price' => (float) ($c['extra_sale_price'] ?? 0),
                        'quantity' => (int) ($c['quantity'] ?? 5),
                        'is_default' => !empty($c['is_default']),
                    ]);
                }
            }
        }

        // Chú ý: Đã sửa thành admin.products.index
        return redirect()->route('admin.products.index')->with('success', 'Cập nhật thông tin xe thành công!');
    }

    // Xử lý xóa xe
    public function destroy(Product $product)
    {
        // Xóa file ảnh trong thư mục (nếu có)
        if($product->image && file_exists(public_path('images/'.$product->image))){
            unlink(public_path('images/'.$product->image));
        }
        
        $product->delete();

        // Chú ý: Đã sửa thành admin.products.index
        return redirect()->route('admin.products.index')->with('success', 'Đã xóa xe khỏi hệ thống.');
    }

    // Hiển thị chi tiết xe cho Khách hàng (Bên ngoài trang chủ)
    public function show_normal(Product $product)
    {
        // Nếu xe của đối tác chưa được Admin duyệt thì chỉ Admin hoặc chính đối tác sở hữu mới được xem
        if ($product->partner_id && $product->approval_status !== 'approved') {
            if (!\Illuminate\Support\Facades\Auth::check() || (!in_array(\Illuminate\Support\Facades\Auth::user()->role, ['admin']) && \Illuminate\Support\Facades\Auth::id() !== $product->partner_id)) {
                abort(404, 'Mẫu xe này hiện đang trong quá trình thẩm định kỹ thuật hoặc tạm dừng hiển thị trên sàn.');
            }
        }

        return view('products.show', compact('product'));
    }

    // Hiển thị chi tiết xe trong khu vực Admin
    public function show(Product $product)
    {
        return view('admin.products.show', compact('product'));
    }

    // Admin phê duyệt xe của đối tác đăng lên sàn
    public function approveCar(Product $product)
    {
        $product->update([
            'approval_status' => 'approved',
            'approved_at' => now(),
            'admin_feedback' => null,
        ]);

        return redirect()->back()->with('success', "✅ Đã phê duyệt xe '{$product->name}' (BKS: {$product->car_plate}) thành công! Xe đã được cấp phép hiển thị cho khách thuê trên sàn AutoCar.");
    }

    // Admin từ chối phê duyệt xe của đối tác
    public function rejectCar(Request $request, Product $product)
    {
        $request->validate([
            'admin_feedback' => 'required|string|max:500',
        ], [
            'admin_feedback.required' => 'Vui lòng nhập lý do từ chối kiểm định xe.',
        ]);

        $product->update([
            'approval_status' => 'rejected',
            'admin_feedback' => $request->admin_feedback,
        ]);

        return redirect()->back()->with('success', "❌ Đã từ chối duyệt xe '{$product->name}' (BKS: {$product->car_plate}). Lý do đã được lưu để thông báo cho đối tác.");
    }

    // Admin phê duyệt / từ chối xe đối tác hàng loạt
    public function bulkApproval(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'exists:products,id',
            'action' => 'required|in:approve,reject',
            'reason' => 'nullable|string|max:500',
        ]);

        if ($request->action === 'approve') {
            Product::whereIn('id', $request->product_ids)->update([
                'approval_status' => 'approved',
                'approved_at' => now(),
                'admin_feedback' => null,
            ]);
            return redirect()->back()->with('success', '✅ Đã phê duyệt thành công ' . count($request->product_ids) . ' mẫu xe của đối tác!');
        } else {
            $reason = $request->reason ?: 'Chưa đạt tiêu chuẩn kiểm định & an toàn kỹ thuật của Sàn AutoCar.';
            Product::whereIn('id', $request->product_ids)->update([
                'approval_status' => 'rejected',
                'admin_feedback' => $reason,
            ]);
            return redirect()->back()->with('success', '❌ Đã từ chối kiểm định ' . count($request->product_ids) . ' mẫu xe với lý do: ' . $reason);
        }
    }
}