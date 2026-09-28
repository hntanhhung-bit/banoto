<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\Transports\GmailApiTransport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GmailOAuthController extends Controller
{
    public function index()
    {
        $saved = GmailApiTransport::getSavedCredentials();
        $clientId = env('GMAIL_CLIENT_ID') ?: ($saved['client_id'] ?? '');
        $clientSecret = env('GMAIL_CLIENT_SECRET') ?: ($saved['client_secret'] ?? '');
        $refreshToken = env('GMAIL_REFRESH_TOKEN') ?: ($saved['refresh_token'] ?? '');
        $connectedEmail = $saved['connected_email'] ?? env('MAIL_FROM_ADDRESS', '');

        $isConnected = !empty($clientId) && !empty($clientSecret) && !empty($refreshToken);

        $redirectUri = route('admin.gmail.callback');

        return view('admin.gmail.index', compact(
            'clientId',
            'clientSecret',
            'refreshToken',
            'connectedEmail',
            'isConnected',
            'redirectUri'
        ));
    }

    public function connect(Request $request)
    {
        $request->validate([
            'client_id' => 'required|string',
            'client_secret' => 'required|string',
        ], [
            'client_id.required' => 'Vui lòng nhập Google Client ID.',
            'client_secret.required' => 'Vui lòng nhập Google Client Secret.',
        ]);

        $clientId = trim($request->client_id);
        $clientSecret = trim($request->client_secret);

        session([
            'gmail_oauth_client_id' => $clientId,
            'gmail_oauth_client_secret' => $clientSecret,
        ]);

        $redirectUri = route('admin.gmail.callback');
        $scopes = [
            'https://mail.google.com/',
            'https://www.googleapis.com/auth/gmail.send',
            'https://www.googleapis.com/auth/gmail.compose',
            'https://www.googleapis.com/auth/userinfo.email',
        ];

        $authUrl = 'https://accounts.google.com/o/oauth2/v2/auth?' . http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => implode(' ', $scopes),
            'access_type' => 'offline',
            'prompt' => 'consent',
        ]);

        return redirect()->away($authUrl);
    }

    public function callback(Request $request)
    {
        if ($request->has('error')) {
            return redirect()->route('admin.gmail.index')->with('error', 'Người dùng đã hủy hoặc Google từ chối cấp quyền: ' . $request->error);
        }

        $code = $request->input('code');
        if (empty($code)) {
            return redirect()->route('admin.gmail.index')->with('error', 'Không nhận được mã xác thực (authorization code) từ Google.');
        }

        $saved = GmailApiTransport::getSavedCredentials();
        $clientId = session('gmail_oauth_client_id') ?: env('GMAIL_CLIENT_ID') ?: ($saved['client_id'] ?? '');
        $clientSecret = session('gmail_oauth_client_secret') ?: env('GMAIL_CLIENT_SECRET') ?: ($saved['client_secret'] ?? '');
        $redirectUri = route('admin.gmail.callback');

        if (empty($clientId) || empty($clientSecret)) {
            return redirect()->route('admin.gmail.index')->with('error', 'Không tìm thấy Client ID hoặc Client Secret trong phiên làm việc. Vui lòng thử lại.');
        }

        try {
            // Đổi mã code lấy Access Token và Refresh Token
            $response = Http::asForm()->timeout(20)->post('https://oauth2.googleapis.com/token', [
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'code' => $code,
                'grant_type' => 'authorization_code',
                'redirect_uri' => $redirectUri,
            ]);

            if (!$response->successful()) {
                return redirect()->route('admin.gmail.index')->with('error', 'Lỗi đổi token từ Google: ' . $response->body());
            }

            $tokenData = $response->json();
            $refreshToken = $tokenData['refresh_token'] ?? ($saved['refresh_token'] ?? null);
            $accessToken = $tokenData['access_token'] ?? null;

            if (empty($refreshToken)) {
                return redirect()->route('admin.gmail.index')->with('warning', 'Google không trả về Refresh Token mới (do tài khoản đã được cấp quyền trước đó). Bạn có thể bấm "Đăng nhập Google" lại và chọn đồng ý cấp quyền để nhận Refresh Token mới.');
            }

            // Lấy thông tin email đã kết nối
            $connectedEmail = 'hntanhhung@gmail.com';
            if ($accessToken) {
                try {
                    $profileRes = Http::withToken($accessToken)->get('https://gmail.googleapis.com/gmail/v1/users/me/profile');
                    if ($profileRes->successful() && !empty($profileRes->json('emailAddress'))) {
                        $connectedEmail = $profileRes->json('emailAddress');
                    }
                } catch (\Throwable $e) {
                    Log::warning('Could not get profile email: ' . $e->getMessage());
                }
            }

            // Lưu cấu hình vào file storage/app/gmail_credentials.json
            GmailApiTransport::saveCredentials([
                'client_id' => $clientId,
                'client_secret' => $clientSecret,
                'refresh_token' => $refreshToken,
                'connected_email' => $connectedEmail,
                'connected_at' => now()->toIso8601String(),
            ]);

            Cache::forget('gmail_api_access_token_' . md5($clientId . $refreshToken));

            return redirect()->route('admin.gmail.index')->with('success', "Kết nối Gmail API thành công với tài khoản: {$connectedEmail}! Website hiện đã có thể gửi email qua cổng HTTPS.");
        } catch (\Throwable $e) {
            return redirect()->route('admin.gmail.index')->with('error', 'Lỗi kết nối Gmail API: ' . $e->getMessage());
        }
    }

    public function sendTest(Request $request)
    {
        $request->validate([
            'test_email' => 'required|email',
        ], [
            'test_email.required' => 'Vui lòng nhập email người nhận thử nghiệm.',
            'test_email.email' => 'Email người nhận không hợp lệ.',
        ]);

        $saved = GmailApiTransport::getSavedCredentials();
        $senderEmail = $saved['connected_email'] ?? env('MAIL_FROM_ADDRESS', 'hntanhhung@gmail.com');
        $targetEmail = $request->test_email;

        try {
            // Tạm thời kích hoạt transport gmail để gửi test
            $transport = new GmailApiTransport();
            
            // Gửi email kiểm tra thông qua Laravel Mailer với driver gmail
            Mail::mailer('gmail')->raw("Xin chào,\n\nĐây là email kiểm tra xác nhận tính năng Gmail API trên website Auto Car đang hoạt động hoàn hảo 100% qua cổng HTTPS 443!\n\nThời gian gửi: " . now()->format('d/m/Y H:i:s'), function ($msg) use ($targetEmail, $senderEmail) {
                $msg->to($targetEmail)
                    ->from($senderEmail, 'Auto Car Vietnam')
                    ->subject('✅ [Auto Car] Kiểm tra kết nối Gmail API thành công');
            });

            return back()->with('success', "Đã gửi email thử nghiệm thành công tới {$targetEmail} qua Gmail API! Vui lòng kiểm tra hộp thư đến.");
        } catch (\Throwable $e) {
            return back()->with('error', 'Gửi email thử nghiệm thất bại: ' . $e->getMessage());
        }
    }

    public function disconnect()
    {
        $path = storage_path('app/gmail_credentials.json');
        if (file_exists($path)) {
            @unlink($path);
        }
        try {
            Cache::forget('gmail_api_credentials');
        } catch (\Throwable $e) {}
        return redirect()->route('admin.gmail.index')->with('success', 'Đã ngắt kết nối Gmail API thành công.');
    }
}
