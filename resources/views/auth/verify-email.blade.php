@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            <div class="card shadow border-0" style="border-radius: 16px; overflow: hidden;">
                <!-- Header Card -->
                <div class="card-header text-white text-center py-4" style="background: linear-gradient(135deg, #005fb7, #0084ff);">
                    <div class="mb-2">
                        <span class="d-inline-flex align-items-center justify-content-center bg-white text-primary rounded-circle shadow-sm" style="width: 70px; height: 70px; font-size: 32px;">
                            <i class="fa fa-shield"></i>
                        </span>
                    </div>
                    <h4 class="mb-1 font-weight-bold text-uppercase" style="letter-spacing: 1px;">Xác Thực Tài Khoản (OTP)</h4>
                    <p class="mb-0 text-white-50" style="font-size: 14px;">Nhập mã 6 chữ số để kích hoạt tài khoản của bạn</p>
                </div>

                <div class="card-body p-4 p-md-5">
                    @if(session('info'))
                        <div class="alert alert-info border-0 shadow-sm mb-4" style="border-radius: 10px;">
                            <i class="fa fa-info-circle mr-1"></i> {{ session('info') }}
                        </div>
                    @endif

                    @if(session('warning'))
                        <div class="alert alert-warning border-0 shadow-sm mb-4" style="border-radius: 10px;">
                            <i class="fa fa-exclamation-triangle mr-1"></i> {{ session('warning') }}
                        </div>
                    @endif

                    <!-- Thông báo đã gửi mã OTP qua Gmail -->
                    <div class="p-4 mb-4 text-center rounded border" style="background: linear-gradient(180deg, #f0f7ff, #ffffff); border-color: #cce5ff !important; border-radius: 14px; box-shadow: 0 2px 8px rgba(0, 95, 183, 0.06);">
                        <div class="mb-3">
                            <span class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-circle shadow-sm" style="width: 54px; height: 54px; font-size: 24px;">
                                <i class="fa fa-envelope-o"></i>
                            </span>
                        </div>
                        <h5 class="font-weight-bold text-dark mb-2" style="font-size: 17px;">
                            Mã xác thực OTP đã được gửi!
                        </h5>
                        <p class="text-muted mb-2" style="font-size: 14px; line-height: 1.5;">
                            Chúng tôi đã gửi mã bảo mật 6 chữ số đến địa chỉ email:
                        </p>
                        <div class="d-inline-block px-3 py-1 bg-white border border-primary text-primary font-weight-bold rounded-pill mb-3" style="font-size: 15px;">
                            <i class="fa fa-envelope text-primary mr-1"></i> {{ Auth::user()->email }}
                        </div>
                        <p class="text-muted small mb-0" style="line-height: 1.5;">
                            Vui lòng mở ứng dụng <strong>Gmail</strong> của bạn, kiểm tra hộp thư đến (Inbox) hoặc mục <strong>Thư rác (Spam / Junk)</strong> để lấy mã và nhập vào bên dưới.
                        </p>
                        <div class="mt-2 py-1 px-2 rounded bg-light border text-secondary small" style="font-size: 12px;">
                            <i class="fa fa-info-circle text-info mr-1"></i> Nếu bạn dùng email trường học (như <em>@hunre.edu.vn</em>), vui lòng kiểm tra thêm tab <strong>Khác (Other)</strong> hoặc hòm thư rác <strong>Junk Email</strong>.
                        </div>
                    </div>

                    <!-- Form nhập mã OTP -->
                    <form method="POST" action="{{ route('verification.otp') }}">
                        @csrf
                        <div class="form-group text-center mb-4">
                            <label for="otpInput" class="font-weight-bold text-secondary text-uppercase" style="font-size: 13px; letter-spacing: 0.5px;">
                                <i class="fa fa-lock text-primary mr-1"></i> Nhập 6 chữ số mã OTP vào đây:
                            </label>
                            <input type="text" 
                                   id="otpInput" 
                                   name="otp" 
                                   class="form-control form-control-lg text-center font-weight-bold @error('otp') is-invalid @enderror" 
                                   maxlength="6" 
                                   pattern="[0-9]{6}"
                                   inputmode="numeric"
                                   placeholder="______"
                                   required 
                                   autofocus
                                   autocomplete="one-time-code"
                                   style="font-size: 34px; letter-spacing: 12px; height: 65px; border-radius: 12px; font-family: monospace; border: 2px solid #005fb7; background-color: #fafbfc;">
                            @error('otp')
                                <div class="invalid-feedback font-weight-bold" style="font-size: 14px;">
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="text-muted small mt-2">
                                <i class="fa fa-clock-o text-muted"></i> Mã OTP có hiệu lực trong vòng <strong>15 phút</strong>.
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg font-weight-bold py-3 shadow-sm mb-3" style="border-radius: 30px; font-size: 16px; letter-spacing: 0.5px;">
                            <i class="fa fa-check-circle mr-1"></i> Xác Nhận Kích Hoạt Tài Khoản
                        </button>
                    </form>

                    <!-- Các nút hành động phụ -->
                    <div class="text-center pt-2">
                        <form method="POST" action="{{ route('verification.resend_otp') }}" class="mb-2">
                            @csrf
                            <button type="submit" class="btn btn-outline-primary btn-block font-weight-bold py-2" style="border-radius: 20px;">
                                <i class="fa fa-refresh mr-1"></i> Chưa nhận được mã? Gửi lại OTP qua Gmail
                            </button>
                        </form>
                        
                        <!-- Dự phòng kích hoạt nếu người dùng gặp sự cố kết nối email -->
                        <form method="POST" action="{{ route('verification.instant') }}" class="mt-2">
                            @csrf
                            <button type="submit" class="btn btn-link btn-sm text-muted" style="font-size: 12.5px; text-decoration: underline;" title="Nhấn vào đây nếu không nhận được thư sau nhiều lần gửi lại">
                                Gặp sự cố không nhận được email? Kích hoạt tài khoản trực tiếp tại đây
                            </button>
                        </form>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between align-items-center text-muted small">
                        <a href="{{ route('welcome') }}" class="text-secondary">
                            <i class="fa fa-home"></i> Về Trang chủ
                        </a>
                        <a href="{{ route('logout') }}" 
                           class="text-danger font-weight-bold"
                           onclick="event.preventDefault(); document.getElementById('logout-form-verify').submit();">
                            <i class="fa fa-sign-out"></i> Đăng xuất
                        </a>
                        <form id="logout-form-verify" action="{{ route('logout') }}" method="POST" style="display: none;">
                            @csrf
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('otpInput');
    if (input) {
        input.addEventListener('input', function(e) {
            // Chỉ cho phép gõ số
            this.value = this.value.replace(/[^0-9]/g, '');
        });
    }
});
</script>
@endsection