<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mailer\Transport\Dsn;
use Symfony\Component\Mailer\Transport\Smtp\EsmtpTransportFactory;

class EmailConfigService
{
    /**
     * Lấy cấu hình SMTP tùy chỉnh (Mắt Bão Email Pro v4 / Custom Domain)
     */
    public static function getCustomSmtp(): array
    {
        // 1. Thử đọc từ Cache
        try {
            $cached = Cache::get('custom_smtp_settings');
            if (is_array($cached) && !empty($cached['host']) && !empty($cached['username'])) {
                return $cached;
            }
        } catch (\Throwable $e) {
            // bỏ qua
        }

        // 2. Thử đọc từ file storage/app/custom_smtp.json
        $filePath = storage_path('app/custom_smtp.json');
        if (file_exists($filePath)) {
            $data = json_decode(file_get_contents($filePath), true);
            if (is_array($data) && !empty($data['host']) && !empty($data['username'])) {
                return $data;
            }
        }

        // 3. Fallback từ .env nếu host không phải smtp.gmail.com hoặc mailtrap
        return [
            'host' => env('MAIL_HOST', 's129d209.emailserver.vn'),
            'port' => (int) env('MAIL_PORT', 465),
            'username' => env('MAIL_USERNAME', ''),
            'password' => env('MAIL_PASSWORD', ''),
            'encryption' => env('MAIL_ENCRYPTION', 'ssl'),
            'from_address' => env('MAIL_FROM_ADDRESS', ''),
            'from_name' => env('MAIL_FROM_NAME', 'Auto Car Vietnam'),
            'is_enabled' => !empty(env('MAIL_USERNAME')) && !empty(env('MAIL_PASSWORD')),
        ];
    }

    /**
     * Lưu cấu hình SMTP vào Cache & storage
     */
    public static function saveCustomSmtp(array $data): void
    {
        $settings = [
            'host' => trim($data['host'] ?? 's129d209.emailserver.vn'),
            'port' => (int) ($data['port'] ?? 465),
            'username' => trim($data['username'] ?? ''),
            'password' => trim($data['password'] ?? ''),
            'encryption' => trim($data['encryption'] ?? 'ssl'),
            'from_address' => trim($data['from_address'] ?? ($data['username'] ?? '')),
            'from_name' => trim($data['from_name'] ?? 'Auto Car Vietnam'),
            'is_enabled' => !empty($data['is_enabled']),
            'updated_at' => now()->toIso8601String(),
        ];

        try {
            $path = storage_path('app/custom_smtp.json');
            $dir = dirname($path);
            if (!is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
            file_put_contents($path, json_encode($settings, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        } catch (\Throwable $e) {
            Log::warning('Could not write custom_smtp.json: ' . $e->getMessage());
        }

        try {
            Cache::forever('custom_smtp_settings', $settings);
        } catch (\Throwable $e) {
            Log::warning('Could not cache custom_smtp_settings: ' . $e->getMessage());
        }
    }

    /**
     * Gửi email thử nghiệm bằng cấu hình SMTP Mắt Bão
     */
    public static function sendTestEmail(string $targetEmail, ?array $overrideConfig = null): bool
    {
        $cfg = $overrideConfig ?: self::getCustomSmtp();

        if (empty($cfg['host']) || empty($cfg['username']) || empty($cfg['password'])) {
            throw new \Exception('Chưa nhập đủ thông tin SMTP (Host, Email đăng nhập, Mật khẩu).');
        }

        config([
            'mail.mailers.custom_smtp_test' => [
                'transport' => 'smtp',
                'host' => $cfg['host'],
                'port' => $cfg['port'],
                'encryption' => $cfg['encryption'],
                'username' => $cfg['username'],
                'password' => $cfg['password'],
                'timeout' => 8,
            ]
        ]);

        $fromAddress = !empty($cfg['from_address']) ? $cfg['from_address'] : $cfg['username'];
        $fromName = !empty($cfg['from_name']) ? $cfg['from_name'] : 'Auto Car Vietnam';

        Mail::mailer('custom_smtp_test')->raw(
            "Xin chào,\n\nĐây là email kiểm tra xác nhận máy chủ Email Mắt Bão ({$cfg['host']}) cho tên miền thueotovn.id.vn đang hoạt động thành công!\n\nEmail gửi: {$fromAddress}\nThời gian gửi: " . now()->format('d/m/Y H:i:s'),
            function ($message) use ($targetEmail, $fromAddress, $fromName) {
                $message->to($targetEmail)
                    ->from($fromAddress, $fromName)
                    ->subject('✅ [Auto Car] Kiểm tra gửi mail Mắt Bão (thueotovn.id.vn) thành công');
            }
        );

        return true;
    }
}
