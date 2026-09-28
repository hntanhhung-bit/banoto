<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MomoService
{
    public function createPayment(Order $order, PaymentTransaction $transaction, string $requestType = 'captureWallet'): array
    {
        $endpoint = config('services.momo.endpoint', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $partnerCode = config('services.momo.partner_code', env('MOMO_PARTNER_CODE', ''));
        $accessKey = config('services.momo.access_key', env('MOMO_ACCESS_KEY', ''));
        $secretKey = config('services.momo.secret_key', env('MOMO_SECRET_KEY', ''));

        $orderInfo = 'Thanh toan don hang #' . ($order->order_code ?? $order->id);
        $amount = (string) ((int) ($order->total_amount ?? $order->total_price ?? $transaction->amount));
        $orderId = $order->id . '_' . $transaction->id . '_' . time();
        $redirectUrl = config('services.momo.redirect_url') ?: route('user.payment.momo.callback');
        $ipnUrl = config('services.momo.ipn_url') ?: route('payment.momo.ipn');
        $extraData = (string) $order->id;
        $requestId = (string) time();

        $rawHash = 'accessKey=' . $accessKey .
            '&amount=' . $amount .
            '&extraData=' . $extraData .
            '&ipnUrl=' . $ipnUrl .
            '&orderId=' . $orderId .
            '&orderInfo=' . $orderInfo .
            '&partnerCode=' . $partnerCode .
            '&redirectUrl=' . $redirectUrl .
            '&requestId=' . $requestId .
            '&requestType=' . $requestType;

        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => 'Auto Showroom & Gara',
            'storeId' => 'GaraStore',
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature,
        ];

        $transaction->update([
            'gateway_order_id' => $orderId,
            'request_payload' => $data,
        ]);

        try {
            $response = Http::withOptions([
                'verify' => filter_var(config('services.momo.verify_ssl', false), FILTER_VALIDATE_BOOLEAN),
            ])->timeout(15)->post($endpoint, $data);

            $result = $response->json() ?? [];

            $transaction->update([
                'response_payload' => $result,
                'result_code' => isset($result['resultCode']) ? (int) $result['resultCode'] : null,
                'message' => $result['message'] ?? null,
                'status' => isset($result['payUrl']) ? 'initiated' : 'failed',
            ]);

            return $result;
        } catch (\Exception $e) {
            Log::error('Momo createPayment error: ' . $e->getMessage());
            $transaction->update([
                'status' => 'failed',
                'message' => 'Lỗi kết nối tới cổng thanh toán MoMo: ' . $e->getMessage(),
            ]);
            return ['resultCode' => -1, 'message' => $e->getMessage()];
        }
    }

    public function isSuccessful(array $payload): bool
    {
        return (string) ($payload['resultCode'] ?? '') === '0';
    }

    public function markPaid(PaymentTransaction $transaction, array $payload): void
    {
        $transaction->update([
            'transaction_id' => $payload['transId'] ?? null,
            'result_code' => (int) ($payload['resultCode'] ?? 0),
            'message' => $payload['message'] ?? 'Giao dịch thành công',
            'response_payload' => $payload,
            'status' => 'paid',
            'paid_at' => Carbon::now(),
        ]);
    }

    public function isPendingResponse(array $payload): bool
    {
        $code = isset($payload['resultCode']) ? (int) $payload['resultCode'] : -1;
        return in_array($code, [7002, 9000, 1000]);
    }

    public function markFailed(PaymentTransaction $transaction, array $payload): void
    {
        $resultCode = isset($payload['resultCode']) ? (int) $payload['resultCode'] : null;
        $isPending = in_array($resultCode, [7002, 9000, 1000]);
        $status = $isPending ? 'pending' : 'failed';
        $message = $payload['message'] ?? ($isPending ? 'Đang chờ xử lý (Chưa nhận được tiền)' : 'Giao dịch thất bại');

        $transaction->update([
            'transaction_id' => $payload['transId'] ?? null,
            'result_code' => $resultCode,
            'message' => $message,
            'response_payload' => $payload,
            'status' => $status,
        ]);
    }

    public function isValidSuccessfulResponse(array $payload): bool
    {
        return $this->isValidResponse($payload) && $this->isSuccessful($payload);
    }

    public function isValidResponse(array $payload): bool
    {
        if (!isset($payload['signature'])) {
            return false;
        }

        $accessKey = config('services.momo.access_key', env('MOMO_ACCESS_KEY', ''));
        $secretKey = config('services.momo.secret_key', env('MOMO_SECRET_KEY', ''));

        $rawHash = 'accessKey=' . $accessKey .
            '&amount=' . ($payload['amount'] ?? '') .
            '&extraData=' . ($payload['extraData'] ?? '') .
            '&message=' . ($payload['message'] ?? '') .
            '&orderId=' . ($payload['orderId'] ?? '') .
            '&orderInfo=' . ($payload['orderInfo'] ?? '') .
            '&orderType=' . ($payload['orderType'] ?? '') .
            '&partnerCode=' . ($payload['partnerCode'] ?? '') .
            '&payType=' . ($payload['payType'] ?? '') .
            '&requestId=' . ($payload['requestId'] ?? '') .
            '&responseTime=' . ($payload['responseTime'] ?? '') .
            '&resultCode=' . ($payload['resultCode'] ?? '') .
            '&transId=' . ($payload['transId'] ?? '');

        return hash_equals(
            hash_hmac('sha256', $rawHash, $secretKey),
            (string) $payload['signature']
        );
    }

    public function orderId(array $payload): ?int
    {
        $orderId = $payload['extraData'] ?? null;
        return is_numeric($orderId) ? (int) $orderId : null;
    }

    public function createRentalPayment(\App\Models\Rental $rental, PaymentTransaction $transaction, string $requestType = 'captureWallet'): array
    {
        $endpoint = config('services.momo.endpoint', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $partnerCode = config('services.momo.partner_code', env('MOMO_PARTNER_CODE', ''));
        $accessKey = config('services.momo.access_key', env('MOMO_ACCESS_KEY', ''));
        $secretKey = config('services.momo.secret_key', env('MOMO_SECRET_KEY', ''));

        $orderInfo = 'Thanh toan coc xe #' . $rental->rental_code;
        $amount = (string) ((int) $rental->deposit_amount);
        $orderId = 'RENTAL_' . $rental->id . '_' . $transaction->id . '_' . time();
        $redirectUrl = config('services.momo.redirect_url') ?: route('user.payment.momo.callback');
        $ipnUrl = config('services.momo.ipn_url') ?: route('payment.momo.ipn');
        $extraData = 'RENTAL_' . $rental->id;
        $requestId = (string) time();

        $rawHash = 'accessKey=' . $accessKey .
            '&amount=' . $amount .
            '&extraData=' . $extraData .
            '&ipnUrl=' . $ipnUrl .
            '&orderId=' . $orderId .
            '&orderInfo=' . $orderInfo .
            '&partnerCode=' . $partnerCode .
            '&redirectUrl=' . $redirectUrl .
            '&requestId=' . $requestId .
            '&requestType=' . $requestType;

        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => 'Auto Showroom & Gara',
            'storeId' => 'GaraStore',
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature,
        ];

        $transaction->update([
            'gateway_order_id' => $orderId,
            'request_payload' => $data,
        ]);

        try {
            $response = \Illuminate\Support\Facades\Http::withOptions([
                'verify' => filter_var(config('services.momo.verify_ssl', false), FILTER_VALIDATE_BOOLEAN),
            ])->timeout(15)->post($endpoint, $data);

            $result = $response->json() ?? [];

            $transaction->update([
                'response_payload' => $result,
                'result_code' => isset($result['resultCode']) ? (int) $result['resultCode'] : null,
                'message' => $result['message'] ?? null,
                'status' => isset($result['payUrl']) ? 'initiated' : 'failed',
            ]);

            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Momo createRentalPayment error: ' . $e->getMessage());
            $transaction->update([
                'status' => 'failed',
                'message' => 'Lỗi kết nối tới cổng thanh toán MoMo: ' . $e->getMessage(),
            ]);
            return ['resultCode' => -1, 'message' => $e->getMessage()];
        }
    }

    public function rentalId(array $payload): ?int
    {
        $extraData = $payload['extraData'] ?? '';
        if (str_starts_with($extraData, 'RENTAL_')) {
            $id = substr($extraData, 7);
            return is_numeric($id) ? (int) $id : null;
        }
        return null;
    }

    public function createPartnerCommissionPayment(\App\Models\Rental $rental, PaymentTransaction $transaction, string $requestType = 'captureWallet'): array
    {
        $endpoint = config('services.momo.endpoint', 'https://test-payment.momo.vn/v2/gateway/api/create');
        $partnerCode = config('services.momo.partner_code', env('MOMO_PARTNER_CODE', ''));
        $accessKey = config('services.momo.access_key', env('MOMO_ACCESS_KEY', ''));
        $secretKey = config('services.momo.secret_key', env('MOMO_SECRET_KEY', ''));

        $totalRentalFee = (float) ($rental->total_rental_fee + $rental->total_driver_fee);
        $commission10 = (int) round($totalRentalFee * 0.10);

        $orderInfo = 'Nop 10% hoa hong san don thue #' . $rental->rental_code;
        $amount = (string) $commission10;
        $orderId = 'COMM_' . $rental->id . '_' . $transaction->id . '_' . time();
        $redirectUrl = config('services.momo.redirect_url') ?: route('user.payment.momo.callback');
        $ipnUrl = config('services.momo.ipn_url') ?: route('payment.momo.ipn');
        $extraData = 'COMMISSION_' . $rental->id;
        $requestId = (string) time();

        $rawHash = 'accessKey=' . $accessKey .
            '&amount=' . $amount .
            '&extraData=' . $extraData .
            '&ipnUrl=' . $ipnUrl .
            '&orderId=' . $orderId .
            '&orderInfo=' . $orderInfo .
            '&partnerCode=' . $partnerCode .
            '&redirectUrl=' . $redirectUrl .
            '&requestId=' . $requestId .
            '&requestType=' . $requestType;

        $signature = hash_hmac('sha256', $rawHash, $secretKey);

        $data = [
            'partnerCode' => $partnerCode,
            'partnerName' => 'AutoCar Admin Platform',
            'storeId' => 'AutoCarAdmin',
            'requestId' => $requestId,
            'amount' => $amount,
            'orderId' => $orderId,
            'orderInfo' => $orderInfo,
            'redirectUrl' => $redirectUrl,
            'ipnUrl' => $ipnUrl,
            'lang' => 'vi',
            'extraData' => $extraData,
            'requestType' => $requestType,
            'signature' => $signature,
        ];

        $transaction->update([
            'gateway_order_id' => $orderId,
            'request_payload' => $data,
        ]);

        try {
            $response = \Illuminate\Support\Facades\Http::withOptions([
                'verify' => filter_var(config('services.momo.verify_ssl', false), FILTER_VALIDATE_BOOLEAN),
            ])->timeout(15)->post($endpoint, $data);

            $result = $response->json() ?? [];

            $transaction->update([
                'response_payload' => $result,
                'result_code' => isset($result['resultCode']) ? (int) $result['resultCode'] : null,
                'message' => $result['message'] ?? null,
                'status' => isset($result['payUrl']) ? 'initiated' : 'failed',
            ]);

            return $result;
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Momo createPartnerCommissionPayment error: ' . $e->getMessage());
            $transaction->update([
                'status' => 'failed',
                'message' => 'Lỗi kết nối tới cổng thanh toán MoMo: ' . $e->getMessage(),
            ]);
            return ['resultCode' => -1, 'message' => $e->getMessage()];
        }
    }

    public function rentalCommissionId(array $payload): ?int
    {
        $extraData = $payload['extraData'] ?? '';
        if (str_starts_with($extraData, 'COMMISSION_')) {
            $id = substr($extraData, 11);
            return is_numeric($id) ? (int) $id : null;
        }
        return null;
    }
}
