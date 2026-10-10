<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Quản lý danh sách đơn hàng trong Admin
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items', 'partner', 'appointment.product']);

        // Tìm kiếm theo mã đơn hàng, tên khách hàng, số điện thoại, tên showroom đối tác
        if ($request->filled('keyword')) {
            $keyword = trim($request->input('keyword'));
            $query->where(function ($q) use ($keyword) {
                $q->where('order_code', 'like', "%{$keyword}%")
                    ->orWhere('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_phone', 'like', "%{$keyword}%")
                    ->orWhereHas('partner', function ($pq) use ($keyword) {
                        $pq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('showroom_name', 'like', "%{$keyword}%");
                    });
            });
        }

        // Lọc theo trạng thái đơn hàng
        if ($request->filled('order_status')) {
            $query->where('order_status', $request->input('order_status'));
        }

        // Lọc theo trạng thái thanh toán
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        // Lọc theo nguồn đơn (Chốt từ Đối tác Showroom / Mua trực tiếp)
        if ($request->filled('source')) {
            if ($request->input('source') === 'partner') {
                $query->whereNotNull('partner_id');
            } elseif ($request->input('source') === 'direct') {
                $query->whereNull('partner_id');
            }
        }

        $orders = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    // Xem chi tiết đơn hàng (Admin)
    public function show(Order $order)
    {
        $order->load(['user', 'items.product', 'paymentTransactions', 'partner', 'appointment.product']);

        $partnerId = $order->partner_id ?: $order->appointment?->partner_id;
        $otherPartnerAppointments = collect();
        $otherPartnerOrders = collect();

        if ($partnerId) {
            // Lấy các lịch hẹn khác của chính partner này (bao gồm trạng thái tiếp đón và kết quả bán xe)
            $otherPartnerAppointments = \App\Models\Appointment::where('partner_id', $partnerId)
                ->where('id', '!=', $order->appointment_id)
                ->with(['product', 'user'])
                ->orderBy('id', 'desc')
                ->get();

            // Lấy các đơn bán xe khác của partner này
            $otherPartnerOrders = Order::where('partner_id', $partnerId)
                ->where('id', '!=', $order->id)
                ->with(['items', 'user'])
                ->orderBy('id', 'desc')
                ->get();
        }

        return view('admin.orders.show', compact('order', 'otherPartnerAppointments', 'otherPartnerOrders'));
    }

    // Cập nhật trạng thái đơn hàng và thanh toán
    public function updateStatus(Request $request, Order $order)
    {
        $request->validate([
            'order_status' => 'required|in:pending,confirmed,shipping,completed,cancelled',
            'payment_status' => 'required|in:unpaid,paid',
        ]);

        $order->update([
            'order_status' => $request->order_status,
            'payment_status' => $request->payment_status,
        ]);

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái đơn hàng #' . $order->order_code . ' thành công!');
    }

    // Cập nhật trạng thái hàng loạt cho nhiều đơn hàng cùng lúc
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'order_status' => 'nullable|in:pending,confirmed,shipping,completed,cancelled',
            'payment_status' => 'nullable|in:unpaid,paid',
        ]);

        $updateData = [];
        if ($request->filled('order_status')) {
            $updateData['order_status'] = $request->order_status;
        }
        if ($request->filled('payment_status')) {
            $updateData['payment_status'] = $request->payment_status;
        }

        if (empty($updateData)) {
            return redirect()->back()->with('error', 'Vui lòng chọn ít nhất một trạng thái để cập nhật hàng loạt!');
        }

        Order::whereIn('id', $request->order_ids)->update($updateData);

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái thành công cho ' . count($request->order_ids) . ' đơn hàng được chọn!');
    }

    // Xóa đơn hàng
    public function destroy(Order $order)
    {
        $code = $order->order_code;
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Đã xóa đơn hàng #' . $code . ' khỏi hệ thống.');
    }
}
