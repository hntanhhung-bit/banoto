<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Rental;
use App\Models\PaymentTransaction;
use App\Services\SepayService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SepayController extends Controller
{
    /**
     * Mở trang hiển thị QR SePay cho đơn mua xe
     */
    public function payOrder(Order $order, SepayService $sepay)
    {
        if (Auth::check() && $order->user_id && $order->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập đơn hàng này.');
        }

        if ($order->payment_status === 'paid') {
            return redirect()->route('checkout.success', $order->order_code)->with('info', 'Đơn hàng này đã được thanh toán thành công trước đó.');
        }

        $description = 'THANHTOAN ' . $order->order_code;
        $qrUrl = $sepay->getQrUrl($order->total_amount, $description);
        $bankInfo = $sepay->getBankInfo();

        // Ghi nhận transaction
        $transaction = PaymentTransaction::firstOrCreate(
            [
                'order_id' => $order->id,
                'gateway' => 'sepay',
                'status' => 'pending',
            ],
            [
                'gateway_order_id' => 'SEPAY_' . $order->order_code,
                'amount' => $order->total_amount,
                'message' => 'Chờ chuyển khoản quét mã QR SePay',
            ]
        );

        return view('checkout.sepay', compact('order', 'qrUrl', 'bankInfo', 'description', 'transaction'));
    }

    /**
     * Mở trang hiển thị QR SePay cho đơn thuê xe
     */
    public function payRental(Rental $rental, SepayService $sepay)
    {
        if (Auth::check() && $rental->user_id && $rental->user_id !== Auth::id()) {
            abort(403, 'Bạn không có quyền truy cập đơn thuê xe này.');
        }

        if ($rental->payment_status === 'deposit_paid' || $rental->payment_status === 'fully_paid') {
            return redirect()->route('rentals.success', $rental->rental_code)->with('info', 'Đơn thuê này đã được thanh toán cọc thành công trước đó.');
        }

        $description = 'COC ' . $rental->rental_code;
        $qrUrl = $sepay->getQrUrl($rental->deposit_amount, $description);
        $bankInfo = $sepay->getBankInfo();

        // Ghi nhận transaction
        $transaction = PaymentTransaction::firstOrCreate(
            [
                'rental_id' => $rental->id,
                'gateway' => 'sepay',
                'status' => 'pending',
            ],
            [
                'gateway_order_id' => 'SEPAY_' . $rental->rental_code,
                'amount' => $rental->deposit_amount,
                'message' => 'Chờ chuyển khoản cọc quét mã QR SePay',
            ]
        );

        return view('rentals.sepay', compact('rental', 'qrUrl', 'bankInfo', 'description', 'transaction'));
    }

    /**
     * Polling AJAX kiểm tra trạng thái thanh toán từ frontend (mỗi 3s)
     */
    public function checkStatus(string $type, string $code, SepayService $sepay)
    {
        if ($type === 'order') {
            $order = Order::where('order_code', $code)->first();
            if (!$order) {
                return response()->json(['success' => false, 'message' => 'Đơn không tồn tại.'], 404);
            }

            if ($order->payment_status === 'paid') {
                return response()->json([
                    'success' => true,
                    'paid' => true,
                    'redirect_url' => route('checkout.success', $order->order_code),
                ]);
            }

            // Gọi SePay API kiểm tra xem tiền đã vào TPBank chưa
            $matchedTx = $sepay->checkTransactionFromApi($order->order_code, $order->total_amount);
            if ($matchedTx) {
                $this->processOrderSuccess($order, $matchedTx);
                return response()->json([
                    'success' => true,
                    'paid' => true,
                    'redirect_url' => route('checkout.success', $order->order_code),
                ]);
            }

            return response()->json(['success' => true, 'paid' => false]);
        }

        if ($type === 'rental') {
            $rental = Rental::where('rental_code', $code)->first();
            if (!$rental) {
                return response()->json(['success' => false, 'message' => 'Đơn không tồn tại.'], 404);
            }

            if ($rental->payment_status === 'deposit_paid' || $rental->payment_status === 'fully_paid') {
                return response()->json([
                    'success' => true,
                    'paid' => true,
                    'redirect_url' => route('rentals.success', $rental->rental_code),
                ]);
            }

            // Gọi SePay API kiểm tra xem tiền đã vào TPBank chưa
            $matchedTx = $sepay->checkTransactionFromApi($rental->rental_code, $rental->deposit_amount);
            if ($matchedTx) {
                $this->processRentalSuccess($rental, $matchedTx);
                return response()->json([
                    'success' => true,
                    'paid' => true,
                    'redirect_url' => route('rentals.success', $rental->rental_code),
                ]);
            }

            return response()->json(['success' => true, 'paid' => false]);
        }

        if ($type === 'appointment') {
            $app = \App\Models\Appointment::where('appointment_code', $code)->first();
            if (!$app) {
                return response()->json(['success' => false, 'message' => 'Lịch hẹn không tồn tại.'], 404);
            }

            if ($app->commission_status === 'paid') {
                return response()->json([
                    'success' => true,
                    'paid' => true,
                    'redirect_url' => route('partner.appointments'),
                ]);
            }

            $carPrice = (float) ($app->deal_price ?: ($app->product?->price ?: 500000000));
            $commission1 = round($carPrice * 0.01);
            $matchedTx = $sepay->checkTransactionFromApi('HH1 ' . $app->appointment_code, $commission1);
            if (!$matchedTx) {
                $matchedTx = $sepay->checkTransactionFromApi($app->appointment_code, $commission1);
            }

            if ($matchedTx) {
                $refNo = (string) ($matchedTx['id'] ?? ($matchedTx['reference_number'] ?? 'SEPAY_' . time()));
                $app->update([
                    'deal_status' => 'deal_won',
                    'deal_price' => $carPrice,
                    'commission_amount' => $commission1,
                    'commission_status' => 'paid',
                    'commission_proof' => $refNo,
                    'commission_paid_at' => now(),
                    'status' => 'completed',
                ]);

                // Gỡ xe khỏi trang chủ và đánh dấu đã bán thành công
                $app->product?->update([
                    'rental_status' => 'sold',
                    'quantity' => 0,
                ]);

                // Đồng bộ Đơn mua xe (Order)
                \App\Models\Order::syncFromAppointment($app, 'bank_transfer', 'paid');

                return response()->json([
                    'success' => true,
                    'paid' => true,
                    'redirect_url' => route('partner.appointments'),
                ]);
            }

            return response()->json(['success' => true, 'paid' => false]);
        }

        return response()->json(['success' => false, 'message' => 'Loại đơn không hợp lệ.'], 400);
    }

    /**
     * Webhook SePay gọi vào khi có giao dịch chuyển tiền vào tài khoản
     */
    public function webhook(Request $request, SepayService $sepay)
    {
        Log::info('SePay Webhook Received', [
            'header' => $request->header('Authorization'),
            'data' => $request->all(),
        ]);

        if (!$sepay->verifyWebhook($request)) {
            Log::warning('SePay Webhook Verification Failed');
            // Trong môi trường development vẫn ghi nhận nếu có data
            if (!app()->environment('local')) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        $payload = $request->all();
        $content = strtoupper($payload['content'] ?? ($payload['description'] ?? ''));
        $transferAmount = (float) ($payload['transferAmount'] ?? ($payload['amount_in'] ?? 0));
        $transferType = strtolower($payload['transferType'] ?? 'in');

        if ($transferType !== 'in' || $transferAmount <= 0) {
            return response()->json(['success' => false, 'message' => 'Not incoming transfer']);
        }

        // 1. Kiểm tra nếu là đối tác nộp 10% hoa hồng sàn (chứa HH10 và mã đơn TULAI/TAIXE)
        if (str_contains($content, 'HH10') && preg_match('/(TULAI|TAIXE)[0-9]{10,18}/', $content, $matches)) {
            $rentalCode = $matches[0];
            $rental = Rental::where('rental_code', $rentalCode)->first();
            if ($rental) {
                $totalRentalFee = (float) ($rental->total_rental_fee + $rental->total_driver_fee);
                $commission10 = round($totalRentalFee * 0.10);
                $refNo = $payload['id'] ?? ($payload['reference_number'] ?? 'SEPAY_' . time());

                $rental->update([
                    'partner_commission_fee' => $commission10,
                    'partner_commission_status' => 'paid',
                    'partner_commission_proof' => $refNo,
                    'partner_commission_paid_at' => now(),
                    'platform_fee' => $commission10,
                    'partner_payout' => max(0, $totalRentalFee - $commission10),
                ]);

                PaymentTransaction::create([
                    'rental_id' => $rental->id,
                    'gateway' => 'sepay',
                    'gateway_order_id' => 'COMM10_' . $rental->rental_code,
                    'transaction_id' => $refNo,
                    'amount' => $commission10,
                    'status' => 'paid',
                    'message' => 'Đối tác đã nộp 10% hoa hồng sàn qua SePay (TPBank Admin). Mã GD: ' . $refNo,
                    'paid_at' => now(),
                ]);

                return response()->json(['success' => true, 'type' => 'commission_10pct', 'code' => $rentalCode]);
            }
        }

        // 1b. Kiểm tra nếu là đối tác nộp 1% hoa hồng bán xe (chứa HH1 và mã lịch hẹn HEN...)
        if (str_contains($content, 'HH1') && preg_match('/HEN[0-9]{10,18}/', $content, $matches)) {
            $appCode = $matches[0];
            $app = \App\Models\Appointment::where('appointment_code', $appCode)->first();
            if ($app) {
                $carPrice = (float) ($app->deal_price ?: ($app->product?->price ?: 500000000));
                $commission1 = round($carPrice * 0.01);
                $refNo = (string) ($payload['id'] ?? ($payload['reference_number'] ?? 'SEPAY_' . time()));

                $app->update([
                    'deal_status' => 'deal_won',
                    'deal_price' => $carPrice,
                    'commission_amount' => $commission1,
                    'commission_status' => 'paid',
                    'commission_proof' => $refNo,
                    'commission_paid_at' => now(),
                    'status' => 'completed',
                ]);

                // Gỡ xe khỏi trang chủ và đánh dấu đã bán thành công
                $app->product?->update([
                    'rental_status' => 'sold',
                    'quantity' => 0,
                ]);

                // Đồng bộ Đơn mua xe (Order)
                \App\Models\Order::syncFromAppointment($app, 'bank_transfer', 'paid');

                PaymentTransaction::create([
                    'gateway' => 'sepay',
                    'gateway_order_id' => 'COMM1_' . $app->appointment_code,
                    'transaction_id' => $refNo,
                    'amount' => $commission1,
                    'status' => 'paid',
                    'message' => 'Đối tác đã nộp 1% hoa hồng bán xe qua SePay. Mã GD: ' . $refNo,
                    'paid_at' => now(),
                ]);

                return response()->json(['success' => true, 'type' => 'appointment_commission', 'code' => $appCode]);
            }
        }

        // 2. Kiểm tra nếu là khách hàng cọc đơn thuê xe: tìm mã TULAI... hoặc TAIXE...
        if (preg_match('/(TULAI|TAIXE)[0-9]{10,18}/', $content, $matches)) {
            $rentalCode = $matches[0];
            $rental = Rental::where('rental_code', $rentalCode)->first();
            if ($rental) {
                $this->processRentalSuccess($rental, $payload);
                return response()->json(['success' => true, 'type' => 'rental', 'code' => $rentalCode]);
            }
        }

        // 2. Kiểm tra nếu là đơn mua xe: tìm mã OTO...
        if (preg_match('/OTO[0-9]{10,18}/', $content, $matches)) {
            $orderCode = $matches[0];
            $order = Order::where('order_code', $orderCode)->first();
            if ($order) {
                $this->processOrderSuccess($order, $payload);
                return response()->json(['success' => true, 'type' => 'order', 'code' => $orderCode]);
            }
        }

        return response()->json(['success' => true, 'message' => 'No matching order found in content']);
    }

    /**
     * Hoàn tất thanh toán cho đơn mua xe
     */
    private function processOrderSuccess(Order $order, array $payload): void
    {
        DB::transaction(function () use ($order, $payload) {
            $order->update([
                'payment_status' => 'paid',
                'order_status' => 'confirmed',
                'payment_method' => 'sepay',
            ]);

            PaymentTransaction::updateOrCreate(
                [
                    'order_id' => $order->id,
                    'gateway' => 'sepay',
                ],
                [
                    'gateway_order_id' => 'SEPAY_' . $order->order_code,
                    'transaction_id' => $payload['id'] ?? ($payload['reference_number'] ?? null),
                    'amount' => $order->total_amount,
                    'status' => 'paid',
                    'result_code' => 0,
                    'message' => 'Thanh toán thành công qua QR SePay (TPBank)',
                    'response_payload' => $payload,
                    'paid_at' => Carbon::now(),
                ]
            );
        });

        session()->forget('cart');
    }

    /**
     * Hoàn tất cọc cho đơn thuê xe
     */
    private function processRentalSuccess(Rental $rental, array $payload): void
    {
        DB::transaction(function () use ($rental, $payload) {
            $rental->update([
                'payment_status' => 'deposit_paid',
                'payment_method' => 'sepay',
                'handover_code' => $rental->handover_code ?: (string) rand(100000, 999999),
            ]);

            PaymentTransaction::updateOrCreate(
                [
                    'rental_id' => $rental->id,
                    'gateway' => 'sepay',
                ],
                [
                    'gateway_order_id' => 'SEPAY_' . $rental->rental_code,
                    'transaction_id' => $payload['id'] ?? ($payload['reference_number'] ?? null),
                    'amount' => $rental->deposit_amount,
                    'status' => 'paid',
                    'result_code' => 0,
                    'message' => 'Thanh toán cọc thành công qua QR SePay (TPBank)',
                    'response_payload' => $payload,
                    'paid_at' => Carbon::now(),
                ]
            );
        });
    }
}
