<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest; // Bổ sung thư viện xử lý Email
use App\Http\Controllers\WelcomeController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CartController;

use App\Models\User;
use Illuminate\Auth\Events\Verified;
use Illuminate\Support\Facades\Auth;

// Trang chủ
Route::get('/', [WelcomeController::class, 'index'])->name('welcome');

// Xác thực người dùng
Route::get('register', [AuthController::class, 'showRegistrationForm'])->name('register');
Route::post('register', [AuthController::class, 'register']);
Route::get('login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('login', [AuthController::class, 'login']);
Route::post('logout', [AuthController::class, 'logout'])->name('logout');

// ================= ROUTE XÁC MINH EMAIL =================
// 1. Route hiển thị thông báo yêu cầu người dùng vào check mail
Route::get('/email/verify', function () {
    return view('auth.verify-email'); 
})->middleware('auth')->name('verification.notice');

// 2. Route xử lý khi người dùng click vào link xác minh trong email
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = User::findOrFail($id);

    // Kiểm tra tính hợp lệ của mã băm email
    if (! hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Link xác minh không hợp lệ hoặc đã bị chỉnh sửa.');
    }

    // Nếu tài khoản đã được xác minh trước đó
    if ($user->hasVerifiedEmail()) {
        Auth::login($user);
        return redirect()->route('welcome')->with('warning', 'Tài khoản của bạn đã được xác minh email trước đó.');
    }

    // Đánh dấu đã xác thực email và kích hoạt sự kiện Verified
    if ($user->markEmailAsVerified()) {
        event(new Verified($user));
    }

    // Tự động đăng nhập vào tài khoản vừa xác minh
    Auth::login($user);

    return redirect()->route('welcome')->with('success', 'Xác minh email thành công! Bạn có thể thêm xe vào giỏ hàng và sử dụng đầy đủ các tính năng.');
})->middleware(['signed'])->name('verification.verify');

// 3. Route gửi lại link xác minh email
Route::post('/email/verification-notification', function (Request $request) {
    try {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'Đã gửi lại link xác minh vào email của bạn! Vui lòng kiểm tra hộp thư đến (hoặc thư mục Spam).');
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Verification email error: ' . $e->getMessage());
        return back()->with('error', 'Không thể kết nối đến máy chủ gửi email (Render Free chặn cổng SMTP). Chi tiết: ' . $e->getMessage());
    }
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');

// 4. Route kích hoạt nhanh tài khoản (Dành cho môi trường Demo / Cloud chặn SMTP)
Route::post('/email/instant-verify', function (Request $request) {
    $user = $request->user();
    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
        event(new \Illuminate\Auth\Events\Verified($user));
    }
    return redirect()->route('welcome')->with('success', 'Chúc mừng! Tài khoản của bạn đã được kích hoạt xác thực email thành công.');
})->middleware(['auth'])->name('verification.instant');
// ========================================================

use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\RentalController;

// ================= GIỎ HÀNG & THANH TOÁN (YÊU CẦU ĐÃ XÁC THỰC EMAIL) =================
// Chỉ cho phép người dùng ĐÃ ĐĂNG NHẬP và ĐÃ XÁC THỰC EMAIL mới được vào giỏ hàng và thanh toán
Route::middleware(['auth', 'verified'])->group(function () {
    // Route để hiển thị giỏ hàng
    Route::get('/cart', [CartController::class, 'index'])->name('cart.index');

    // Route để thêm sản phẩm vào giỏ hàng
    Route::post('/cart/add/{product}', [CartController::class, 'add'])->name('cart.add');

    // Route cập nhật thông tin giỏ hàng
    Route::patch('/cart/{id}', [CartController::class, 'update'])->name('cart.update');

    // Route để xoá sản phẩm khỏi giỏ hàng
    Route::delete('/cart/remove/{product}', [CartController::class, 'remove'])->name('cart.remove');

    // Các Route Thanh toán & Đặt hàng
    Route::get('/checkout', [CheckoutController::class, 'showCheckout'])->name('checkout.index');
    Route::post('/checkout', [CheckoutController::class, 'processCheckout'])->name('checkout.process');
    Route::get('/checkout/success/{order_code}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/my-orders', [CheckoutController::class, 'myOrders'])->name('orders.my');

    // Route alias theo Lab 05 & MoMo
    Route::get('/payment', [CheckoutController::class, 'showCheckout'])->name('payment.index');
    Route::post('/payment/process', [CheckoutController::class, 'processCheckout'])->name('payment.process');
    Route::get('/orders', [CheckoutController::class, 'myOrders'])->name('orders.index');
    Route::get('/orders/{order}', [\App\Http\Controllers\User\OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/cancel', [\App\Http\Controllers\User\OrderController::class, 'cancel'])->name('orders.cancel');

    // MoMo Start & Pay Again
    Route::get('/orders/{order}/start-momo', [\App\Http\Controllers\User\MomoController::class, 'start'])->name('orders.momo.start');
    Route::get('/orders/{order}/pay/momo', [\App\Http\Controllers\User\MomoController::class, 'payAgain'])->name('orders.momo.pay');
    Route::get('/rentals/{rental}/pay/momo', [\App\Http\Controllers\User\MomoController::class, 'payRental'])->name('rentals.momo.pay');

    // SePay QR Payment (Ngân hàng tự động)
    Route::get('/orders/{order}/pay/sepay', [\App\Http\Controllers\User\SepayController::class, 'payOrder'])->name('orders.sepay.pay');
    Route::get('/rentals/{rental}/pay/sepay', [\App\Http\Controllers\User\SepayController::class, 'payRental'])->name('rentals.sepay.pay');

    // ================= DỊCH VỤ ĐẶT LỊCH XEM XE / LÁI THỬ =================
    Route::post('/appointments/{product}', [AppointmentController::class, 'store'])->name('appointments.store');
    Route::get('/my-appointments', [AppointmentController::class, 'myAppointments'])->name('appointments.my');
    Route::post('/appointments/{appointment}/cancel', [AppointmentController::class, 'cancel'])->name('appointments.cancel');

    // ================= DỊCH VỤ CHO THUÊ XE TỰ LÁI =================
    Route::post('/rentals/{product}', [RentalController::class, 'store'])->name('rentals.store');
    Route::get('/rentals/success/{rental_code}', [RentalController::class, 'success'])->name('rentals.success');
    Route::get('/my-rentals', [RentalController::class, 'myRentals'])->name('rentals.my');
    Route::get('/rentals/{rental}/voucher', [RentalController::class, 'voucher'])->name('rentals.voucher');
    Route::post('/rentals/{rental}/cancel', [RentalController::class, 'cancel'])->name('rentals.cancel');

    // Tin nhắn trực tuyến Livechat Khách hàng (Lab 7)
    Route::prefix('user')->name('user.')->group(function () {
        Route::post('/chat/send', [\App\Http\Controllers\User\ChatController::class, 'send'])->name('chat.send');
        Route::get('/chat/messages', [\App\Http\Controllers\User\ChatController::class, 'getMessages'])->name('chat.messages');
    });
});

// ================= MOMO WEBHOOKS & CALLBACKS (KHÔNG DÙNG AUTH) =================
Route::post('/payment/momo/ipn', [\App\Http\Controllers\User\MomoController::class, 'ipn'])->name('payment.momo.ipn');
Route::get('/payment/momo/callback', [\App\Http\Controllers\User\MomoController::class, 'callback'])->name('user.payment.momo.callback');

// ================= SEPAY WEBHOOKS & POLLING CHECK (KHÔNG DÙNG AUTH) =================
Route::get('/payment/sepay/check/{type}/{code}', [\App\Http\Controllers\User\SepayController::class, 'checkStatus'])->name('sepay.check');
Route::post('/payment/sepay/webhook', [\App\Http\Controllers\User\SepayController::class, 'webhook'])->name('payment.sepay.webhook');


// ================= ROUTES GHN / LOCATIONS =================
Route::prefix('locations')->name('locations.')->group(function () {
    Route::get('/provinces', [\App\Http\Controllers\User\GHNController::class, 'getProvinces'])->name('provinces');
    Route::get('/districts/{provinceId}', [\App\Http\Controllers\User\GHNController::class, 'getDistricts'])->name('districts');
    Route::get('/wards/{districtId}', [\App\Http\Controllers\User\GHNController::class, 'getWards'])->name('wards');
    Route::post('/calculate-fee', [\App\Http\Controllers\User\GHNController::class, 'getShippingFee'])->name('fee');
});
// ========================================================================

// Đường dẫn dành cho Admin (Bảo vệ bằng middleware)
Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    
    // Báo cáo doanh thu & Thống kê kinh doanh (Lab 8)
    Route::get('/admin/reports', [\App\Http\Controllers\Admin\ReportController::class, 'index'])->name('admin.reports.index');
    Route::get('/admin/reports/charts', [\App\Http\Controllers\Admin\ReportController::class, 'charts'])->name('admin.reports.charts');

    // Quản lý sản phẩm (xe) & danh mục (hãng xe)
    Route::resource('/admin/products', ProductController::class, ['as' => 'admin']);
    Route::post('/admin/products/bulk-approval', [\App\Http\Controllers\ProductController::class, 'bulkApproval'])->name('admin.products.bulkApproval');
    Route::resource('/admin/categories', CategoryController::class, ['as' => 'admin']);

    // Quản lý đơn đặt xe & thanh toán
    Route::get('/admin/orders', [OrderController::class, 'index'])->name('admin.orders.index');
    Route::post('/admin/orders/bulk-status', [OrderController::class, 'bulkStatus'])->name('admin.orders.bulkStatus');
    Route::get('/admin/orders/{order}', [OrderController::class, 'show'])->name('admin.orders.show');
    Route::patch('/admin/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('admin.orders.updateStatus');
    Route::delete('/admin/orders/{order}', [OrderController::class, 'destroy'])->name('admin.orders.destroy');

    // Quản lý Giao dịch & Báo cáo Thanh toán MoMo / Payment Transactions
    Route::get('/admin/payments', [\App\Http\Controllers\Admin\PaymentController::class, 'index'])->name('admin.payments.index');

    // Quản lý Lịch hẹn xem xe
    Route::get('/admin/appointments', [\App\Http\Controllers\Admin\AppointmentController::class, 'index'])->name('admin.appointments.index');
    Route::post('/admin/appointments/bulk-status', [\App\Http\Controllers\Admin\AppointmentController::class, 'bulkStatus'])->name('admin.appointments.bulkStatus');
    Route::get('/admin/appointments/{appointment}', [\App\Http\Controllers\Admin\AppointmentController::class, 'show'])->name('admin.appointments.show');
    Route::patch('/admin/appointments/{appointment}/status', [\App\Http\Controllers\Admin\AppointmentController::class, 'updateStatus'])->name('admin.appointments.updateStatus');
    Route::delete('/admin/appointments/{appointment}', [\App\Http\Controllers\Admin\AppointmentController::class, 'destroy'])->name('admin.appointments.destroy');

    // Quản lý Đơn thuê xe
    Route::get('/admin/rentals', [\App\Http\Controllers\Admin\RentalController::class, 'index'])->name('admin.rentals.index');
    Route::post('/admin/rentals/bulk-status', [\App\Http\Controllers\Admin\RentalController::class, 'bulkStatus'])->name('admin.rentals.bulkStatus');
    Route::get('/admin/rentals/{rental}', [\App\Http\Controllers\Admin\RentalController::class, 'show'])->name('admin.rentals.show');
    Route::patch('/admin/rentals/{rental}/status', [\App\Http\Controllers\Admin\RentalController::class, 'updateStatus'])->name('admin.rentals.updateStatus');
    Route::post('/admin/rentals/{rental}/confirm-deposit', [\App\Http\Controllers\Admin\RentalController::class, 'confirmDeposit'])->name('admin.rentals.confirmDeposit');
    Route::post('/admin/rentals/{rental}/refund', [\App\Http\Controllers\Admin\RentalController::class, 'refund'])->name('admin.rentals.refund');
    Route::delete('/admin/rentals/{rental}', [\App\Http\Controllers\Admin\RentalController::class, 'destroy'])->name('admin.rentals.destroy');

    // Thẩm định & Phê duyệt hồ sơ Đối tác Showroom / Nhà xe
    Route::get('/admin/partners', [\App\Http\Controllers\Admin\PartnerApprovalController::class, 'index'])->name('admin.partners.index');
    Route::post('/admin/partners/bulk-action', [\App\Http\Controllers\Admin\PartnerApprovalController::class, 'bulkAction'])->name('admin.partners.bulkAction');
    Route::post('/admin/partners/{user}/approve', [\App\Http\Controllers\Admin\PartnerApprovalController::class, 'approve'])->name('admin.partners.approve');
    Route::post('/admin/partners/{user}/reject', [\App\Http\Controllers\Admin\PartnerApprovalController::class, 'reject'])->name('admin.partners.reject');

    // Phê duyệt & Kiểm định xe của Đối tác trước khi hiển thị trên sàn
    Route::post('/admin/products/{product}/approve-car', [\App\Http\Controllers\ProductController::class, 'approveCar'])->name('admin.products.approveCar');
    Route::post('/admin/products/{product}/reject-car', [\App\Http\Controllers\ProductController::class, 'rejectCar'])->name('admin.products.rejectCar');

    // Quản lý người dùng & phân quyền tài khoản
    Route::get('/admin/users', [UserController::class, 'index'])->name('admin.users.index');
    Route::post('/admin/users/{user}/toggle-role', [UserController::class, 'toggleRole'])->name('admin.users.toggleRole');
    Route::post('/admin/users/{user}/update-role', [UserController::class, 'toggleRole'])->name('admin.users.updateRole');
    Route::delete('/admin/users/{user}', [UserController::class, 'destroy'])->name('admin.users.destroy');

    // ================= TIN NHẮN TRỰC TUYẾN LIVECHAT (LAB 7) =================
    Route::prefix('admin/chat')->name('admin.chat.')->group(function () {
        Route::get('/users', [\App\Http\Controllers\Admin\ChatController::class, 'getUsers'])->name('users');
        Route::get('/messages/{userId}', [\App\Http\Controllers\Admin\ChatController::class, 'getMessages'])->name('messages');
        Route::post('/send', [\App\Http\Controllers\Admin\ChatController::class, 'send'])->name('send');
    });
});

// ================= ROUTE DÀNH CHO ĐỐI TÁC SHOWROOM / NHÀ XE =================
// 1. Đăng ký & Thông báo tình trạng duyệt hồ sơ Đối tác
Route::get('/tro-thanh-doi-tac', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'showRegister'])->name('partner.register');
Route::post('/tro-thanh-doi-tac', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'processRegister'])->name('partner.register.submit');
Route::get('/doi-tac/cho-duyet', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'pendingApproval'])->name('partner.pending');

// 2. Kênh Quản trị Đối tác (Yêu cầu đăng nhập tài khoản Partner)
Route::middleware(['auth', 'partner'])->prefix('partner')->name('partner.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/cars', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'cars'])->name('cars');
    Route::get('/cars/create', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'createCar'])->name('cars.create');
    Route::post('/cars', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'storeCar'])->name('cars.store');
    Route::get('/cars/{product}/edit', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'editCar'])->name('cars.edit');
    Route::put('/cars/{product}', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'updateCar'])->name('cars.update');
    Route::delete('/cars/{product}', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'destroyCar'])->name('cars.destroy');
    Route::patch('/cars/{product}/status', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'updateCarStatus'])->name('cars.updateStatus');
    
    Route::get('/appointments', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'appointments'])->name('appointments');
    Route::patch('/appointments/{appointment}/status', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'updateAppointmentStatus'])->name('appointments.updateStatus');
    
    Route::get('/rentals', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'rentals'])->name('rentals');
    Route::post('/rentals/bulk-status', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'bulkUpdateRentalStatus'])->name('rentals.bulkStatus');
    Route::get('/rentals/{rental}/voucher', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'voucher'])->name('rentals.voucher');
    Route::patch('/rentals/{rental}/status', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'updateRentalStatus'])->name('rentals.updateStatus');
    Route::post('/rentals/{rental}/verify-handover', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'verifyHandover'])->name('rentals.verifyHandover');
    Route::post('/rentals/{rental}/refund', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'refund'])->name('rentals.refund');
    Route::get('/rentals/{rental}/pay-commission-momo', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'payCommissionMomo'])->name('rentals.payCommissionMomo');
    
    Route::get('/payouts', [\App\Http\Controllers\Partner\PartnerDashboardController::class, 'payouts'])->name('payouts');
    
    // Thống kê Doanh thu & Biểu đồ trực quan Đối tác (Tương tự Admin)
    Route::get('/reports', [\App\Http\Controllers\Partner\PartnerReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/charts', [\App\Http\Controllers\Partner\PartnerReportController::class, 'charts'])->name('reports.charts');
});

// Cho phép khách xem chi tiết sản phẩm bình thường
Route::get('/products/{product}', [ProductController::class, 'show_normal'])->name('products.show');