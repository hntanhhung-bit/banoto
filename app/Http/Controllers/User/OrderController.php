<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\GHNService;
use App\Services\GHNOrderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // ==========================================
    // 1. CÁC VIEW HIỂN THỊ ĐƠN HÀNG & THANH TOÁN
    // ==========================================
    public function index()
    {
        $cart = session('cart', []);
        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng đang trống.');
        }

        $totalPrice = collect($cart)->sum(fn($item) => $item['price'] * $item['quantity']);
        $user = Auth::user();

        return view('checkout.index', compact('cart', 'totalPrice', 'user'));
    }

    public function orderHistory()
    {
        $orders = Order::where('user_id', Auth::id())
            ->with(['items.product'])
            ->orderByDesc('created_at')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }

    public function show(Order $order)
    {
        if ($order->user_id !== Auth::id() && (!Auth::user() || !Auth::user()->is_admin)) {
            abort(403);
        }

        $order->load(['items.product']);

        return view('orders.show', compact('order'));
    }

    public function cancel(Order $order, GHNService $ghn)
    {
        abort_unless($order->user_id === Auth::id(), 403);
        $allowedStatuses = ['pending', 'ready_to_pick', 'not_shipped'];

        if (!in_array($order->shipping_status, $allowedStatuses, true)) {
            return back()->with('error', 'Đơn hàng không còn ở trạng thái có thể hủy.');
        }

        if ($order->ghn_order_code) {
            $response = $ghn->cancelOrder([$order->ghn_order_code]);
            if (($response['code'] ?? null) !== 200) {
                return back()->with('error', 'GHN không cho phép hủy vận đơn này.');
            }
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'order_status' => 'cancelled',
                'shipping_status' => 'cancelled',
            ]);
        });

        return back()->with('success', 'Đơn hàng đã được hủy.');
    }
}
