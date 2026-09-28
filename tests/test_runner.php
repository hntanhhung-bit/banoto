<?php

require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

echo "===============================================\n";
echo "TIEN HANH KIEM THU TOAN BO CHUC NANG DU AN\n";
echo "===============================================\n\n";

$results = [];

function runTest($name, $closure) {
    global $results;
    try {
        $result = $closure();
        $results[] = [
            'name' => $name,
            'status' => $result['status'],
            'note' => $result['note'],
            'data' => $result['data'] ?? null
        ];
        echo ($result['status'] === 'PASS' ? "[PASS] " : "[FAIL] ") . "$name: {$result['note']}\n";
    } catch (\Throwable $e) {
        $results[] = [
            'name' => $name,
            'status' => 'FAIL / ERROR',
            'note' => $e->getMessage() . " at " . $e->getFile() . ":" . $e->getLine()
        ];
        echo "[ERROR] $name: " . $e->getMessage() . "\n";
    }
}

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\Rental;
use App\Models\Appointment;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

// Test 1: Trang chu
runTest("TC_01_Homepage", function() use ($kernel) {
    $req = Request::create('/', 'GET');
    $res = $kernel->handle($req);
    $status = $res->getStatusCode();
    if ($status === 200 && str_contains($res->getContent(), 'Hyundai')) {
        return ['status' => 'PASS', 'note' => 'Trang chu render thanh cong 200 OK, co du lieu xe'];
    }
    return ['status' => 'FAIL', 'note' => "Status code: $status, khong tim thay danh sach xe"];
});

// Test 2: Chi tiet san pham xe
runTest("TC_02_Product_Detail", function() use ($kernel) {
    $product = Product::first();
    if (!$product) return ['status' => 'FAIL', 'note' => 'Khong co san pham trong DB'];
    $req = Request::create('/products/' . $product->id, 'GET');
    $res = $kernel->handle($req);
    $status = $res->getStatusCode();
    if ($status === 200) {
        return ['status' => 'PASS', 'note' => "Xem chi tiet xe ID {$product->id} thanh cong (200 OK)"];
    }
    return ['status' => 'FAIL', 'note' => "Status code: $status"];
});

// Test 3: Dang ky validation trong
runTest("TC_03_Register_Validation_Empty", function() use ($kernel) {
    $req = Request::create('/register', 'POST', []);
    $res = $kernel->handle($req);
    $status = $res->getStatusCode();
    // In Laravel, validation failure on web redirect back with 302
    if ($status === 302) {
        return ['status' => 'PASS', 'note' => 'Chuyen huong 302 voi session validation errors'];
    }
    return ['status' => 'FAIL', 'note' => "Khong chan request trong (Status: $status)"];
});

// Test 4: Dang ky tai khoan hop le
runTest("TC_04_Register_Success", function() use ($kernel) {
    $email = 'khachhang_' . time() . '@example.com';
    $req = Request::create('/register', 'POST', [
        'name' => 'Nguyen Van Test',
        'email' => $email,
        'password' => '12345678',
        'password_confirmation' => '12345678'
    ]);
    $res = $kernel->handle($req);
    $user = User::where('email', $email)->first();
    if ($user) {
        return ['status' => 'PASS', 'note' => "Tao user $email thanh cong. email_verified_at = " . ($user->email_verified_at ? 'verified' : 'unverified')];
    }
    return ['status' => 'FAIL', 'note' => "User khong duoc tao trong DB (Status: " . $res->getStatusCode() . ")"];
});

// Test 5: Chan user chua xac minh email vao /cart hoac /checkout
runTest("TC_05_Email_Verified_Middleware", function() use ($kernel) {
    $user = User::create([
        'name' => 'Unverified User',
        'email' => 'unverified_' . time() . '@example.com',
        'password' => Hash::make('password'),
        'role' => 'user',
        'email_verified_at' => null
    ]);
    Auth::login($user);
    $req = Request::create('/cart', 'GET');
    $res = $kernel->handle($req);
    Auth::logout();
    
    if ($res->getStatusCode() === 302 && str_contains($res->headers->get('Location') ?? '', 'email/verify')) {
        return ['status' => 'PASS', 'note' => 'Middleware verified da chan dung va redirect ve /email/verify'];
    }
    return ['status' => 'FAIL', 'note' => 'Khong chuyen huong ve /email/verify, Status: ' . $res->getStatusCode() . ' Location: ' . $res->headers->get('Location')];
});

// Test 6: Dang nhap Admin
runTest("TC_06_Admin_Login", function() use ($kernel) {
    $req = Request::create('/login', 'POST', [
        'email' => 'admin@example.com',
        'password' => 'password'
    ]);
    $res = $kernel->handle($req);
    if ($res->getStatusCode() === 302 && Auth::check() && Auth::user()->role === 'admin') {
        Auth::logout();
        return ['status' => 'PASS', 'note' => 'Admin dang nhap thanh cong, role dung la admin'];
    }
    return ['status' => 'FAIL', 'note' => 'Dang nhap Admin that bai hoac role sai. Status: ' . $res->getStatusCode()];
});

// Test 7: Bao ve /admin bang middleware admin (Guest / User thuong bi chan)
runTest("TC_07_Admin_Route_Protection", function() use ($kernel) {
    Auth::logout();
    $req = Request::create('/admin/dashboard', 'GET');
    $res = $kernel->handle($req);
    // Guest accessing /admin/dashboard must be redirected to login (302) or 403
    if ($res->getStatusCode() === 302 || $res->getStatusCode() === 403) {
        return ['status' => 'PASS', 'note' => 'Khach vang lai bi chan khoi /admin/dashboard (Status ' . $res->getStatusCode() . ')'];
    }
    return ['status' => 'FAIL', 'note' => 'Lo hong bao mat: Khach truy cap duoc admin dashboard (Status: ' . $res->getStatusCode() . ')'];
});

// Test 8: Tinh phi GHN API - Kiem tra Endpoint locations
runTest("TC_08_GHN_Locations_API", function() use ($kernel) {
    $req = Request::create('/locations/provinces', 'GET');
    $res = $kernel->handle($req);
    $status = $res->getStatusCode();
    $body = json_decode($res->getContent(), true);
    if ($status === 200 && is_array($body)) {
        return ['status' => 'PASS', 'note' => 'API /locations/provinces tra ve danh sach ' . count($body) . ' tinh thanh'];
    }
    return ['status' => 'FAIL', 'note' => "API GHN Provinces tra ve loi Status $status: " . substr($res->getContent(), 0, 150)];
});

// Test 9: Dat thue xe (Rental) - Kiem tra validation ngay nhan & ngay tra
runTest("TC_09_Rental_Date_Validation", function() use ($kernel) {
    $user = User::whereNotNull('email_verified_at')->first();
    if (!$user) {
        $user = User::create([
            'name' => 'Verified User',
            'email' => 'verified_' . time() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'user',
            'email_verified_at' => now()
        ]);
    }
    Auth::login($user);
    $product = Product::first();
    
    // Test ngay tra xe o qua khu (Start: 2020-01-01, End: 2019-01-01)
    $req = Request::create('/rentals/' . $product->id, 'POST', [
        'start_date' => '2020-01-01',
        'end_date' => '2019-01-01',
        'need_driver' => 0
    ]);
    $res = $kernel->handle($req);
    Auth::logout();
    
    // Check if validation rejects or creates invalid rental
    $invalidRental = Rental::where('start_date', '2020-01-01')->first();
    if ($invalidRental) {
        return ['status' => 'FAIL', 'note' => 'BUG NGHIEM TRONG: He thong van cho phep tao don thue xe voi ngay tra < ngay nhan! Rental ID: ' . $invalidRental->id];
    }
    return ['status' => 'PASS', 'note' => 'He thong da chan don thue xe co khoang ngay khong hop le'];
});

// Test 10: Dat lich hen lai thu (Appointment)
runTest("TC_10_Appointment_Booking", function() use ($kernel) {
    $user = User::whereNotNull('email_verified_at')->first();
    if (!$user) {
        $user = User::create([
            'name' => 'Verified User',
            'email' => 'verified_' . uniqid() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer',
            'email_verified_at' => now()
        ]);
    }
    Auth::login($user);
    $product = Product::first();
    
    $req = Request::create('/appointments/' . $product->id, 'POST', [
        'customer_name' => 'Le Van Lai Thu',
        'phone' => '0987654321',
        'appointment_date' => date('Y-m-d', strtotime('+3 days')),
        'appointment_time' => '10:00',
        'note' => 'Muon chay thu cung gia dinh'
    ]);
    $res = $kernel->handle($req);
    Auth::logout();
    
    $appt = Appointment::where('phone', '0987654321')->first();
    if ($appt) {
        return ['status' => 'PASS', 'note' => "Tao lich hen thanh cong cho KH Le Van Lai Thu (ID: {$appt->id})"];
    }
    return ['status' => 'FAIL', 'note' => 'Khong luu duoc lich hen vao CSDL (Status: ' . $res->getStatusCode() . ')'];
});

// Test 11: Bao mat Webhook SePay (Gia mao webhook khong hop le)
runTest("TC_11_Sepay_Webhook_Security", function() use ($kernel) {
    $req = Request::create('/payment/sepay/webhook', 'POST', [
        'id' => 999999,
        'gateway' => 'TPBank',
        'transactionDate' => date('Y-m-d H:i:s'),
        'accountNumber' => '12325072005',
        'subAccount' => null,
        'amountIn' => 10000000,
        'amountOut' => 0,
        'accumulated' => 10000000,
        'code' => null,
        'transactionContent' => 'FAKE TRANSACTION',
        'referenceNumber' => 'FT123456',
        'body' => 'FAKE'
    ]);
    // Không truyền API Key Authorization header
    $res = $kernel->handle($req);
    $status = $res->getStatusCode();
    if ($status === 401 || $status === 403) {
        return ['status' => 'PASS', 'note' => "Webhook SePay chan thanh cong request gia mao khong co API Key (Status: $status)"];
    }
    return ['status' => 'FAIL', 'note' => "LO HONG BAO MAT: Webhook SePay chap nhan request khong hop le (Status tra ve: $status)"];
});

// Test 12: MoMo IPN Callback Security
runTest("TC_12_MoMo_IPN_Signature_Verification", function() use ($kernel) {
    $req = Request::create('/payment/momo/ipn', 'POST', [
        'partnerCode' => 'MOMO',
        'orderId' => 'ORD_TEST_9999',
        'requestId' => 'REQ_9999',
        'amount' => 5000000,
        'orderInfo' => 'Test fake momo',
        'orderType' => 'momo_wallet',
        'transId' => '1234567890',
        'resultCode' => 0,
        'message' => 'Successful.',
        'payType' => 'qr',
        'responseTime' => time(),
        'extraData' => '',
        'signature' => 'invalid_fake_signature_hash'
    ]);
    $res = $kernel->handle($req);
    $status = $res->getStatusCode();
    $content = $res->getContent();
    if ($status === 400 || $status === 403 || str_contains($content, 'Invalid signature') || str_contains($content, 'khong hop le')) {
        return ['status' => 'PASS', 'note' => "MoMo IPN tu choi chu ky gia mao signature (Status: $status)"];
    }
    return ['status' => 'FAIL', 'note' => "CANH BAO BAO MAT: MoMo IPN khong chan chu ky sai! Status: $status, Response: " . substr($content, 0, 100)];
});

// Test 13: Admin phan quyen Toggle Role
runTest("TC_13_Admin_Toggle_Role", function() use ($kernel) {
    $admin = User::where('role', 'admin')->first();
    $targetUser = User::where('role', 'customer')->orWhere('role', 'user')->first();
    if (!$targetUser) {
        $targetUser = User::create([
            'name' => 'Demo User',
            'email' => 'demouser_' . time() . '@example.com',
            'password' => Hash::make('password'),
            'role' => 'customer'
        ]);
    }
    Auth::login($admin);
    $initialRole = $targetUser->role;

    $session = app('session.store');
    $session->start();
    $token = $session->token();

    $req = Request::create("/admin/users/{$targetUser->id}/toggle-role", 'POST', ['_token' => $token]);
    $req->setLaravelSession($session);
    $res = $kernel->handle($req);
    Auth::logout();
    
    $targetUser->refresh();
    if ($targetUser->role !== $initialRole) {
        return ['status' => 'PASS', 'note' => "Chuyen quyen thanh cong tu '$initialRole' sang '{$targetUser->role}'"];
    }
    return ['status' => 'FAIL', 'note' => "Khong thay doi duoc role cua user. Status: " . $res->getStatusCode()];
});

echo "\n===============================================\n";
echo "KET QUA KIEM THU CHI TIET VA TONG KET\n";
echo "===============================================\n";

$passCount = count(array_filter($results, fn($r) => $r['status'] === 'PASS'));
$failCount = count(array_filter($results, fn($r) => $r['status'] !== 'PASS'));

echo "Tong so Test Cases: " . count($results) . "\n";
echo "Pass: $passCount\n";
echo "Fail / Phat hien loi: $failCount\n\n";

file_put_contents(__DIR__ . '/test_execution_results.json', json_encode($results, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
