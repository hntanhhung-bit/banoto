<?php

// Bật hiển thị chi tiết lỗi để dễ dàng kiểm tra nguyên nhân
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// 1. Kiểm tra phiên bản PHP trên hosting (Laravel 11 bắt buộc PHP >= 8.2)
if (version_compare(PHP_VERSION, '8.2.0', '<')) {
    die('<div style="font-family:Segoe UI,sans-serif;max-width:600px;margin:50px auto;padding:25px;background:#fff5f5;color:#c53030;border:1px solid #feb2b2;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.05);">'
        . '<h3 style="margin-top:0;">⚠️ Phiên bản PHP trên Hosting chưa tương thích!</h3>'
        . '<p>Hosting của bạn hiện đang chạy <b>PHP ' . PHP_VERSION . '</b>, nhưng mã nguồn Laravel 11 yêu cầu tối thiểu <b>PHP 8.2</b>.</p>'
        . '<p><b>Cách khắc phục:</b> Vào Control Panel của InfinityFree -> Tìm mục <b>"Select PHP Version"</b> (hoặc "PHP Configuration") -> Đổi sang <b>PHP 8.2</b> rồi lưu lại.</p>'
        . '</div>');
}

// 2. Kiểm tra thư mục vendor đã được upload lên chưa
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
    die('<div style="font-family:Segoe UI,sans-serif;max-width:600px;margin:50px auto;padding:25px;background:#fffaf0;color:#dd6b20;border:1px solid #fbd38d;border-radius:8px;box-shadow:0 4px 12px rgba(0,0,0,0.05);">'
        . '<h3 style="margin-top:0;">⚠️ Thiếu thư mục "vendor"!</h3>'
        . '<p>Chưa tìm thấy file <code>vendor/autoload.php</code> bên trong thư mục <code>htdocs</code>.</p>'
        . '<p><b>Cách khắc phục:</b> Bạn cần tải (upload) toàn bộ thư mục <b>vendor</b> từ máy tính lên thư mục <b>htdocs</b> trên hosting.</p>'
        . '</div>');
}

// 3. Kiểm tra trạng thái bảo trì
if (file_exists($maintenance = __DIR__ . '/storage/framework/maintenance.php')) {
    require $maintenance;
}

// 4. Tự động nạp thư viện Composer
require __DIR__ . '/vendor/autoload.php';

// 5. Khởi động ứng dụng Laravel
/** @var Application $app */
$app = require_once __DIR__ . '/bootstrap/app.php';

// 6. Thiết lập thư mục public
$app->usePublicPath(__DIR__ . '/public');

// 7. Xử lý yêu cầu và hiển thị website
$app->handleRequest(Request::capture());
