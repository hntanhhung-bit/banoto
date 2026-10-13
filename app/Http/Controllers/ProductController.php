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

        // Lọc theo trạng thái kiểm định & phê duyệt xe
        $filter = $request->input('filter', 'all');
        if ($filter === 'pending_approval') {
            $query->where('approval_status', 'pending');
        } elseif ($filter === 'approved_cars' || $filter === 'partner_cars') {
            $query->where('approval_status', 'approved');
        } elseif ($filter === 'rejected_cars') {
            $query->where('approval_status', 'rejected');
        } elseif ($filter === 'admin_cars') {
            $query->whereNull('partner_id');
        }

        $countPendingCars = Product::where('approval_status', 'pending')->count();
        $countApprovedCars = Product::where('approval_status', 'approved')->count();
        $countRejectedCars = Product::where('approval_status', 'rejected')->count();
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
            'countApprovedCars',
            'countRejectedCars',
            'countPartnerCars',
            'countAdminCars',
            'countTotal'
        ));
    }

    // Giao diện thêm xe mới (Đã tắt cho Admin - Xe do Đối tác đăng)
    public function create()
    {
        return redirect()->route('admin.products.index')
            ->with('warning', 'Quản trị viên không trực tiếp đăng xe. Xe trên sàn do các Nhà xe / Đối tác (Partner) ký gửi và đăng ký.');
    }

    // Xử lý lưu xe mới (Đã tắt cho Admin)
    public function store(Request $request)
    {
        return redirect()->route('admin.products.index')
            ->with('error', 'Chức năng đăng xe trực tiếp bởi Quản trị viên đã được tắt.');
    }
    // Giao diện sửa thông tin xe (Đã tắt cho Admin - Thông tin do Đối tác quản lý)
    public function edit(Product $product)
    {
        return redirect()->route('admin.products.index')
            ->with('warning', 'Quản trị viên không chỉnh sửa thông tin xe của đối tác. Vui lòng duyệt hoặc từ chối để đối tác cập nhật.');
    }

    // Xử lý cập nhật thông tin xe (Đã tắt cho Admin)
    public function update(Request $request, Product $product)
    {
        return redirect()->route('admin.products.index')
            ->with('error', 'Quản trị viên không có quyền chỉnh sửa thông tin xe của đối tác.');
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