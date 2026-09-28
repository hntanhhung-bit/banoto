<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckoutController extends Controller
{
    // Hiển thị trang thanh toán / đặt cọc
    public function showCheckout()
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng của bạn đang trống! Vui lòng chọn xe trước khi thanh toán.');
        }

        $total = 0;
        foreach ($cart as $item) {
            $total += $item['price'] * $item['quantity'];
        }

        $user = Auth::user();

        return view('checkout.index', compact('cart', 'total', 'user'));
    }

    // Xử lý lưu đơn hàng & thanh toán
    public function processCheckout(Request $request, \App\Services\GHNOrderService $ghnOrderService)
    {
        $cart = session()->get('cart', []);

        if (empty($cart)) {
            return redirect()->route('cart.index')->with('error', 'Giỏ hàng trống!');
        }

        $deliveryType = $request->input('delivery_type', 'showroom');

        $request->validate([
            'customer_name' => 'required|string|max:255',
            'customer_phone' => ['required', 'regex:/^[0-9]{10}$/'],
            'customer_email' => 'nullable|email|max:255',
            'delivery_type' => 'required|in:showroom,garage_delivery',
            'customer_address' => $deliveryType === 'garage_delivery' ? 'required|string|max:500' : 'nullable|string|max:500',
            'province_id' => $deliveryType === 'garage_delivery' ? 'nullable|integer' : 'nullable',
            'to_district_id' => $deliveryType === 'garage_delivery' ? 'nullable|integer' : 'nullable',
            'to_ward_code' => $deliveryType === 'garage_delivery' ? 'nullable|string' : 'nullable',
            'shipping_fee' => 'nullable|numeric|min:0',
            'payment_method' => 'required|in:cod,bank_transfer,momo,sepay',
            'note' => 'nullable|string|max:1000',
        ], [
            'customer_name.required' => 'Vui lòng nhập họ và tên người nhận xe.',
            'customer_phone.required' => 'Vui lòng nhập số điện thoại liên hệ.',
            'customer_phone.regex' => 'Số điện thoại không hợp lệ! Vui lòng nhập đúng 10 chữ số (Ví dụ: 0987654321).',
            'customer_address.required' => 'Vui lòng nhập địa chỉ để Gara bàn giao xe tận nơi.',
            'payment_method.required' => 'Vui lòng chọn phương thức thanh toán.',
        ]);

        // KIỂM TRA TỒN KHO TRƯỚC KHI TẠO ĐƠN
        foreach ($cart as $productId => $item) {
            $product = \App\Models\Product::find($productId);
            if (!$product) {
                return redirect()->route('cart.index')->with('error', "Mẫu xe '{$item['name']}' không còn tồn tại trên hệ thống!");
            }
            if ($product->quantity <= 0) {
                return redirect()->route('cart.index')->with('error', "Mẫu xe '{$product->name}' hiện tại đã hết hàng trong kho. Vui lòng chọn xe khác!");
            }
            if ($item['quantity'] > $product->quantity) {
                return redirect()->route('cart.index')->with('error', "Mẫu xe '{$product->name}' trong kho chỉ còn {$product->quantity} xe, không đủ số lượng đặt mua ({$item['quantity']} xe). Vui lòng cập nhật lại số lượng!");
            }
        }

        $subtotal = 0;
        foreach ($cart as $item) {
            $subtotal += $item['price'] * $item['quantity'];
        }

        // Nếu nhận tại showroom thì phí bàn giao = 0, nếu Gara mang xe tận nơi thì tính cước
        $shippingFee = ($deliveryType === 'garage_delivery') ? (int) ($request->shipping_fee ?? 0) : 0;
        $total = $subtotal + $shippingFee;

        // Tạo mã đơn hàng độc nhất: OTO + NămThángNgày + 4 số ngẫu nhiên
        $orderCode = 'OTO' . date('Ymd') . rand(1000, 9999);
        $finalAddress = ($deliveryType === 'showroom') 
            ? 'Nhận trực tiếp tại Showroom Gara (Trụ sở chính)' 
            : $request->customer_address;

        // 1. Tạo đơn hàng (Order)
        $order = Order::create([
            'user_id' => Auth::id(),
            'order_code' => $orderCode,
            'customer_name' => $request->customer_name,
            'customer_phone' => $request->customer_phone,
            'customer_email' => $request->customer_email ?: (Auth::user() ? Auth::user()->email : null),
            'customer_address' => $finalAddress,
            'total_amount' => $total,
            'ghn_total_fee' => $shippingFee,
            'delivery_type' => $deliveryType,
            'to_district_id' => $deliveryType === 'garage_delivery' ? $request->to_district_id : null,
            'to_ward_code' => $deliveryType === 'garage_delivery' ? $request->to_ward_code : null,
            'payment_method' => $request->payment_method,
            'payment_status' => 'unpaid',
            'order_status' => 'pending',
            'shipping_status' => ($deliveryType === 'showroom') ? 'ready_at_showroom' : 'pending',
            'note' => $request->note,
        ]);

        // 2. Tạo chi tiết đơn hàng (OrderItems) & Trừ tồn kho
        foreach ($cart as $productId => $item) {
            $categoryName = 'N/A';
            if (isset($item['category'])) {
                $categoryName = is_array($item['category']) ? ($item['category']['name'] ?? 'N/A') : $item['category'];
            }

            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'product_name' => $item['name'],
                'price' => $item['price'],
                'quantity' => $item['quantity'],
                'color' => $item['color'] ?? null,
                'category_name' => $categoryName,
                'image' => $item['image'] ?? null,
            ]);

            // Trừ số lượng tồn kho trong database
            $product = \App\Models\Product::find($productId);
            if ($product) {
                $product->decrement('quantity', (int) $item['quantity']);
            }
        }

        // 3. Phân luồng Thanh toán theo kiến trúc tài liệu
        if ($request->payment_method === 'momo') {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway' => 'momo',
                'amount' => $order->total_amount,
                'status' => 'pending',
            ]);

            session()->forget('cart');
            return redirect()->route('orders.momo.start', $order);
        }

        if ($request->payment_method === 'sepay') {
            PaymentTransaction::create([
                'order_id' => $order->id,
                'gateway' => 'sepay',
                'gateway_order_id' => 'SEPAY_' . $order->order_code,
                'amount' => $order->total_amount,
                'status' => 'pending',
                'message' => 'Chờ chuyển khoản quét mã QR SePay (TPBank)',
            ]);

            session()->forget('cart');
            return redirect()->route('orders.sepay.pay', $order->id);
        }

        // Nhánh COD hoặc Chuyển khoản QR: Tạo bản ghi transaction
        PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => $request->payment_method,
            'amount' => $order->total_amount,
            'status' => 'pending',
            'message' => ($request->payment_method === 'bank_transfer') 
                ? 'Thanh toán qua quét mã VietQR' 
                : 'Thanh toán tiền mặt/cà thẻ khi nhận bàn giao xe',
        ]);

        // Xử lý vận chuyển bàn giao xe của Gara
        if ($deliveryType === 'garage_delivery') {
            $deliveryCode = 'BGX-' . date('Ymd') . '-' . rand(1000, 9999);
            if ($order->to_district_id && $order->to_ward_code) {
                try {
                    $ghnResponse = $ghnOrderService->create($order, false);
                    if (isset($ghnResponse['code']) && $ghnResponse['code'] == 200 && !empty($ghnResponse['data']['order_code'])) {
                        $deliveryCode = $ghnResponse['data']['order_code'];
                    }
                } catch (\Exception $e) {
                    \Illuminate\Support\Facades\Log::warning('Gara delivery dispatch: ' . $e->getMessage());
                }
            }

            $order->update([
                'ghn_order_code' => $deliveryCode,
                'shipping_status' => 'ready_to_deliver',
            ]);
        } else {
            $order->update([
                'ghn_order_code' => 'SHOWROOM-' . $order->order_code,
                'shipping_status' => 'ready_at_showroom',
            ]);
        }

        // Xóa giỏ hàng sau khi đặt thành công
        session()->forget('cart');

        return redirect()->route('checkout.success', $order->order_code)->with('success', 'Đặt hàng thành công!');
    }

    // Hiển thị trang thông báo đặt hàng thành công & mã QR thanh toán
    public function success($order_code)
    {
        $order = Order::with('items')->where('order_code', $order_code)->firstOrFail();

        // Tạo link VietQR động nếu phương thức thanh toán là chuyển khoản ngân hàng
        $qrUrl = null;
        if ($order->payment_method === 'bank_transfer') {
            $bankId = 'TPB'; // Ngân hàng TPBank
            $accountNo = '12325072005';
            $accountName = 'HOANG NGOC THI';
            $amount = (int) $order->total_amount;
            $addInfo = 'THANH TOAN ' . $order->order_code;
            $qrUrl = "https://img.vietqr.io/image/{$bankId}-{$accountNo}-compact2.png?amount={$amount}&addInfo=" . urlencode($addInfo) . "&accountName=" . urlencode($accountName);
        }

        return view('checkout.success', compact('order', 'qrUrl'));
    }

    // Lịch sử đơn hàng của người dùng
    public function myOrders()
    {
        $orders = Order::with('items')
            ->where('user_id', Auth::id())
            ->orderBy('id', 'desc')
            ->paginate(10);

        return view('orders.index', compact('orders'));
    }
}
