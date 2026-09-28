<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Quản lý danh sách đơn hàng trong Admin
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items']);

        // Tìm kiếm theo mã đơn hàng, tên khách hàng, số điện thoại
        if ($request->filled('keyword')) {
            $keyword = trim($request->input('keyword'));
            $query->where(function ($q) use ($keyword) {
                $q->where('order_code', 'like', "%{$keyword}%")
                  ->orWhere('customer_name', 'like', "%{$keyword}%")
                  ->orWhere('customer_phone', 'like', "%{$keyword}%");
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

        $orders = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    // Xem chi tiết đơn hàng (Admin)
    public function show(Order $order)
    {
        $order->load(['user', 'items', 'paymentTransactions']);
        return view('admin.orders.show', compact('order'));
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
