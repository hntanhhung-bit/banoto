<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SepayService
{
    protected string $apiKey;
    protected string $bankAcc;
    protected string $bankName;
    protected string $accName;
    protected string $bankBin;
    protected string $apiEndpoint;

    public function __construct()
    {
        $this->apiKey = config('services.sepay.api_key', '2ZABJEF6XREZPY7VT5QNN0BQGCCEUIVGZIMFGMCWMSOYUOF7SIXWH89YETJ5NPYL');
        $this->bankAcc = config('services.sepay.bank_acc', '12325072005');
        $this->bankName = config('services.sepay.bank_name', 'TPBank');
        $this->accName = config('services.sepay.acc_name', 'HOANG NGOC THI');
        $this->bankBin = config('services.sepay.bank_bin', '970423');
        $this->apiEndpoint = config('services.sepay.api_endpoint', 'https://my.sepay.vn/userapi');
    }

    /**
     * Tạo đường dẫn ảnh mã QR SePay / VietQR theo đúng cú pháp chuyển khoản
     */
    public function getQrUrl(int|float $amount, string $description): string
    {
        $cleanDesc = urlencode(trim($description));
        $intAmount = (int) round($amount);

        // Sử dụng cổng ảnh QR chuẩn động của SePay
        return "https://qr.sepay.vn/img?acc={$this->bankAcc}&bank={$this->bankName}&amount={$intAmount}&des={$cleanDesc}&template=compact";
    }

    /**
     * Lấy đường dẫn ảnh mã VietQR dự phòng
     */
    public function getVietQrUrl(int|float $amount, string $description): string
    {
        $cleanDesc = urlencode(trim($description));
        $encodedName = urlencode($this->accName);
        $intAmount = (int) round($amount);

        return "https://img.vietqr.io/image/{$this->bankBin}-{$this->bankAcc}-compact2.png?amount={$intAmount}&addInfo={$cleanDesc}&accountName={$encodedName}";
    }

    /**
     * Lấy thông tin tài khoản ngân hàng thụ hưởng
     */
    public function getBankInfo(): array
    {
        return [
            'bank_name' => $this->bankName,
            'bank_full_name' => 'Ngân hàng TMCP Tiên Phong (TPBank)',
            'account_number' => $this->bankAcc,
            'account_holder' => $this->accName,
            'bank_bin' => $this->bankBin,
        ];
    }

    /**
     * Xác thực Webhook nhận từ SePay
     */
    public function verifyWebhook(Request $request): bool
    {
        $authHeader = $request->header('Authorization', '');
        
        // SePay có thể gửi dạng "Apikey KEY" hoặc "Bearer KEY"
        if (!empty($authHeader)) {
            $parts = explode(' ', $authHeader);
            $token = end($parts);
            if (hash_equals($this->apiKey, trim($token))) {
                return true;
            }
        }

        // Hoặc truyền token qua query param / payload
        $tokenParam = $request->query('api_key') ?: $request->input('api_key');
        if ($tokenParam && hash_equals($this->apiKey, trim($tokenParam))) {
            return true;
        }

        // Nếu trong môi trường local/test cho phép linh hoạt log lại
        Log::warning('SePay Webhook Auth Header: ' . $authHeader);
        return false;
    }

    /**
     * Kiểm tra chủ động giao dịch qua SePay API (Transactions List)
     * Rất tiện lợi cho việc polling Ajax tự động hoàn tất đơn khi chuyển khoản thành công
     */
    public function checkTransactionFromApi(string $orderCode, int|float $expectedAmount): ?array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->withoutVerifying()->get($this->apiEndpoint . '/transactions/list', [
                'limit' => 20,
            ]);

            if (!$response->successful()) {
                Log::warning('SePay checkTransactionFromApi HTTP failed: ' . $response->status());
                return null;
            }

            $data = $response->json();
            $transactions = $data['transactions'] ?? [];

            $normalizedOrderCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $orderCode));
            $minAmount = (int) round($expectedAmount * 0.95); // Chấp nhận sai số nhỏ nếu có

            foreach ($transactions as $tx) {
                // Chỉ lấy giao dịch tiền vào (amount_in > 0)
                $amountIn = (int) round((float) ($tx['amount_in'] ?? 0));
                if ($amountIn < $minAmount) {
                    continue;
                }

                $content = strtoupper($tx['transaction_content'] ?? '');
                $normalizedContent = preg_replace('/[^A-Za-z0-9]/', '', $content);

                // Nếu nội dung chuyển khoản chứa mã đơn hàng
                if (str_contains($normalizedContent, $normalizedOrderCode) || str_contains($content, strtoupper($orderCode))) {
                    return $tx;
                }
            }
        } catch (\Exception $e) {
            Log::error('SePay checkTransactionFromApi Error: ' . $e->getMessage());
        }

        return null;
    }
}
