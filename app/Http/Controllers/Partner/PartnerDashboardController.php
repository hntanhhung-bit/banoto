<?php

namespace App\Http\Controllers\Partner;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use App\Models\Appointment;
use App\Models\Rental;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PartnerDashboardController extends Controller
{
    // Hiển thị form đăng ký đối tác Showroom / Nhà xe
    public function showRegister()
    {
        if (Auth::check()) {
            if (Auth::user()->role === 'partner' && Auth::user()->partner_status === 'approved') {
                return redirect()->route('partner.dashboard');
            }
            if (in_array(Auth::user()->partner_status, ['pending', 'rejected'])) {
                return redirect()->route('partner.pending');
            }
        }
        return view('partner.register');
    }

    // Trang hiển thị trạng thái xét duyệt hồ sơ đối tác
    public function pendingApproval()
    {
        $user = Auth::user();
        if (!$user) {
            return redirect()->route('login')->with('info', 'Vui lòng đăng nhập để kiểm tra tiến độ xét duyệt hồ sơ.');
        }

        if ($user->role === 'partner' && $user->partner_status === 'approved') {
            return redirect()->route('partner.dashboard')->with('success', 'Hồ sơ của bạn đã được phê duyệt thành công!');
        }

        return view('partner.pending', compact('user'));
    }

    // Xử lý nộp hồ sơ đăng ký đối tác
    public function processRegister(Request $request)
    {
        $isLoggedIn = Auth::check();
        $userId = $isLoggedIn ? Auth::id() : null;

        $rules = [
            'name' => 'required|string|max:255',
            'phone' => ['required', 'regex:/^[0-9]{10}$/'],
            'email' => 'required|email|max:255|unique:users,email' . ($userId ? ",$userId" : ''),
            'showroom_address' => 'required|string|max:500',
            'representative_name' => 'required|string|max:255',
            'id_card_number' => 'required|string|max:50',
            'tax_code' => 'nullable|string|max:50',
            'business_license_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'id_card_image' => 'required|image|mimes:jpeg,png,jpg,webp|max:5120',
            'car_count' => 'nullable|integer|min:1',
            'service_type' => 'required|in:sale,rental_self,rental_driver,all',
        ];

        if (!$isLoggedIn) {
            $rules['password'] = 'required|string|min:6|confirmed';
        }

        $request->validate($rules, [
            'name.required' => 'Vui lòng nhập tên Showroom / Doanh nghiệp của bạn.',
            'phone.required' => 'Vui lòng nhập số điện thoại liên hệ.',
            'phone.regex' => 'Số điện thoại phải gồm đúng 10 chữ số.',
            'email.required' => 'Vui lòng nhập email đăng nhập.',
            'email.unique' => 'Email này đã được sử dụng trên hệ thống.',
            'password.required' => 'Vui lòng nhập mật khẩu.',
            'password.min' => 'Mật khẩu phải từ 6 ký tự trở lên.',
            'password.confirmed' => 'Xác nhận mật khẩu không khớp.',
            'showroom_address.required' => 'Vui lòng nhập địa chỉ Showroom / Bãi xe.',
            'representative_name.required' => 'Vui lòng nhập họ tên người đại diện pháp luật.',
            'id_card_number.required' => 'Vui lòng nhập số CMND / CCCD người đại diện.',
            'business_license_image.required' => 'Vui lòng tải lên ảnh chụp Giấy phép đăng ký kinh doanh xe.',
            'id_card_image.required' => 'Vui lòng tải lên ảnh chụp CCCD hoặc ảnh chụp Showroom bãi xe.',
        ]);

        $uploadDir = public_path('uploads/partner_docs');
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        $licenseFileName = null;
        if ($request->hasFile('business_license_image')) {
            $licenseFileName = 'license_' . time() . '_' . uniqid() . '.' . $request->file('business_license_image')->extension();
            $request->file('business_license_image')->move($uploadDir, $licenseFileName);
        }

        $idCardFileName = null;
        if ($request->hasFile('id_card_image')) {
            $idCardFileName = 'idcard_' . time() . '_' . uniqid() . '.' . $request->file('id_card_image')->extension();
            $request->file('id_card_image')->move($uploadDir, $idCardFileName);
        }

        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'showroom_name' => $request->name,
            'showroom_address' => $request->showroom_address,
            'representative_name' => $request->representative_name,
            'id_card_number' => $request->id_card_number,
            'tax_code' => $request->tax_code,
            'business_license_image' => $licenseFileName,
            'id_card_image' => $idCardFileName,
            'partner_status' => 'pending',
            'partner_applied_at' => now(),
            'partner_reject_reason' => null,
            'role' => 'user', // Vẫn là user thông thường cho tới khi Admin phê duyệt
        ];

        if ($request->filled('password')) {
            $userData['password'] = \Illuminate\Support\Facades\Hash::make($request->password);
        }

        if ($isLoggedIn) {
            $user = Auth::user();
            $user->update($userData);
        } else {
            $userData['email_verified_at'] = now();
            $user = \App\Models\User::create($userData);
            Auth::login($user);
        }

        return redirect()->route('partner.pending')->with('success', "Hồ sơ đăng ký đối tác của Quý khách đã được gửi thành công! Ban Quản Trị AutoCar đang tiến hành thẩm định và sẽ phản hồi sớm nhất.");
    }

    // Lấy ID đối tác hiện tại (hoặc nếu là admin thì cho phép xem của đối tác đầu tiên)
    private function getPartnerId()
    {
        $user = Auth::user();
        if ($user->role === 'admin') {
            $firstPartner = \App\Models\User::where('role', 'partner')->first();
            return $firstPartner ? $firstPartner->id : $user->id;
        }
        return $user->id;
    }

    // Trang chủ Dashboard Đối tác
    public function dashboard()
    {
        $partnerId = $this->getPartnerId();
        $partnerUser = Auth::user()->role === 'partner' ? Auth::user() : \App\Models\User::find($partnerId);

        $totalCars = Product::where('partner_id', $partnerId)->count();
        $pendingAppointments = Appointment::where('partner_id', $partnerId)->where('status', 'pending')->count();
        $activeRentals = Rental::where('partner_id', $partnerId)->whereIn('rental_status', ['confirmed', 'in_progress'])->count();

        // Thống kê tài chính cho thuê xe (10% Sàn - 90% Showroom)
        $paidRentals = Rental::where('partner_id', $partnerId)->where('payment_status', '!=', 'unpaid')->get();
        $totalRentalRevenue = $paidRentals->sum('total_rental_fee') + $paidRentals->sum('total_driver_fee');
        $platformFeeTotal = $paidRentals->sum('platform_fee') ?: round($totalRentalRevenue * 0.10);
        $partnerPayoutTotal = $paidRentals->sum('partner_payout') ?: ($totalRentalRevenue - $platformFeeTotal);

        // Thống kê giới thiệu mua bán xe thành công (Môi giới 1%)
        $wonDealsCount = Appointment::where('partner_id', $partnerId)->where('deal_status', 'deal_won')->count();
        $totalSalesCommission = Appointment::where('partner_id', $partnerId)->where('deal_status', 'deal_won')->sum('commission_amount');

        // Danh sách lịch hẹn mới nhất
        $recentAppointments = Appointment::where('partner_id', $partnerId)
            ->with(['product', 'user'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        // Danh sách đơn thuê xe mới nhất
        $recentRentals = Rental::where('partner_id', $partnerId)
            ->with(['product', 'user'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();

        return view('partner.dashboard', compact(
            'partnerUser',
            'totalCars',
            'pendingAppointments',
            'wonDealsCount',
            'totalSalesCommission',
            'activeRentals',
            'totalRentalRevenue',
            'platformFeeTotal',
            'partnerPayoutTotal',
            'recentAppointments',
            'recentRentals'
        ));
    }

    // Danh sách xe của đối tác
    public function cars()
    {
        $partnerId = $this->getPartnerId();
        $cars = Product::where('partner_id', $partnerId)
            ->with('category')
            ->orderBy('id', 'desc')
            ->paginate(12);

        return view('partner.cars', compact('cars'));
    }

    // Giao diện thêm xe mới vào Showroom của đối tác
    public function createCar()
    {
        $categories = Category::all();
        return view('partner.cars.create', compact('categories'));
    }

    // Xử lý lưu xe mới của đối tác
    public function storeCar(Request $request)
    {
        $partnerId = $this->getPartnerId();

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
            'quantity' => 'nullable|integer|min:1',
            'color' => 'nullable|string|max:255',
            'rental_status' => 'nullable|in:available,maintenance',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
        ], [
            'name.required' => 'Vui lòng nhập tên mẫu xe.',
            'category_id.required' => 'Vui lòng chọn hãng xe (danh mục).',
            'car_plate.required' => 'Vui lòng nhập Biển số xe (BKS).',
            'car_year.required' => 'Vui lòng nhập Năm sản xuất / Đời xe.',
            'car_condition.required' => 'Vui lòng nhập Tình trạng kỹ thuật, Đăng kiểm & Bảo hiểm của xe.',
            'rent_price_per_day.required' => 'Vui lòng nhập giá thuê xe tự lái theo ngày.',
            'rent_price_per_day.min' => 'Giá thuê xe không được là số âm.',
            'rental_deposit.required' => 'Vui lòng nhập số tiền cọc thế chân giữ xe.',
            'rental_deposit.min' => 'Tiền cọc không được là số âm.',
        ]);

        $imageName = null;
        if ($request->hasFile('image')) {
            $imageName = time() . '_' . uniqid() . '.' . $request->image->extension();
            $request->image->move(public_path('images'), $imageName);
            if (is_dir(public_path('storage'))) {
                @copy(public_path('images/' . $imageName), public_path('storage/' . $imageName));
            }
        }

        $rentPrice = (float) $request->rent_price_per_day;
        $driverPrice = (float) ($request->driver_price_per_day ?: 0);
        $deposit = (float) $request->rental_deposit;
        $salePrice = $request->filled('price') ? (float) $request->price : ($rentPrice * 300);

        $product = Product::create([
            'partner_id' => $partnerId,
            'category_id' => $request->category_id,
            'name' => $request->name,
            'price' => $salePrice,
            'is_for_rent' => 1,
            'rent_price_per_day' => $rentPrice,
            'driver_price_per_day' => $driverPrice,
            'rental_deposit' => $deposit,
            'rental_status' => $request->rental_status ?: 'available',
            'quantity' => (int) ($request->quantity ?: 1),
            'color' => $request->color ?: 'Trắng ngọc trai',
            'description' => $request->description,
            'image' => $imageName,
            'car_plate' => $request->car_plate,
            'car_year' => (int) $request->car_year,
            'car_condition' => $request->car_condition,
            'approval_status' => 'pending', // Phải chờ Admin duyệt
            'admin_feedback' => null,
            'approved_at' => null,
        ]);

        // Tạo biến thể màu mặc định để khách chọn màu
        $mainColor = $product->color ?: 'Trắng ngọc trai';
        \App\Models\ProductColor::create([
            'product_id' => $product->id,
            'color_name' => $mainColor,
            'color_hex' => '#FFFFFF',
            'extra_rent_price' => 0,
            'rent_price_per_day' => $rentPrice,
            'quantity' => (int) ($product->quantity ?: 1),
            'is_default' => 1,
        ]);

        return redirect()->route('partner.cars')->with('success', "✅ Đã lưu mẫu xe '{$product->name}' (BKS: {$product->car_plate}) thành công! Xe đang ở trạng thái 'Chờ kiểm định'. Ban Quản Trị sàn sẽ phê duyệt trước khi hiển thị cho khách thuê.");
    }

    // Giao diện chỉnh sửa thông tin xe của đối tác
    public function editCar(Product $product)
    {
        $partnerId = $this->getPartnerId();
        if ($product->partner_id !== $partnerId && Auth::user()->role !== 'admin') {
            abort(403, 'Bạn không có quyền chỉnh sửa mẫu xe này.');
        }

        $categories = Category::all();
        return view('partner.cars.edit', compact('product', 'categories'));
    }

    // Xử lý cập nhật xe của đối tác
    public function updateCar(Request $request, Product $product)
    {
        $partnerId = $this->getPartnerId();
        if ($product->partner_id !== $partnerId && Auth::user()->role !== 'admin') {
            abort(403, 'Bạn không có quyền cập nhật mẫu xe này.');
        }

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
            'quantity' => 'nullable|integer|min:1',
            'color' => 'nullable|string|max:255',
            'rental_status' => 'required|in:available,rented,maintenance',
            'description' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
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
            'name' => $request->name,
            'car_plate' => $request->car_plate,
            'car_year' => (int) $request->car_year,
            'car_condition' => $request->car_condition,
            'rent_price_per_day' => (float) $request->rent_price_per_day,
            'driver_price_per_day' => (float) ($request->driver_price_per_day ?: 0),
            'rental_deposit' => (float) $request->rental_deposit,
            'rental_status' => $request->rental_status,
            'quantity' => (int) ($request->quantity ?: 1),
            'color' => $request->color ?: 'Trắng ngọc trai',
            'description' => $request->description,
        ];

        // Nếu xe từng bị từ chối, khi đối tác sửa lại thông tin thì chuyển về pending để Admin duyệt lại
        if ($product->approval_status === 'rejected') {
            $data['approval_status'] = 'pending';
            $data['admin_feedback'] = null;
        }

        if ($request->filled('price')) {
            $data['price'] = (float) $request->price;
        }

        // Cập nhật ảnh nếu có tải ảnh mới
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

        $product->update($data);

        return redirect()->route('partner.cars')->with('success', "✅ Đã cập nhật thông tin mẫu xe '{$product->name}' thành công!");
    }

    // Gỡ xe khỏi Showroom
    public function destroyCar(Product $product)
    {
        $partnerId = $this->getPartnerId();
        if ($product->partner_id !== $partnerId && Auth::user()->role !== 'admin') {
            abort(403, 'Bạn không có quyền gỡ mẫu xe này.');
        }

        $hasActiveRentals = $product->rentals()->whereIn('rental_status', ['pending', 'confirmed', 'in_progress'])->exists();
        if ($hasActiveRentals) {
            return redirect()->back()->with('error', "Không thể gỡ mẫu xe '{$product->name}' vì đang có đơn thuê xe chờ bàn giao hoặc đang phục vụ!");
        }

        $name = $product->name;
        $product->delete();

        return redirect()->route('partner.cars')->with('success', "Đã gỡ mẫu xe '{$name}' khỏi Showroom của bạn.");
    }

    // Cập nhật trạng thái xe (Sẵn sàng / Đang bảo dưỡng / Đang cho thuê)
    public function updateCarStatus(Request $request, Product $product)
    {
        $partnerId = $this->getPartnerId();
        if ($product->partner_id !== $partnerId && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'rental_status' => 'required|in:available,rented,maintenance',
        ]);

        $product->update([
            'rental_status' => $request->rental_status,
        ]);

        return redirect()->back()->with('success', "Đã cập nhật trạng thái mẫu xe {$product->name}!");
    }

    // Quản lý lịch hẹn xem xe
    public function appointments(Request $request)
    {
        $partnerId = $this->getPartnerId();
        $query = Appointment::where('partner_id', $partnerId)->with(['product', 'user']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $appointments = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('partner.appointments', compact('appointments'));
    }

    // Cập nhật trạng thái lịch hẹn & kết quả chốt bán xe
    public function updateAppointmentStatus(Request $request, Appointment $appointment)
    {
        $partnerId = $this->getPartnerId();
        if ($appointment->partner_id !== $partnerId && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'deal_status' => 'nullable|in:negotiating,deal_won,deal_lost',
            'deal_price' => 'nullable|numeric|min:0',
            'commission_amount' => 'nullable|numeric|min:0',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $dealStatus = $request->input('deal_status', $appointment->deal_status ?: 'negotiating');
        $dealPrice = $request->filled('deal_price') ? (float) $request->deal_price : $appointment->deal_price;
        $commissionAmount = $appointment->commission_amount;

        if ($dealStatus === 'deal_won') {
            if ($request->filled('commission_amount') && (float) $request->commission_amount > 0) {
                $commissionAmount = (float) $request->commission_amount;
            } elseif ($dealPrice > 0) {
                // 1% hoa hồng môi giới bán xe thành công
                $commissionAmount = round($dealPrice * 0.01);
            } else {
                $basePrice = $appointment->product?->price ?: 500000000;
                $commissionAmount = round($basePrice * 0.01);
            }
        }

        $appointment->update([
            'status' => $request->status,
            'deal_status' => $dealStatus,
            'deal_price' => $dealPrice,
            'commission_amount' => $commissionAmount,
            'admin_note' => $request->admin_note,
        ]);

        return redirect()->back()->with('success', 'Đã cập nhật tiến độ tư vấn & hoa hồng môi giới giới thiệu mua xe!');
    }

    // Quản lý đơn thuê xe
    public function rentals(Request $request)
    {
        $partnerId = $this->getPartnerId();
        $query = Rental::where('partner_id', $partnerId)->with(['product', 'user', 'paymentTransactions']);

        // 1. Lọc theo trạng thái tiến độ thuê xe
        $status = $request->input('rental_status', 'all');
        if (in_array($status, ['pending', 'confirmed', 'in_progress', 'returned', 'cancelled'])) {
            $query->where('rental_status', $status);
        }

        // 2. Lọc theo trạng thái thanh toán
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // 3. Tìm kiếm theo từ khóa
        if ($request->filled('keyword')) {
            $kw = trim($request->keyword);
            $query->where(function($q) use ($kw) {
                $q->where('rental_code', 'like', "%{$kw}%")
                  ->orWhere('customer_name', 'like', "%{$kw}%")
                  ->orWhere('customer_phone', 'like', "%{$kw}%")
                  ->orWhereHas('product', function($pq) use ($kw) {
                      $pq->where('name', 'like', "%{$kw}%");
                  });
            });
        }

        // 4. Đếm số lượng theo từng tiến độ
        $countQuery = Rental::where('partner_id', $partnerId);
        $countTotal = (clone $countQuery)->count();
        $countPending = (clone $countQuery)->where('rental_status', 'pending')->count();
        $countConfirmed = (clone $countQuery)->where('rental_status', 'confirmed')->count();
        $countInProgress = (clone $countQuery)->where('rental_status', 'in_progress')->count();
        $countReturned = (clone $countQuery)->where('rental_status', 'returned')->count();
        $countCancelled = (clone $countQuery)->where('rental_status', 'cancelled')->count();

        $rentals = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('partner.rentals', compact(
            'rentals',
            'status',
            'countTotal',
            'countPending',
            'countConfirmed',
            'countInProgress',
            'countReturned',
            'countCancelled'
        ));
    }

    // Thao tác hàng loạt thay đổi nhanh tiến độ nhiều hợp đồng cùng lúc
    public function bulkUpdateRentalStatus(Request $request)
    {
        $partnerId = $this->getPartnerId();
        $request->validate([
            'rental_ids' => 'required|array|min:1',
            'rental_ids.*' => 'exists:rentals,id',
            'rental_status' => 'required|in:pending,confirmed,in_progress,returned,cancelled',
        ], [
            'rental_ids.required' => 'Vui lòng tích chọn ít nhất 1 hợp đồng để thực hiện.',
            'rental_ids.min' => 'Vui lòng tích chọn ít nhất 1 hợp đồng để thực hiện.',
            'rental_status.required' => 'Vui lòng chọn trạng thái tiến độ cần cập nhật.',
        ]);

        $rentalIds = $request->input('rental_ids', []);
        $newStatus = $request->input('rental_status');

        $query = Rental::whereIn('id', $rentalIds);
        if (Auth::user()->role !== 'admin') {
            $query->where('partner_id', $partnerId);
        }
        $rentals = $query->get();

        $count = 0;
        foreach ($rentals as $rental) {
            $rental->update(['rental_status' => $newStatus]);

            // Cập nhật trạng thái xe tương ứng
            if (in_array($newStatus, ['confirmed', 'in_progress'])) {
                $rental->product?->update(['rental_status' => 'rented']);
            } elseif (in_array($newStatus, ['returned', 'cancelled'])) {
                $rental->product?->update(['rental_status' => 'available']);
            }
            $count++;
        }

        $statusLabels = [
            'pending' => 'Chờ đối chiếu / duyệt',
            'confirmed' => 'Đã sẵn sàng xe',
            'in_progress' => 'Đang phục vụ / Khách đang đi',
            'returned' => 'Khách đã trả xe',
            'cancelled' => 'Đã hủy đơn',
        ];
        $label = $statusLabels[$newStatus] ?? $newStatus;

        return redirect()->back()->with('success', "✅ Đã cập nhật thành công {$count} hợp đồng sang tiến độ: '{$label}'!");
    }

    // Cập nhật trạng thái đơn thuê xe & giao xe
    public function updateRentalStatus(Request $request, Rental $rental)
    {
        $partnerId = $this->getPartnerId();
        if ($rental->partner_id !== $partnerId && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $request->validate([
            'rental_status' => 'required|in:pending,confirmed,in_progress,returned,cancelled',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:50',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $rental->update([
            'rental_status' => $request->rental_status,
            'driver_name' => $request->driver_name,
            'driver_phone' => $request->driver_phone,
            'admin_note' => $request->admin_note,
        ]);

        if (in_array($request->rental_status, ['confirmed', 'in_progress'])) {
            $rental->product?->update(['rental_status' => 'rented']);
        } elseif (in_array($request->rental_status, ['returned', 'cancelled'])) {
            $rental->product?->update(['rental_status' => 'available']);
        }

        return redirect()->back()->with('success', 'Đã cập nhật tiến độ đơn thuê xe!');
    }

    // Đối tác xem Phiếu Đơn Thuê Xe & Biên Bản Bàn Giao Điện Tử để đối chiếu
    public function voucher(Rental $rental)
    {
        $partnerId = $this->getPartnerId();
        if ($rental->partner_id !== $partnerId && Auth::user()->role !== 'admin') {
            abort(403, 'Bạn không có quyền truy cập phiếu đơn này.');
        }

        $rental->load(['product.category', 'partner', 'paymentTransactions']);

        return view('rentals.voucher', compact('rental'));
    }

    // Đối tác đối chiếu Mã Bảo Mật (OTP 6 số) do khách cung cấp để xác nhận bàn giao xe
    public function verifyHandover(Request $request, Rental $rental)
    {
        $partnerId = $this->getPartnerId();
        if ($rental->partner_id !== $partnerId && Auth::user()->role !== 'admin') {
            abort(403, 'Bạn không có quyền thực hiện thao tác này.');
        }

        $request->validate([
            'handover_code' => 'required|string',
            'handover_odo' => 'nullable|numeric|min:0',
            'handover_fuel' => 'nullable|integer|min:0|max:100',
            'handover_notes' => 'nullable|string|max:1000',
        ], [
            'handover_code.required' => 'Vui lòng nhập Mã bảo mật đối chiếu do khách hàng cung cấp.',
        ]);

        // Đảm bảo đơn đã nộp tiền cọc và có mã đối chiếu hợp lệ
        if (empty($rental->handover_code) || $rental->payment_status === 'unpaid') {
            return redirect()->back()->with('error', '❌ Đơn thuê xe này chưa nộp tiền cọc (hoặc chưa được Admin xác nhận cọc). Mã bảo mật đối chiếu chưa được kích hoạt, đối tác tuyệt đối không được phép bàn giao xe!');
        }

        // Kiểm tra mã đối chiếu
        if (trim($request->handover_code) !== trim($rental->handover_code)) {
            return redirect()->back()->with('error', '❌ MÃ BẢO MẬT ĐỐI CHIẾU KHÔNG CHÍNH XÁC! Bạn không được phép bàn giao xe khi mã chưa trùng khớp để phòng ngừa rủi ro mạo danh. Vui lòng yêu cầu khách mở lại Phiếu Đơn Thuê Xe để lấy đúng 6 chữ số.');
        }

        $partnerName = Auth::user()->company_name ?: Auth::user()->name;

        $rental->update([
            'handover_status' => 'verified',
            'handover_verified_at' => now(),
            'handover_verified_by' => Auth::user()->name . " ({$partnerName})",
            'handover_odo' => $request->handover_odo,
            'handover_fuel' => $request->handover_fuel,
            'handover_notes' => $request->handover_notes,
            'rental_status' => 'in_progress', // Chuyển trạng thái đơn sang Đang phục vụ / Đang đi xe
        ]);

        // Cập nhật trạng thái xe sang đang thuê
        $rental->product?->update(['rental_status' => 'rented']);

        PaymentTransaction::create([
            'rental_id' => $rental->id,
            'gateway' => 'handover_verified',
            'gateway_order_id' => 'HANDOVER_' . $rental->rental_code,
            'amount' => 0,
            'status' => 'success',
            'message' => "Xác thực mã bảo mật đối chiếu thành công ({$rental->handover_code}). Đã bàn giao xe cho khách hàng {$rental->customer_name}. ODO lúc giao: {$request->handover_odo} km, Xăng: {$request->handover_fuel}%." . ($request->handover_notes ? " Ghi chú: {$request->handover_notes}" : ''),
            'paid_at' => now(),
        ]);

        return redirect()->back()->with('success', "✅ ĐỐI CHIẾU MÃ BẢO MẬT THÀNH CÔNG! Đã bàn giao xe cho khách hàng {$rental->customer_name}. Hợp đồng chuyển sang trạng thái ĐANG PHỤC VỤ.");
    }

    // Bắt đầu thanh toán 10% hoa hồng sàn qua Cổng MoMo Test (Ví MoMo hoặc Thẻ ATM Napas)
    public function payCommissionMomo(Request $request, Rental $rental, \App\Services\MomoService $momo)
    {
        $partnerId = $this->getPartnerId();
        if ($rental->partner_id !== $partnerId && Auth::user()->role !== 'admin') {
            abort(403, 'Bạn không có quyền thực hiện thanh toán cho đơn thuê này.');
        }

        if ($rental->partner_commission_status === 'paid') {
            return redirect()->route('partner.rentals')->with('info', 'Đơn thuê này đã được nộp 10% hoa hồng sàn trước đó.');
        }

        $totalRentalFee = (float) ($rental->total_rental_fee + $rental->total_driver_fee);
        $commission10 = round($totalRentalFee * 0.10);

        $requestType = $request->query('type', 'captureWallet'); // 'captureWallet' hoặc 'payWithATM'
        $methodLabel = $requestType === 'payWithATM' ? 'Thẻ ATM nội địa (Cổng MoMo Test)' : 'Ví MoMo Test';

        $transaction = PaymentTransaction::create([
            'rental_id' => $rental->id,
            'gateway' => 'momo',
            'amount' => $commission10,
            'status' => 'pending',
            'message' => "Nộp 10% hoa hồng sàn đơn thuê #{$rental->rental_code} qua {$methodLabel}",
        ]);

        $result = $momo->createPartnerCommissionPayment($rental, $transaction, $requestType);

        if (isset($result['payUrl']) && !empty($result['payUrl'])) {
            return redirect($result['payUrl']);
        }

        return redirect()->route('partner.rentals')->with('error', 'Không thể kết nối tới cổng MoMo Test: ' . ($result['message'] ?? 'Vui lòng thử lại sau.'));
    }

    // Đối tác nghiệm thu biên bản trả xe & gửi đề xuất hoàn cọc ký quỹ tới Admin sàn
    // BẮT BUỘC: Đối tác phải chuyển 10% hoa hồng sàn (qua MoMo Test hoặc chuyển khoản ngân hàng Admin TPBank)
    public function refund(Request $request, Rental $rental, \App\Services\MomoService $momo)
    {
        $partnerId = $this->getPartnerId();
        if ($rental->partner_id !== $partnerId && Auth::user()->role !== 'admin') {
            abort(403);
        }

        $totalRentalFee = (float) ($rental->total_rental_fee + $rental->total_driver_fee);
        $commission10 = round($totalRentalFee * 0.10);

        // Nếu đối tác chọn thanh toán 10% hoa hồng qua MoMo Test ngay trong form
        if ($request->input('commission_payment_method') === 'momo' && $rental->partner_commission_status !== 'paid') {
            $rental->update([
                'refund_amount' => (float) $request->refund_amount,
                'refund_holding_fee' => (float) $request->input('refund_holding_fee', 0),
                'refund_notes' => $request->refund_notes,
                'return_odo' => $request->return_odo,
                'return_fuel' => $request->return_fuel,
            ]);
            return $this->payCommissionMomo($request, $rental, $momo);
        }

        $rules = [
            'refund_amount' => 'required|numeric|min:0',
            'refund_holding_fee' => 'nullable|numeric|min:0',
            'refund_notes' => 'nullable|string|max:1000',
            'return_odo' => 'nullable|numeric|min:0',
            'return_fuel' => 'nullable|integer|min:0|max:100',
        ];

        // Nếu là xe của đối tác và chưa nộp qua MoMo, bắt buộc đối tác phải nhập mã GD ngân hàng
        if ($rental->partner_id && $commission10 > 0 && $rental->partner_commission_status !== 'paid') {
            $rules['commission_payment_proof'] = 'required|string|max:100';
            $rules['confirm_commission_paid'] = 'required|accepted';
        }

        $request->validate($rules, [
            'refund_amount.required' => 'Vui lòng nhập số tiền đề xuất hoàn cọc cho khách hàng.',
            'commission_payment_proof.required' => 'Vui lòng nhập Mã giao dịch chuyển khoản ngân hàng vào tài khoản Admin TPBank (10% hoa hồng sàn).',
            'confirm_commission_paid.required' => 'Bạn phải xác nhận đã chuyển khoản đủ 10% hoa hồng sàn trước khi hoàn tất nghiệm thu xe.',
            'confirm_commission_paid.accepted' => 'Bạn phải xác nhận đã chuyển khoản đủ 10% hoa hồng sàn trước khi hoàn tất nghiệm thu xe.',
        ]);

        $refundAmount = (float) $request->refund_amount;
        $holdingFee = (float) $request->input('refund_holding_fee', 0);
        $refundNotes = $request->refund_notes;

        $partnerName = Auth::user()->showroom_name ?: (Auth::user()->company_name ?: Auth::user()->name);

        // Cập nhật biên bản nghiệm thu xe của Đối tác và gửi đề xuất hoàn cọc sang Admin
        $updateData = [
            'refund_amount' => $refundAmount,
            'refund_holding_fee' => $holdingFee,
            'refund_notes' => $refundNotes,
            'refund_status' => 'waiting_admin', // Chờ Admin Sàn đối soát & thanh toán cọc cho khách
            'return_odo' => $request->return_odo,
            'return_fuel' => $request->return_fuel,
            'return_verified_at' => now(),
            'handover_status' => 'returned',
            'rental_status' => 'returned',
            'platform_fee' => $commission10,
            'partner_payout' => max(0, $totalRentalFee - $commission10),
        ];

        $wasPaidBefore = ($rental->partner_commission_status === 'paid');

        if ($rental->partner_id) {
            $updateData['partner_commission_fee'] = $commission10;
            $updateData['partner_commission_status'] = 'paid';
            if (!$wasPaidBefore) {
                $updateData['partner_commission_proof'] = $request->commission_payment_proof;
                $updateData['partner_commission_paid_at'] = now();
            }
        }

        $rental->update($updateData);

        // Mở lại trạng thái xe sẵn sàng cho thuê trong kho Showroom
        $rental->product?->update(['rental_status' => 'available']);

        $proofCode = $rental->partner_commission_proof ?: ($request->commission_payment_proof ?: 'MOMO_TEST_PAID');

        // Ghi nhận giao dịch nộp 10% hoa hồng sàn của đối tác nếu nộp qua chuyển khoản ngân hàng
        if ($rental->partner_id && $commission10 > 0 && !$wasPaidBefore) {
            PaymentTransaction::create([
                'rental_id' => $rental->id,
                'gateway' => 'partner_commission_10pct',
                'gateway_order_id' => 'COMM10_' . $rental->rental_code,
                'amount' => $commission10,
                'status' => 'success',
                'message' => "Đối tác Showroom ({$partnerName}) đã chuyển khoản đủ 10% hoa hồng sàn: " . number_format($commission10) . " VNĐ vào tài khoản Admin TPBank. Mã GD: {$proofCode}.",
                'paid_at' => now(),
            ]);
        }

        PaymentTransaction::create([
            'rental_id' => $rental->id,
            'gateway' => 'partner_inspection_completed',
            'gateway_order_id' => 'INSPECT_' . $rental->rental_code,
            'amount' => $refundAmount,
            'status' => 'pending',
            'message' => "Showroom đối tác ({$partnerName}) đã nghiệm thu nhận lại xe nguyên vẹn và đã nộp 10% hoa hồng sàn (" . number_format($commission10) . "đ, Mã: {$proofCode}). Đã gửi đề xuất Admin sàn chuyển cọc " . number_format($refundAmount) . "đ cho khách hàng {$rental->customer_name}. Giữ lại đối soát: " . number_format($holdingFee) . "đ." . ($refundNotes ? " Ghi chú: {$refundNotes}" : ''),
            'paid_at' => now(),
        ]);

        return redirect()->back()->with('success', '✅ Đã nộp 10% hoa hồng sàn (' . number_format($commission10) . 'đ) và hoàn tất biên bản nghiệm thu xe thành công! Mẫu xe đã sẵn sàng cho lượt thuê mới. Đề xuất hoàn cọc ' . number_format($refundAmount) . 'đ đã được chuyển sang Ban Quản Trị để chuyển khoản cho khách hàng.');
    }

    // Bảng đối soát tài chính & hoa hồng (Mua bán xe 1% & Thuê xe 10% - 90%)
    public function payouts()
    {
        $partnerId = $this->getPartnerId();
        $partnerUser = Auth::user()->role === 'partner' ? Auth::user() : \App\Models\User::find($partnerId);

        $rentals = Rental::where('partner_id', $partnerId)
            ->with(['product', 'paymentTransactions'])
            ->orderBy('id', 'desc')
            ->paginate(15);

        $paidRentals = Rental::where('partner_id', $partnerId)->where('payment_status', '!=', 'unpaid')->get();
        $totalGross = $paidRentals->sum('total_rental_fee') + $paidRentals->sum('total_driver_fee');
        $totalPlatformCommission = $paidRentals->sum('platform_fee') ?: round($totalGross * 0.10);
        $totalPartnerNet = $paidRentals->sum('partner_payout') ?: ($totalGross - $totalPlatformCommission);

        // Danh sách các hợp đồng bán xe thành công mà sàn đã giới thiệu
        $soldCarAppointments = Appointment::where('partner_id', $partnerId)
            ->where('deal_status', 'deal_won')
            ->with(['product', 'user'])
            ->orderBy('id', 'desc')
            ->get();
        $totalSalesCommission = $soldCarAppointments->sum('commission_amount');

        return view('partner.payouts', compact(
            'rentals',
            'partnerUser',
            'totalGross',
            'totalPlatformCommission',
            'totalPartnerNet',
            'soldCarAppointments',
            'totalSalesCommission'
        ));
    }
}
