<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GHNService
{
    protected string $baseUrl;
    protected string $token;
    protected int $shopId;
    protected int $fromDistrictId;
    protected string $fromWardCode;

    public function __construct()
    {
        $this->baseUrl = config('services.ghn.base_url', 'https://dev-online-gateway.ghn.vn/shiip/public-api');
        $this->token = config('services.ghn.token', 'eea1eb4a-aa85-11f1-a973-aee5264794df');
        $this->shopId = (int) config('services.ghn.shop_id', 217505);
        $this->fromDistrictId = (int) config('services.ghn.from_district_id', 1450);
        $this->fromWardCode = (string) config('services.ghn.from_ward_code', '20806');
    }

    protected function client(bool $withShopId = false)
    {
        $headers = [
            'Token' => $this->token,
            'Content-Type' => 'application/json',
        ];

        if ($withShopId && $this->shopId > 0) {
            $headers['ShopId'] = $this->shopId;
        }

        return Http::baseUrl($this->baseUrl)
            ->withOptions([
                'verify' => filter_var(config('services.ghn.verify_ssl', false), FILTER_VALIDATE_BOOLEAN),
            ])
            ->acceptJson()
            ->timeout(15)
            ->withHeaders($headers);
    }

    // Lấy Tỉnh/Thành
    public function getProvinces(): array
    {
        return $this->get('/master-data/province');
    }

    // Lấy Quận/Huyện
    public function getDistricts(int $provinceId): array
    {
        return $this->post('/master-data/district', [
            'province_id' => $provinceId,
        ]);
    }

    // Lấy Phường/Xã
    public function getWards(int $districtId): array
    {
        return $this->post('/master-data/ward', [
            'district_id' => $districtId,
        ]);
    }

    // Thông số gói hàng xe ô tô (>20kg)
    public function packageParameters(int $weight = 25000): array
    {
        $finalWeight = $weight >= 20000 ? $weight : (int) config('services.ghn.default_weight', 25000);

        return [
            'service_type_id' => 2, // Dịch vụ giao hàng chuẩn GHN
            'insurance_value' => 0,
            'coupon' => null,
            'weight' => $finalWeight, // Tính bằng gram (25000g = 25kg > 20kg)
            'length' => 100, // Dài 100cm
            'width' => 80,   // Rộng 80cm
            'height' => 60,  // Cao 60cm
        ];
    }

    // Tính phí giao hàng cho xe ô tô
    public function calculateFee(array $params): array
    {
        $defaultWeight = (int) config('services.ghn.default_weight', 25000);
        $payload = array_merge([
            'from_district_id' => $this->fromDistrictId,
            'from_ward_code' => $this->fromWardCode,
            'service_type_id' => 2,
            'insurance_value' => 0,
            'coupon' => null,
            'weight' => $defaultWeight,
            'length' => 100,
            'width' => 80,
            'height' => 60,
        ], $params);

        return $this->post('/v2/shipping-order/fee', $payload, false);
    }

    // Tạo đơn giao hàng
    public function createOrder(array $orderData): array
    {
        return $this->post('/v2/shipping-order/create', array_merge([
            'shop_id' => $this->shopId,
        ], $orderData), true);
    }

    // Hủy đơn hàng
    public function cancelOrder(array $orderCodes): array
    {
        return $this->post('/v2/switch-status/cancel', [
            'order_codes' => $orderCodes,
            'shop_id' => $this->shopId,
        ], true);
    }

    protected function get(string $uri, array $query = []): array
    {
        try {
            $response = $this->client(false)->get($uri, $query);

            if (!$response->successful()) {
                Log::warning('GHN GET request failed', [
                    'uri' => $uri,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
                return ['code' => $response->status(), 'message' => 'GHN API request failed.', 'data' => []];
            }

            return $response->json() ?? ['code' => -1, 'message' => 'GHN returned an empty response.', 'data' => []];
        } catch (ConnectionException $exception) {
            Log::error('Unable to connect to GHN', ['uri' => $uri, 'error' => $exception->getMessage()]);
            return ['code' => -1, 'message' => 'Unable to connect to GHN.', 'data' => []];
        } catch (\Exception $e) {
            Log::error('GHN Exception', ['uri' => $uri, 'error' => $e->getMessage()]);
            return ['code' => -1, 'message' => $e->getMessage(), 'data' => []];
        }
    }

    protected function post(string $uri, array $payload, bool $withShopId = false): array
    {
        try {
            $response = $this->client($withShopId)->post($uri, $payload);

            if (!$response->successful()) {
                Log::warning('GHN POST request failed', [
                    'uri' => $uri,
                    'status' => $response->status(),
                    'body' => $response->json(),
                ]);
                return ['code' => $response->status(), 'message' => 'GHN API request failed.', 'data' => []];
            }

            return $response->json() ?? ['code' => -1, 'message' => 'GHN returned an empty response.', 'data' => []];
        } catch (ConnectionException $exception) {
            Log::error('Unable to connect to GHN', ['uri' => $uri, 'error' => $exception->getMessage()]);
            return ['code' => -1, 'message' => 'Unable to connect to GHN.', 'data' => []];
        } catch (\Exception $e) {
            Log::error('GHN Exception', ['uri' => $uri, 'error' => $e->getMessage()]);
            return ['code' => -1, 'message' => $e->getMessage(), 'data' => []];
        }
    }
}
