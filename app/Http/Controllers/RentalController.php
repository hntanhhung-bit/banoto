<?php

namespace App\Http\Controllers;

use App\Models\Rental;
use App\Models\Product;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class RentalController extends Controller
{
    // Xử lý tạo đơn thuê xe (Tự lái hoặc Kèm tài xế)
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'rental_type' => 'required|in:self_drive,with_driver',
            'customer_name' => 'required|string|max:255',
            'customer_phone' => ['required', 'regex:/^[0-9]{10}$/'],
            'customer_email' => 'nullable|email|max:255',
            'customer_address' => 'nullable|string|max:500',
            'destination_address' => 'nullable|string|max:500',
            'pickup_lat' => 'nullable|string|max:50',
            'pickup_lng' => 'nullable|string|max:50',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'pickup_location' => 'required|in:at_showroom,delivery_home',
            'payment_method' => 'required|in:bank_transfer,cod,momo,sepay',
            'refund_bank_name' => 'nullable|string|max:100',
            'refund_account_number' => 'nullable|string|max:50',
            'refund_account_holder' => 'nullable|string|max:255',
            'note' => 'nullable|string|max:1000',
        ], [
            'rental_type.required' => 'Vui lòng chọn hình thức thuê xe.',
            'customer_name.required' => 'Vui lòng nhập họ và tên người thuê xe.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại liên hệ.',
            'customer_phone.regex' => 'Số điện thoại phải đúng 10 chữ số.',
            'start_date.required' => 'Vui lòng chọn ngày bắt đầu thuê.',
            'start_date.after_or_equal' => 'Ngày bắt đầu thuê phải từ hôm nay trở đi.',
            'end_date.required' => 'Vui lòng chọn ngày kết thúc thuê.',
            'end_date.after_or_equal' => 'Ngày kết thúc thuê phải sau hoặc cùng ngày bắt đầu.',
            'pickup_location.required' => 'Vui lòng chọn hình thức giao/nhận xe.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
        ]);

        // Kiểm tra xe có đang rảnh không
        if ($product->rental_status === 'rented') {
            return redirect()->back()->with('error', "Mẫu xe '{$product->name}' hiện tại đang có khách thuê. Vui lòng chọn mẫu xe khác hoặc đặt lịch hẹn xem xe!");
        }

        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate);
        if ($totalDays <= 0) {
            $totalDays = 1; // Thuê trong ngày tính 1 ngày
        }

        $rentalType = $request->input('rental_type', 'self_drive');
        $selectedColor = $request->input('selected_color', 'Trắng ngọc trai');

        // Tính giá thuê theo màu sắc đã chọn
        $dailyPrice = $product->rent_price_per_day ?: 800000;
        $colorVariants = $product->getColorVariants();
        $matchedColor = $colorVariants->firstWhere('color_name', $selectedColor);
        if ($matchedColor) {
            $dailyPrice = $matchedColor->rent_price_per_day;
        }

        $totalRentalFee = $totalDays * $dailyPrice;

        // Phí tài xế nếu chọn thuê có tài xế
        $driverFeePerDay = 0;
        $totalDriverFee = 0;
        if ($rentalType === 'with_driver') {
            $driverFeePerDay = $product->driver_price_per_day ?: 500000;
            $totalDriverFee = $totalDays * $driverFeePerDay;
        }

        $depositAmount = $product->rental_deposit ?: 5000000;
        $totalAmount = $totalRentalFee + $totalDriverFee + $depositAmount;

        // Tính toán hoa hồng sàn 10% và chi trả đối tác 90% trên tổng phí thuê
        $totalServiceFee = $totalRentalFee + $totalDriverFee;
        $platformFee = round($totalServiceFee * 0.10);
        $partnerPayout = $totalServiceFee - $platformFee;

        $prefix = $rentalType === 'with_driver' ? 'TAIXE' : 'TULAI';
        $rentalCode = $prefix . date('Ymd') . rand(1000, 9999);
        $partnerShowroom = $product->partner_showroom;

        $note = $request->note ? trim($request->note) : '';
        $note .= ($note ? "\n" : '') . "[Dịch vụ Bên Thứ Ba]: Đặt thuê xe hộ có bảo lãnh cọc qua đối tác {$partnerShowroom->name}. Nền tảng chịu trách nhiệm giám sát và bảo vệ quyền lợi khách hàng 100%.";

        $pickupAddress = $request->pickup_location === 'delivery_home'
            ? ($request->customer_address ?: 'Giao xe tận nơi theo địa chỉ khách hàng')
            : "Nhận tại Showroom/Nhà xe đối tác: {$partnerShowroom->name} ({$partnerShowroom->address})";

        // Ban đầu chưa thanh toán cọc -> Chưa cấp mã đối chiếu bàn giao xe
        $handoverCode = null;

        $rental = Rental::create([
            'user_id' => Auth::id(),
            'partner_id' => $product->partner_id,
            'product_id' => $product->id,
            'selected_color' => $selectedColor,
            'rental_code' => $rentalCode,
            'handover_code' => $handoverCode,
            'handover_status' => 'pending',
            'rental_type' => $rentalType,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email ?: (Auth::user() ? Auth::user()->email : null),
            'customer_address' => $pickupAddress,
            'destination_address' => $request->destination_address,
            'pickup_lat' => $request->pickup_lat,
            'pickup_lng' => $request->pickup_lng,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'daily_price' => $dailyPrice,
            'driver_fee_per_day' => $driverFeePerDay,
            'total_rental_fee' => $totalRentalFee,
            'total_driver_fee' => $totalDriverFee,
            'deposit_amount' => $depositAmount,
            'total_amount' => $totalAmount,
            'platform_fee' => $platformFee,
            'partner_payout' => $partnerPayout,
            'pickup_location' => $request->pickup_location,
            'payment_method' => $request->payment_method,
            'payment_status' => 'unpaid',
            'refund_bank_name' => $request->refund_bank_name,
            'refund_account_number' => $request->refund_account_number,
            'refund_account_holder' => $request->refund_account_holder,
            'refund_status' => 'holding',
            'refund_amount' => $depositAmount,
            'rental_status' => 'pending',
            'note' => $note,
        ]);

        if ($rental->payment_method === 'momo') {
            return redirect()->route('rentals.momo.pay', $rental->id);
        }

        if ($rental->payment_method === 'sepay') {
            return redirect()->route('rentals.sepay.pay', $rental->id);
        }

        // Ghi nhận nhật ký cho COD
        PaymentTransaction::create([
            'rental_id' => $rental->id,
            'gateway' => 'cod',
            'gateway_order_id' => 'RENTAL_' . $rental->rental_code,
            'amount' => $rental->deposit_amount,
            'status' => 'pending',
            'message' => 'Thanh toán cọc trực tiếp khi nhận bàn giao xe',
        ]);

        $serviceTitle = $rentalType === 'with_driver' ? 'Thuê xe có tài xế hộ' : 'Thuê xe tự lái hộ';
        return redirect()->route('rentals.success', $rental->rental_code)->with('success', "Đăng ký dịch vụ {$serviceTitle} thành công! Nền tảng Bên thứ ba đang bảo lãnh đơn thuê và điều phối xe từ Nhà xe đối tác {$partnerShowroom->name}.");
    }

    // Hiển thị thông báo hợp đồng thuê xe & thanh toán cọc MoMo ATM
    public function success($rental_code)
    {
        $rental = Rental::with('product')->where('rental_code', $rental_code)->firstOrFail();

        return view('rentals.success', compact('rental'));
    }

    // Danh sách xe tôi đang thuê
    public function myRentals()
    {
        $rentals = Rental::with('product')
            ->where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('rentals.my', compact('rentals'));
    }

    // Xem Phiếu Đơn Thuê Xe & Biên Bản Bàn Giao Điện Tử (Kèm Mã Bảo Mật Đối Chiếu)
    public function voucher(Rental $rental)
    {
        // Cho phép chủ đơn, đối tác sở hữu xe, hoặc Admin xem
        $partnerId = Auth::user()->role === 'partner' ? Auth::id() : null;
        if ($rental->user_id !== Auth::id() && Auth::user()->role !== 'admin' && $rental->partner_id !== $partnerId) {
            abort(403, 'Bạn không có quyền truy cập phiếu đơn thuê xe này.');
        }

        $rental->load(['product.category', 'partner', 'paymentTransactions']);

        return view('rentals.voucher', compact('rental'));
    }

    // Hủy đơn thuê xe
    public function cancel(Rental $rental)
    {
        if ($rental->user_id !== Auth::id()) {
            abort(403);
        }

        if ($rental->rental_status === 'pending') {
            $rental->update(['rental_status' => 'cancelled']);
            return redirect()->back()->with('success', 'Đã hủy yêu cầu thuê xe thành công.');
        }

        return redirect()->back()->with('error', 'Không thể hủy đơn thuê xe đã được duyệt hoặc đang diễn ra.');
    }
}
