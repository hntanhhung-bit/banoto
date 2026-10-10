<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\GHNOrderService;
use App\Services\MomoService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MomoController extends Controller
{
    /**
     * Bắt đầu thanh toán MoMo cho đơn hàng mới
     */
    public function start(Order $order, MomoService $momo)
    {
        if (Auth::check() && $order->user_id && $order->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền thanh toán cho đơn hàng này.');
        }

        return $this->redirectToMomo($order, $this->newTransaction($order), $momo);
    }

    /**
     * Thanh toán lại cho đơn hàng cũ (Pay Again)
     */
    public function payAgain(Order $order, MomoService $momo)
    {
        if (Auth::check() && $order->user_id && $order->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền thanh toán lại cho đơn hàng này.');
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('orders.index')->with('warning', 'Đơn hàng này đã được thanh toán thành công trước đó.');
        }

        return $this->redirectToMomo($order, $this->newTransaction($order), $momo);
    }

    /**
     * Callback khi khách hàng hoàn tất hoặc hủy thao tác từ trang MoMo
     */
    public function callback(Request $request, GHNOrderService $ghnOrders, MomoService $momo)
    {
        Log::info('MoMo callback received', [
            'payload' => $request->except('signature'),
            'has_signature' => $request->has('signature'),
        ]);

        // 1. Kiểm tra nếu là đối tác nộp 10% hoa hồng sàn
        $commissionRentalId = $momo->rentalCommissionId($request->all());
        if ($commissionRentalId) {
            $rental = \App\Models\Rental::find($commissionRentalId);
            if (!$momo->isValidSuccessfulResponse($request->all())) {
                if ($momo->isValidResponse($request->all())) {
                    $this->markFailed($request->all(), $momo);
                }
                return redirect()->route('partner.rentals')->with('warning', 'Giao dịch nộp 10% hoa hồng sàn qua MoMo Test chưa hoàn tất hoặc bị hủy. Vui lòng thanh toán lại hoặc chuyển khoản ngân hàng.');
            }

            $this->completeCommissionPayment($request->all(), $momo);
            $rental?->refresh();
            return redirect()->route('partner.rentals')->with('success', '✅ Đã nộp 10% hoa hồng sàn (' . number_format($rental->partner_commission_fee) . ' VNĐ) qua Cổng MoMo Test thành công! Bạn có thể mở biên bản nghiệm thu xe để hoàn tất.');
        }

        // 1b. Kiểm tra nếu là đối tác nộp 1% hoa hồng môi giới bán xe (Appointment)
        $commissionAppId = $momo->appointmentCommissionId($request->all());
        if ($commissionAppId) {
            $appointment = \App\Models\Appointment::find($commissionAppId);
            if (!$momo->isValidSuccessfulResponse($request->all())) {
                if ($momo->isValidResponse($request->all())) {
                    $this->markFailed($request->all(), $momo);
                }
                return redirect()->route('partner.appointments')->with('warning', 'Giao dịch nộp 1% hoa hồng môi giới bán xe qua MoMo Test chưa hoàn tất hoặc bị hủy. Vui lòng thử lại.');
            }

            $this->completeAppointmentCommissionPayment($request->all(), $momo);
            $appointment?->refresh();
            return redirect()->route('partner.appointments')->with('success', '✅ Đã nộp 1% hoa hồng Sàn (' . number_format($appointment->commission_amount) . ' VNĐ) qua Cổng MoMo Test thành công! Lịch hẹn đã chốt mua và khóa trạng thái.');
        }

        // 2. Kiểm tra xem đây là giao dịch thuê xe hay giao dịch mua xe
        $rentalId = $momo->rentalId($request->all());
        if ($rentalId) {
            $rental = \App\Models\Rental::find($rentalId);
            if (!$momo->isValidSuccessfulResponse($request->all())) {
                if ($momo->isValidResponse($request->all())) {
                    $this->markFailed($request->all(), $momo);
                }
                $resultCode = (int) ($request->input('resultCode') ?? -1);
                $redirectUrl = $rental ? route('rentals.success', $rental->rental_code) : route('rentals.my');

                if (in_array($resultCode, [7002, 9000, 1000])) {
                    return redirect($redirectUrl)->with('info', 'Đơn thuê xe #' . ($rental ? $rental->rental_code : '') . ' đã được ghi nhận thành công! Giao dịch đang ở trạng thái: Đang chờ xử lý (Chưa nhận được tiền cọc). Quý khách có thể bấm nút "Thanh toán cọc ngay qua Ví MoMo" bên dưới để hoàn tất.');
                }

                return redirect($redirectUrl)->with('warning', 'Đơn thuê xe #' . ($rental ? $rental->rental_code : '') . ' đã được lưu lại thành công ở trạng thái: Chưa nhận được tiền cọc (Chưa cấp mã nhận xe). Quý khách có thể bấm nút "Thanh toán cọc ngay qua Ví MoMo" bên dưới để hoàn tất bất cứ lúc nào.');
            }

            $this->completeRentalPayment($request->all(), $momo);
            $redirectUrl = $rental ? route('rentals.success', $rental->rental_code) : route('rentals.my');
            return redirect($redirectUrl)->with('success', 'Thanh toán tiền cọc thuê xe qua Ví MoMo thành công! Mã bảo mật đối chiếu nhận xe đã được kích hoạt.');
        }

        if (!$momo->isValidSuccessfulResponse($request->all())) {
            Log::warning('MoMo callback rejected or failed', [
                'result_code' => $request->input('resultCode'),
                'order_id' => $request->input('orderId'),
                'signature_valid' => $momo->isValidResponse($request->all()),
            ]);

            if ($momo->isValidResponse($request->all())) {
                $this->markFailed($request->all(), $momo);
            }

            $orderId = $momo->orderId($request->all());
            $order = $orderId ? Order::find($orderId) : null;
            $resultCode = (int) ($request->input('resultCode') ?? -1);

            $message = in_array($resultCode, [7002, 9000, 1000])
                ? 'Đơn đặt xe #' . ($order ? $order->order_code : '') . ' đã được ghi nhận thành công! Giao dịch đang chờ xử lý (Chưa nhận được tiền). Quý khách có thể bấm "Thanh toán lại" bất cứ lúc nào.'
                : 'Đơn đặt xe #' . ($order ? $order->order_code : '') . ' đã được lưu lại ở trạng thái: Chưa nhận được tiền. Quý khách có thể bấm "Thanh toán lại" bên dưới để hoàn tất giao dịch.';

            if ($order) {
                return redirect()->route('checkout.success', $order->order_code)->with('warning', $message);
            }

            return redirect()->route('orders.index')->with('warning', $message);
        }

        $result = $this->completePayment($request->all(), $momo, $ghnOrders);
        session()->forget('cart'); // Xóa sạch giỏ hàng khi thanh toán thành công
        $orderId = $momo->orderId($request->all());
        $order = $orderId ? Order::find($orderId) : null;
        $orderCode = $order ? $order->order_code : '';

        $message = 'Thanh toán MoMo thành công! Đơn hàng đã chuyển sang trạng thái Đã thanh toán và Gara đang chuẩn bị bàn giao xe.';

        if ($orderCode) {
            return redirect()->route('checkout.success', $orderCode)->with('success', $message);
        }

        return redirect()->route('orders.index')->with('success', $message);
    }

    /**
     * Webhook IPN nhận thông báo giao dịch trực tiếp từ MoMo server-to-server
     */
    public function ipn(Request $request, GHNOrderService $ghnOrders, MomoService $momo)
    {
        Log::info('MoMo IPN received', [
            'payload' => $request->except('signature'),
            'has_signature' => $request->has('signature'),
        ]);

        // Kiểm tra chữ ký bảo mật từ MoMo, chặn ngay lập tức nếu chữ ký không hợp lệ
        if (!$momo->isValidResponse($request->all())) {
            Log::warning('MoMo IPN signature verification failed', [
                'order_id' => $request->input('orderId'),
                'ip' => $request->ip()
            ]);
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        // 1. Kiểm tra nếu là đối tác nộp 10% hoa hồng sàn
        $commissionRentalId = $momo->rentalCommissionId($request->all());
        if ($commissionRentalId) {
            if ($momo->isSuccessful($request->all())) {
                $this->completeCommissionPayment($request->all(), $momo);
            } else {
                $this->markFailed($request->all(), $momo);
            }
            return response()->json(['message' => 'Received']);
        }

        // 1b. Kiểm tra nếu là đối tác nộp 1% hoa hồng bán xe (Appointment)
        $commissionAppId = $momo->appointmentCommissionId($request->all());
        if ($commissionAppId) {
            if ($momo->isSuccessful($request->all())) {
                $this->completeAppointmentCommissionPayment($request->all(), $momo);
            } else {
                $this->markFailed($request->all(), $momo);
            }
            return response()->json(['message' => 'Received']);
        }

        // 2. Kiểm tra nếu là khách hàng cọc thuê xe
        $rentalId = $momo->rentalId($request->all());
        if ($rentalId) {
            if ($momo->isSuccessful($request->all())) {
                $this->completeRentalPayment($request->all(), $momo);
            } else {
                $this->markFailed($request->all(), $momo);
            }
            return response()->json(['message' => 'Received']);
        }

        // 3. Mặc định là đơn hàng mua xe
        if ($momo->isSuccessful($request->all())) {
            $this->completePayment($request->all(), $momo, $ghnOrders);
        } else {
            $this->markFailed($request->all(), $momo);
        }

        return response()->json(['message' => 'Received']);
    }

    /**
     * Khởi tạo bản ghi transaction mới cho mỗi lần bấm thanh toán
     */
    private function newTransaction(Order $order): PaymentTransaction
    {
        return PaymentTransaction::create([
            'order_id' => $order->id,
            'gateway' => 'momo',
            'amount' => $order->total_amount ?? $order->total_price ?? 0,
            'status' => 'pending',
        ]);
    }

    /**
     * Gọi MomoService tạo link và chuyển hướng khách
     */
    private function redirectToMomo(Order $order, PaymentTransaction $transaction, MomoService $momo)
    {
        $result = $momo->createPayment($order, $transaction);

        if (isset($result['payUrl']) && !empty($result['payUrl'])) {
            return redirect($result['payUrl']);
        }

        return redirect()->route('orders.index')->with('error', 'Không thể kết nối đến cổng thanh toán MoMo: ' . ($result['message'] ?? 'Vui lòng thử lại sau.'));
    }

    /**
     * Xác nhận thanh toán thành công và kích hoạt quy trình bàn giao xe của Gara
     */
    private function completePayment(array $payload, MomoService $momo, ?GHNOrderService $ghnOrders = null): string
    {
        $result = DB::transaction(function () use ($payload, $momo) {
            $transaction = PaymentTransaction::where('gateway', 'momo')
                ->where('gateway_order_id', $payload['orderId'] ?? '')
                ->lockForUpdate()
                ->first();

            if (!$transaction) {
                return 'invalid';
            }

            $order = Order::lockForUpdate()->find($transaction->order_id);
            if (!$order) {
                return 'invalid';
            }

            if ($order->payment_status === 'paid') {
                return 'already_paid';
            }

            // Kiểm tra khớp số tiền
            if ((int) $transaction->amount !== (int) ($payload['amount'] ?? 0)) {
                $momo->markFailed($transaction, $payload);
                return 'invalid_amount';
            }

            // Cập nhật trạng thái thanh toán đơn hàng
            $order->update([
                'payment_status' => 'paid',
                'order_status' => 'confirmed',
                'shipping_status' => 'processing', // Gara bắt đầu kiểm tra và chuẩn bị xe
            ]);

            $momo->markPaid($transaction, $payload);

            return ['success', $order->id];
        });

        if (!is_array($result)) {
            return (string) $result;
        }

        $order = Order::with('items.product')->find($result[1]);

        // Tạo mã điều phối bàn giao xe của Gara (hoặc GHN nếu có yêu cầu)
        $deliveryCode = 'BGX-' . date('Ymd') . '-' . rand(1000, 9999);

        // Nếu khách chọn giao xe tận nơi và có thông tin địa bàn, đồng thời thử tạo vận đơn
        if ($order->delivery_type === 'garage_delivery' || $order->to_district_id) {
            if ($ghnOrders && $order->to_district_id && $order->to_ward_code) {
                try {
                    $response = $ghnOrders->create($order, true);
                    if (isset($response['code']) && $response['code'] === 200 && !empty($response['data']['order_code'])) {
                        $deliveryCode = $response['data']['order_code'];
                    }
                } catch (\Exception $e) {
                    Log::warning('GHN dispatch skipped, using internal Garage delivery code: ' . $e->getMessage());
                }
            }

            $order->update([
                'ghn_order_code' => $deliveryCode,
                'shipping_status' => 'ready_to_deliver', // Gara đã sẵn sàng mang xe giao tận nơi
            ]);
        } else {
            // Khách nhận tại showroom
            $order->update([
                'ghn_order_code' => 'SHOWROOM-' . $order->order_code,
                'shipping_status' => 'ready_at_showroom', // Xe sẵn sàng tại showroom đón khách
            ]);
        }

        return 'created';
    }

    /**
     * Ghi nhận giao dịch thanh toán thất bại
     */
    private function markFailed(array $payload, MomoService $momo): void
    {
        $transaction = PaymentTransaction::where('gateway', 'momo')
            ->where('gateway_order_id', $payload['orderId'] ?? '')
            ->first();

        if ($transaction && $transaction->status !== 'paid') {
            $momo->markFailed($transaction, $payload);
        }
    }

    /**
     * Bắt đầu thanh toán cọc cho đơn thuê xe qua MoMo ATM
     */
    public function payRental(Request $request, \App\Models\Rental $rental, MomoService $momo)
    {
        if (Auth::check() && $rental->user_id && $rental->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền thanh toán cho đơn thuê xe này.');
        }

        if ($rental->payment_status === 'deposit_paid' || $rental->payment_status === 'fully_paid') {
            return redirect()->route('rentals.success', $rental->rental_code)->with('warning', 'Đơn thuê này đã được đặt cọc thành công trước đó.');
        }

        // Hỗ trợ cả test thẻ nội địa Napas (payWithATM) và Ví MoMo QR (captureWallet)
        $requestType = $request->query('type', 'payWithATM');
        $methodLabel = $requestType === 'captureWallet' ? 'Ví MoMo (Quét QR)' : 'Thẻ ATM nội địa (Napas)';

        $transaction = PaymentTransaction::create([
            'rental_id' => $rental->id,
            'gateway' => 'momo',
            'amount' => $rental->deposit_amount,
            'status' => 'pending',
            'message' => 'Thanh toán cọc thuê xe #' . $rental->rental_code . ' qua MoMo ' . $methodLabel,
        ]);

        $result = $momo->createRentalPayment($rental, $transaction, $requestType);

        if (isset($result['payUrl']) && !empty($result['payUrl'])) {
            return redirect($result['payUrl']);
        }

        return redirect()->route('rentals.success', $rental->rental_code)->with('error', 'Không thể kết nối đến cổng MoMo: ' . ($result['message'] ?? 'Vui lòng thử lại sau.'));
    }

    /**
     * Xác nhận thanh toán cọc thuê xe thành công
     */
    private function completeRentalPayment(array $payload, MomoService $momo): void
    {
        DB::transaction(function () use ($payload, $momo) {
            $transaction = PaymentTransaction::where('gateway', 'momo')
                ->where('gateway_order_id', $payload['orderId'] ?? '')
                ->lockForUpdate()
                ->first();

            if (!$transaction || !$transaction->rental_id) {
                return;
            }

            $rental = \App\Models\Rental::lockForUpdate()->find($transaction->rental_id);
            if (!$rental) {
                return;
            }

            $rental->update([
                'payment_status' => 'deposit_paid',
                'handover_code' => $rental->handover_code ?: (string) rand(100000, 999999),
            ]);

            $momo->markPaid($transaction, $payload);
        });
    }

    /**
     * Xác nhận đối tác nộp 10% hoa hồng sàn thành công qua MoMo
     */
    private function completeCommissionPayment(array $payload, MomoService $momo): void
    {
        DB::transaction(function () use ($payload, $momo) {
            $transaction = PaymentTransaction::where('gateway', 'momo')
                ->where('gateway_order_id', $payload['orderId'] ?? '')
                ->lockForUpdate()
                ->first();

            if (!$transaction || !$transaction->rental_id) {
                return;
            }

            $rental = \App\Models\Rental::lockForUpdate()->find($transaction->rental_id);
            if (!$rental) {
                return;
            }

            $totalRentalFee = (float) ($rental->total_rental_fee + $rental->total_driver_fee);
            $commission10 = round($totalRentalFee * 0.10);
            $transId = $payload['transId'] ?? ($payload['orderId'] ?? 'MOMO_' . time());

            $rental->update([
                'partner_commission_fee' => $commission10,
                'partner_commission_status' => 'paid',
                'partner_commission_proof' => $transId,
                'partner_commission_paid_at' => now(),
                'platform_fee' => $commission10,
                'partner_payout' => max(0, $totalRentalFee - $commission10),
            ]);

            $momo->markPaid($transaction, $payload);
        });
    }

    private function completeAppointmentCommissionPayment(array $payload, MomoService $momo): void
    {
        DB::transaction(function () use ($payload, $momo) {
            $transaction = PaymentTransaction::where('gateway', 'momo')
                ->where('gateway_order_id', $payload['orderId'] ?? '')
                ->lockForUpdate()
                ->first();

            $appId = $momo->appointmentCommissionId($payload);
            $appointment = $appId ? \App\Models\Appointment::lockForUpdate()->find($appId) : null;
            if (!$appointment) {
                return;
            }

            $carPrice = (float) ($appointment->deal_price ?: ($appointment->product?->price ?: 500000000));
            $commission1 = round($carPrice * 0.01);
            $transId = (string) ($payload['transId'] ?? ($payload['orderId'] ?? 'MOMO_' . time()));

            $appointment->update([
                'deal_status' => 'deal_won',
                'deal_price' => $carPrice,
                'commission_amount' => $commission1,
                'commission_status' => 'paid',
                'commission_proof' => $transId,
                'commission_paid_at' => now(),
                'status' => 'completed',
            ]);

            // Gỡ xe khỏi trang chủ và đánh dấu đã bán thành công
            $appointment->product?->update([
                'rental_status' => 'sold',
                'quantity' => 0,
            ]);

            // Đồng bộ Đơn mua xe (Order)
            \App\Models\Order::syncFromAppointment($appointment, 'momo', 'paid');

            if ($transaction) {
                $momo->markPaid($transaction, $payload);
            }
        });
    }
}
