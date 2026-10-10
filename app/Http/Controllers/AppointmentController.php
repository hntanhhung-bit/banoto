<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AppointmentController extends Controller
{
    // Xử lý gửi yêu cầu đặt lịch xem xe / lái thử
    public function store(Request $request, Product $product)
    {
        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => ['required', 'regex:/^[0-9]{10}$/'],
            'customer_email' => 'nullable|email|max:255',
            'appointment_date' => 'required|date|after_or_equal:today',
            'appointment_time' => 'required|string|max:100',
            'location_type' => 'required|in:at_showroom,at_home',
            'address' => 'nullable|string|max:500',
            'note' => 'nullable|string|max:1000',
        ], [
            'customer_name.required' => 'Vui lòng nhập họ và tên của bạn.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại liên hệ.',
            'customer_phone.regex' => 'Số điện thoại phải đủ 10 chữ số.',
            'appointment_date.required' => 'Vui lòng chọn ngày xem xe.',
            'appointment_date.after_or_equal' => 'Ngày hẹn xem xe phải từ hôm nay trở đi.',
            'appointment_time.required' => 'Vui lòng chọn khung giờ hẹn.',
        ]);

        // Kiểm tra xe đã bán chưa
        if ($product->isSold()) {
            return redirect()->back()->with('error', "Rất tiếc! Mẫu xe '{$product->name}' đã được bán thành công. Không thể đặt lịch xem xe hoặc lái thử nữa.");
        }

        // Kiểm tra xe có đang bận phục vụ khách hàng không
        if ($product->isServing()) {
            return redirect()->back()->with('error', "Rất tiếc! Mẫu xe '{$product->name}' hiện tại đang trong chuyến phục vụ khách hàng. Không thể đặt lịch xem xe hoặc lái thử lúc này.");
        }

        $appointmentCode = 'HEN' . date('Ymd') . rand(1000, 9999);
        $partnerShowroom = $product->partner_showroom;

        $note = $request->note ? trim($request->note) : '';
        if ($request->boolean('with_inspector')) {
            $note .= ($note ? "\n" : '') . "[Dịch vụ Bên Thứ Ba]: Đăng ký cử Chuyên viên Kỹ thuật độc lập đi cùng kiểm tra xe hộ (Miễn phí).";
        }

        $appointmentAddress = $request->location_type === 'at_home' 
            ? ($request->address ?: 'Tại địa chỉ khách hàng cung cấp')
            : "{$partnerShowroom->name} ({$partnerShowroom->address})";

        $appointment = Appointment::create([
            'user_id' => Auth::id(),
            'partner_id' => $product->partner_id,
            'product_id' => $product->id,
            'selected_color' => $request->input('selected_color', $product->color ?: 'Trắng ngọc trai'),
            'appointment_code' => $appointmentCode,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email ?: (Auth::user() ? Auth::user()->email : null),
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'location_type' => $request->location_type,
            'address' => $appointmentAddress,
            'status' => 'pending',
            'note' => $note,
        ]);

        return redirect()->route('appointments.my')->with('success', "Yêu cầu ĐẶT HỘ LỊCH XEM XE cho mẫu '{$product->name}' đã được tiếp nhận thành công! Mã hẹn: #{$appointmentCode}. Nền tảng Bên thứ ba sẽ liên hệ '{$partnerShowroom->name}' để giữ lịch và cử chuyên viên đồng hành cùng bạn.");
    }

    // Danh sách lịch hẹn của tôi
    public function myAppointments()
    {
        $appointments = Appointment::with('product')
            ->where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('appointments.my', compact('appointments'));
    }

    // Hủy lịch hẹn
    public function cancel(Appointment $appointment)
    {
        if ($appointment->user_id !== Auth::id()) {
            abort(403);
        }

        if ($appointment->status === 'pending' || $appointment->status === 'confirmed') {
            $appointment->update(['status' => 'cancelled']);
            return redirect()->back()->with('success', 'Đã hủy lịch hẹn xem xe thành công.');
        }

        return redirect()->back()->with('error', 'Không thể hủy lịch hẹn ở trạng thái hiện tại.');
    }
}
