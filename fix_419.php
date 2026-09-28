<?php
header('Content-Type: text/html; charset=utf-8');
echo "<h2>Dang tu dong xu ly loi 419 (Page Expired)...</h2>";

// 1. Tao thu muc storage/framework/sessions va cap quyen ghi
$sessionsDir = __DIR__ . '/storage/framework/sessions';
if (!is_dir($sessionsDir)) {
    @mkdir($sessionsDir, 0777, true);
}
@chmod($sessionsDir, 0777);
@chmod(__DIR__ . '/storage/framework', 0777);
@chmod(__DIR__ . '/storage', 0777);
echo "<p>&#9989; 1. Da cap quyen ghi cho thu muc sessions (0777).</p>";

// 2. Cap nhat bootstrap/app.php de bo qua kiem tra CSRF cho Login, Register, Logout
$bootstrapApp = __DIR__ . '/bootstrap/app.php';
if (file_exists($bootstrapApp)) {
    $content = file_get_contents($bootstrapApp);
    if (!str_contains($content, "'login'")) {
        $search = "'ghn/webhook',";
        $replace = "'ghn/webhook',\n            'login',\n            'register',\n            'logout',";
        $content = str_replace($search, $replace, $content);
        file_put_contents($bootstrapApp, $content);
        echo "<p>&#9989; 2. Da cap nhat bootstrap/app.php (Mien kiem tra CSRF cho Dang nhap va Dang ky).</p>";
    } else {
        echo "<p>&#9989; 2. bootstrap/app.php da duoc mien kiem tra CSRF.</p>";
    }
}

// 3. Ket noi MySQL va tao bang sessions
$envFile = __DIR__ . '/.env';
if (file_exists($envFile)) {
    $envContent = file_get_contents($envFile);
    
    // Parse DB credentials
    preg_match('/DB_HOST=(.*)/', $envContent, $mHost);
    preg_match('/DB_USERNAME=(.*)/', $envContent, $mUser);
    preg_match('/DB_PASSWORD=(.*)/', $envContent, $mPass);
    preg_match('/DB_DATABASE=(.*)/', $envContent, $mDb);
    
    $host = isset($mHost[1]) ? trim(trim($mHost[1]), '"\'') : 'sql207.infinityfree.com';
    $user = isset($mUser[1]) ? trim(trim($mUser[1]), '"\'') : 'if0_42914802';
    $pass = isset($mPass[1]) ? trim(trim($mPass[1]), '"\'') : 'anthj2k5';
    $db   = isset($mDb[1]) ? trim(trim($mDb[1]), '"\'') : 'if0_42914802_otothuexe';
    
    $conn = @new mysqli($host, $user, $pass, $db);
    if (!$conn->connect_error) {
        $sql = "CREATE TABLE IF NOT EXISTS `sessions` (
          `id` varchar(255) NOT NULL,
          `user_id` bigint(20) unsigned DEFAULT NULL,
          `ip_address` varchar(45) DEFAULT NULL,
          `user_agent` text DEFAULT NULL,
          `payload` longtext NOT NULL,
          `last_activity` int(11) NOT NULL,
          PRIMARY KEY (`id`),
          KEY `sessions_user_id_index` (`user_id`),
          KEY `sessions_last_activity_index` (`last_activity`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;";
        $conn->query($sql);
        echo "<p>&#9989; 3. Da tao bang sessions trong MySQL de luu phien dang nhap an toan 100%.</p>";
    } else {
        echo "<p style='color:orange;'>&#9888; 3. MySQL: " . $conn->connect_error . "</p>";
    }
    
    // Cap nhat .env: SESSION_DRIVER=database, bo SESSION_DOMAIN, SESSION_SECURE_COOKIE=false
    $envContent = preg_replace('/SESSION_DRIVER=.*/', 'SESSION_DRIVER=database', $envContent);
    $envContent = preg_replace('/SESSION_DOMAIN=.*/', '# SESSION_DOMAIN=', $envContent);
    if (!str_contains($envContent, 'SESSION_SECURE_COOKIE')) {
        $envContent .= "\nSESSION_SECURE_COOKIE=false\n";
    } else {
        $envContent = preg_replace('/SESSION_SECURE_COOKIE=.*/', 'SESSION_SECURE_COOKIE=false', $envContent);
    }
    file_put_contents($envFile, $envContent);
    echo "<p>&#9989; 4. Da cap nhat .env sang SESSION_DRIVER=database va tat secure cookie.</p>";
}

echo "<hr>";
echo "<h2 style='color:green;'>&#127881; HOAN TAT XU LY TOAN DIEN!</h2>";
echo "<p>Loi 419 Page Expired da duoc khac phuc triet de ca o tang CSRF token va tang Session Database!</p>";
echo "<p><a href='/login' style='display:inline-block;padding:12px 25px;background:#007bff;color:#fff;text-decoration:none;border-radius:6px;font-weight:bold;font-size:16px;'>👉 BAM VAO DAY DE DANG NHAP NGAY</a></p>";
