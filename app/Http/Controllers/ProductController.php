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
        $partners = \App\Models\User::where('role', 'partner')->get();
        return view('admin.products.create', compact('categories', 'partners'));
    }

    // Xử lý lưu xe mới
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'car_plate' => 'required|string|max:50',
            'car_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'car_condition' => 'required|string|max:1000',
            'rent_price_per_day' => 'required|numeric|min:0',
            'rental_deposit' => 'required|numeric|min:0',
            'driver_price_per_day' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:255',
            'rental_status' => 'nullable|in:available,rented,maintenance,sold',
            'partner_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ], [
            'name.required' => 'Vui lòng nhập tên mẫu xe.',
            'category_id.required' => 'Vui lòng chọn hãng xe (danh mục).',
            'car_plate.required' => 'Vui lòng nhập Biển số xe (BKS).',
            'car_year.required' => 'Vui lòng nhập Năm sản xuất / Đời xe.',
            'car_condition.required' => 'Vui lòng nhập Tình trạng kỹ thuật, Đăng kiểm & Bảo hiểm của xe.',
            'rent_price_per_day.required' => 'Vui lòng nhập giá thuê xe tự lái theo ngày.',
            'rental_deposit.required' => 'Vui lòng nhập số tiền cọc thế chân giữ xe.',
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            if (is_dir(public_path('storage'))) {
                @copy(public_path('images/' . $imageName), public_path('storage/' . $imageName));
            }
        }

        // Xử lý upload danh sách nhiều ảnh chi tiết
        $galleryImages = [];
        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $gName = time() . '_' . uniqid() . '.' . $file->extension();
                $file->move(public_path('images'), $gName);
                if (is_dir(public_path('storage'))) {
                    @copy(public_path('images/' . $gName), public_path('storage/' . $gName));
                }
                $galleryImages[] = $gName;
            }
        }

        $rentPrice = (int) $request->rent_price_per_day;
        $driverPrice = (int) ($request->driver_price_per_day ?: 0);
        $deposit = (int) $request->rental_deposit;
        $salePrice = $request->filled('price') ? (int) $request->price : ($rentPrice * 300);

        $product = Product::create([
            'partner_id' => $request->partner_id ?: null,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $salePrice,
            'is_for_rent' => 1,
            'rent_price_per_day' => $rentPrice,
            'driver_price_per_day' => $driverPrice,
            'rental_deposit' => $deposit,
            'rental_status' => $request->rental_status ?: 'available',
            'quantity' => 1, // Mỗi lần thêm là 1 chiếc xe cụ thể
            'color' => $request->color ?: 'Trắng ngọc trai',
            'description' => $request->description,
            'image' => $imageName,
            'gallery_images' => !empty($galleryImages) ? json_encode($galleryImages) : null,
            'car_plate' => $request->car_plate,
            'car_year' => (int) $request->car_year,
            'car_condition' => $request->car_condition,
            'approval_status' => 'approved',
            'approved_at' => now(),
            'admin_feedback' => null,
        ]);

        // Tạo biến thể màu mặc định
        \App\Models\ProductColor::create([
            'product_id' => $product->id,
            'color_name' => $product->color ?: 'Trắng ngọc trai',
            'color_hex' => '#FFFFFF',
            'extra_rent_price' => 0,
            'rent_price_per_day' => $rentPrice,
            'extra_sale_price' => 0,
            'quantity' => 1,
            'is_default' => 1,
        ]);

        return redirect()->route('admin.products.index')->with('success', "Đã thêm xe mới '{$product->name}' (BKS: {$product->car_plate}) vào kho hệ thống thành công!");
    }

    // Giao diện sửa thông tin xe
    public function edit(Product $product)
    {
        $categories = Category::all();
        $partners = \App\Models\User::where('role', 'partner')->get();
        return view('admin.products.edit', compact('product', 'categories', 'partners'));
    }

    // Xử lý cập nhật thông tin xe
    public function update(Request $request, Product $product)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'car_plate' => 'required|string|max:50',
            'car_year' => 'required|integer|min:2000|max:' . (date('Y') + 1),
            'car_condition' => 'required|string|max:1000',
            'rent_price_per_day' => 'required|numeric|min:0',
            'rental_deposit' => 'required|numeric|min:0',
            'driver_price_per_day' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:255',
            'rental_status' => 'required|in:available,rented,maintenance,sold',
            'partner_id' => 'nullable|exists:users,id',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:4096',
        ], [
            'name.required' => 'Vui lòng nhập tên mẫu xe.',
            'category_id.required' => 'Vui lòng chọn hãng xe (danh mục).',
            'car_plate.required' => 'Vui lòng nhập Biển số xe (BKS).',
            'car_year.required' => 'Vui lòng nhập Năm sản xuất / Đời xe.',
            'car_condition.required' => 'Vui lòng nhập Tình trạng kỹ thuật, Đăng kiểm & Bảo hiểm của xe.',
            'rent_price_per_day.required' => 'Vui lòng nhập giá thuê xe tự lái theo ngày.',
            'rental_deposit.required' => 'Vui lòng nhập số tiền cọc thế chân giữ xe.',
        ]);

        $data = [
            'category_id' => $request->category_id,
            'partner_id' => $request->partner_id ?: null,
            'name' => $request->name,
            'car_plate' => $request->car_plate,
            'car_year' => (int) $request->car_year,
            'car_condition' => $request->car_condition,
            'rent_price_per_day' => (int) $request->rent_price_per_day,
            'driver_price_per_day' => (int) ($request->driver_price_per_day ?: 0),
            'rental_deposit' => (int) $request->rental_deposit,
            'rental_status' => $request->rental_status,
            'quantity' => 1,
            'color' => $request->color ?: 'Trắng ngọc trai',
            'description' => $request->description,
        ];

        if ($request->filled('price')) {
            $data['price'] = (int) $request->price;
        }

        // Xử lý upload ảnh đại diện mới
        if ($request->hasFile('image')) {
            if ($product->image && file_exists(public_path('images/' . $product->image))) {
                @unlink(public_path('images/' . $product->image));
            }
            if ($product->image && is_dir(public_path('storage')) && file_exists(public_path('storage/' . $product->image))) {
                @unlink(public_path('storage/' . $product->image));
            }

            $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            if (is_dir(public_path('storage'))) {
                @copy(public_path('images/' . $imageName), public_path('storage/' . $imageName));
            }
            $data['image'] = $imageName;
        }

        // Xử lý gallery
        $currentGallery = is_array($product->gallery_images) ? $product->gallery_images : (json_decode($product->gallery_images, true) ?: []);

        if ($request->has('remove_gallery') && is_array($request->remove_gallery)) {
            foreach ($request->remove_gallery as $rm) {
                if (($key = array_search($rm, $currentGallery)) !== false) {
                    unset($currentGallery[$key]);
                    if (file_exists(public_path('images/' . $rm))) {
                        @unlink(public_path('images/' . $rm));
                    }
                    if (is_dir(public_path('storage')) && file_exists(public_path('storage/' . $rm))) {
                        @unlink(public_path('storage/' . $rm));
                    }
                }
            }
            $currentGallery = array_values($currentGallery);
        }

        if ($request->hasFile('gallery_images')) {
            foreach ($request->file('gallery_images') as $file) {
                $gName = time() . '_' . uniqid() . '.' . $file->extension();
                $file->move(public_path('images'), $gName);
                if (is_dir(public_path('storage'))) {
                    @copy(public_path('images/' . $gName), public_path('storage/' . $gName));
                }
                $currentGallery[] = $gName;
            }
        }
        $data['gallery_images'] = !empty($currentGallery) ? json_encode(array_values($currentGallery)) : null;

        $product->update($data);

        // Cập nhật ProductColor
        $defaultColor = $product->colors()->where('is_default', 1)->first();
        if ($defaultColor) {
            $defaultColor->update([
                'color_name' => $product->color ?: 'Trắng ngọc trai',
                'rent_price_per_day' => $product->rent_price_per_day,
                'quantity' => 1,
            ]);
        } else {
            \App\Models\ProductColor::create([
                'product_id' => $product->id,
                'color_name' => $product->color ?: 'Trắng ngọc trai',
                'color_hex' => '#FFFFFF',
                'extra_rent_price' => 0,
                'rent_price_per_day' => $product->rent_price_per_day,
                'extra_sale_price' => 0,
                'quantity' => 1,
                'is_default' => 1,
            ]);
        }

        return redirect()->route('admin.products.index')->with('success', "Đã cập nhật thông tin xe '{$product->name}' thành công!");
    }

    // Xử lý xóa xe
    public function destroy(Product $product)
    {
        // Xóa file ảnh đại diện trong thư mục (nếu có)
        if($product->image && file_exists(public_path('images/'.$product->image))){
            @unlink(public_path('images/'.$product->image));
        }
        
        // Xóa các file ảnh gallery (nếu có)
        if(!empty($product->gallery_images) && is_array($product->gallery_images)){
            foreach ($product->gallery_images as $gImg) {
                if(file_exists(public_path('images/'.$gImg))){
                    @unlink(public_path('images/'.$gImg));
                }
            }
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

    // Trang đặt dịch vụ & thanh toán (Tách biệt khỏi trang chi tiết xe)
    public function showBooking(Product $product)
    {
        // Kiểm tra xe có được hiển thị không
        if ($product->partner_id && $product->approval_status !== 'approved') {
            if (!\Illuminate\Support\Facades\Auth::check() || (!in_array(\Illuminate\Support\Facades\Auth::user()->role, ['admin']) && \Illuminate\Support\Facades\Auth::id() !== $product->partner_id)) {
                abort(404, 'Mẫu xe này hiện đang trong quá trình thẩm định kỹ thuật.');
            }
        }

        // Nếu xe đã bán thành công -> không cho phép vào trang đặt lịch hay thuê xe
        if ($product->isSold()) {
            return redirect()->route('products.show', $product->id)->with('error', "Mẫu xe '{$product->name}' đã được bán thành công. Không thể đặt xem xe hoặc thuê xe nữa.");
        }

        // Nếu xe đang phục vụ khách hàng -> không cho phép vào trang đặt lịch hay thuê xe
        if ($product->isServing()) {
            return redirect()->route('products.show', $product->id)->with('error', "Mẫu xe '{$product->name}' hiện tại đang trong chuyến phục vụ khách hàng (đang cho thuê). Tạm thời không thể đặt xem xe, lái thử hoặc thuê xe lúc này.");
        }

        return view('products.booking', compact('product'));
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