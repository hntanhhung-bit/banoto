<?php

namespace App\Mail\Transports;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\SentMessage;
use Symfony\Component\Mailer\Transport\AbstractTransport;

class GmailApiTransport extends AbstractTransport
{
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected ?string $refreshToken;

    public function __construct(?string $clientId = null, ?string $clientSecret = null, ?string $refreshToken = null)
    {
        parent::__construct();

        // Ưu tiên đọc từ tham số truyền vào hoặc .env, nếu không có thì đọc từ file lưu cấu hình
        $this->clientId = $clientId ?: env('GMAIL_CLIENT_ID');
        $this->clientSecret = $clientSecret ?: env('GMAIL_CLIENT_SECRET');
        $this->refreshToken = $refreshToken ?: env('GMAIL_REFRESH_TOKEN');

        if (empty($this->refreshToken)) {
            $saved = self::getSavedCredentials();
            $this->clientId = $this->clientId ?: ($saved['client_id'] ?? null);
            $this->clientSecret = $this->clientSecret ?: ($saved['client_secret'] ?? null);
            $this->refreshToken = $this->refreshToken ?: ($saved['refresh_token'] ?? null);
        }
    }

    public static function getSavedCredentials(): array
    {
        $path = storage_path('app/gmail_credentials.json');
        if (file_exists($path)) {
            $data = json_decode(file_get_contents($path), true);
            if (is_array($data)) {
                return $data;
            }
        }
        return [];
    }

    public static function saveCredentials(array $data): void
    {
        $path = storage_path('app/gmail_credentials.json');
        @file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT));
    }

    protected function doSend(SentMessage $message): void
    {
        if (empty($this->clientId) || empty($this->clientSecret) || empty($this->refreshToken)) {
            throw new \RuntimeException("Chưa cấu hình đầy đủ Gmail API: GMAIL_CLIENT_ID, GMAIL_CLIENT_SECRET hoặc GMAIL_REFRESH_TOKEN. Vui lòng vào trang Quản trị -> Cấu hình Gmail API để hoàn tất kết nối.");
        }

        $accessToken = $this->getAccessToken();

        // 1. Lấy toàn bộ nội dung email MIME theo chuẩn RFC 2822
        $rawMessage = $message->toString();

        // 2. Chuyển đổi sang chuẩn URL-safe Base64 mà Google Gmail API yêu cầu (RFC 4648)
        $rawEncoded = rtrim(strtr(base64_encode($rawMessage), '+/', '-_'), '=');

        // 3. Gửi qua HTTPS API (Cổng 443 - không bao giờ bị Render hay tường lửa chặn)
        $response = Http::withToken($accessToken)
            ->timeout(20)
            ->post('https://gmail.googleapis.com/gmail/v1/users/me/messages/send', [
                'raw' => $rawEncoded,
            ]);

        if (!$response->successful()) {
            $errorBody = $response->body();
            Log::error('Gmail API Send Failed: ' . $errorBody);
            throw new \RuntimeException("Lỗi gửi qua Gmail API: [HTTP {$response->status()}] {$errorBody}");
        }

        Log::info('Email sent successfully via Gmail API. Message ID: ' . ($response->json('id') ?? 'OK'));
    }

    /**
     * Lấy Access Token từ Refresh Token (Tự động cache 55 phút để tiết kiệm request)
     */
    public function getAccessToken(): string
    {
        $cacheKey = 'gmail_api_access_token_' . md5($this->clientId . $this->refreshToken);

        return Cache::remember($cacheKey, 3300, function () {
            $response = Http::asForm()
                ->timeout(15)
                ->post('https://oauth2.googleapis.com/token', [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'refresh_token' => $this->refreshToken,
                    'grant_type' => 'refresh_token',
                ]);

            if (!$response->successful()) {
                $errorBody = $response->body();
                Log::error('Gmail API Refresh Token Failed: ' . $errorBody);
                throw new \RuntimeException("Không thể làm mới Access Token từ Google: " . $errorBody);
            }

            $data = $response->json();
            if (empty($data['access_token'])) {
                throw new \RuntimeException("Google không trả về access_token.");
            }

            return $data['access_token'];
        });
    }

    public function __toString(): string
    {
        return 'gmail';
    }
}
