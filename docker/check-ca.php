<?php
/**
 * Script kiểm tra định dạng và quyền đọc chứng chỉ SSL CA cho Aiven MySQL
 */
$caPath = getenv('MYSQL_ATTR_SSL_CA') ?: '/run/app-certificates/mysql-ca.pem';

if (!file_exists($caPath)) {
    fwrite(STDERR, "Lỗi kiểm tra chứng chỉ: Không tìm thấy file tại '{$caPath}'. Hãy kiểm tra Secret Files trên Render và biến MYSQL_ATTR_SSL_CA.\n");
    exit(1);
}

if (!is_readable($caPath)) {
    fwrite(STDERR, "Lỗi kiểm tra chứng chỉ: Tiến trình PHP (www-data) không có quyền đọc file '{$caPath}'.\n");
    exit(1);
}

$content = file_get_contents($caPath);
if (empty(trim($content)) || !str_contains($content, 'BEGIN CERTIFICATE') || !str_contains($content, 'END CERTIFICATE')) {
    fwrite(STDERR, "Lỗi kiểm tra chứng chỉ: File '{$caPath}' không đúng định dạng PEM hợp lệ của Aiven CA.\n");
    exit(1);
}

echo "✅ Đã xác minh chứng chỉ SSL CA thành công: {$caPath}\n";
exit(0);
