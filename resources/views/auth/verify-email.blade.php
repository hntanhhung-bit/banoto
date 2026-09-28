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

                    <!-- Thông tin tài khoản -->
                    <div class="text-center mb-4">
                        <div class="text-muted" style="font-size: 14px;">Tài khoản đăng ký:</div>
                        <div class="font-weight-bold text-dark" style="font-size: 17px;">
                            <i class="fa fa-user-circle text-primary"></i> {{ Auth::user()->name }} ({{ Auth::user()->email }})
                        </div>
                    </div>

                    <!-- Khung hiển thị mã OTP trực tiếp (Dành cho môi trường Demo/Trực quan) -->
                    @php
                        $displayOtp = $otp ?? (Auth::user() ? Auth::user()->getActiveOtp() : '------');
                    @endphp
                    <div class="p-3 mb-4 text-center rounded border" style="background-color: #f0f7ff; border-color: #cce5ff !important; border-radius: 12px;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="badge badge-primary px-2 py-1" style="font-size: 11px;">MÃ OTP BẢO MẬT</span>
                            <span class="text-muted" style="font-size: 12px;"><i class="fa fa-clock-o"></i> Hiệu lực 15 phút</span>
                        </div>
                        <div id="otpDisplay" class="font-weight-bold text-primary my-2" style="font-size: 36px; letter-spacing: 10px; font-family: monospace;">
                            {{ $displayOtp }}
                        </div>
                        <div class="text-muted small">
                            Mã xác thực của bạn. Bấm nút dưới để điền nhanh hoặc tự gõ vào ô:
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary font-weight-bold mt-2 px-3" style="border-radius: 20px;" onclick="fillOtp('{{ $displayOtp }}')">
                            <i class="fa fa-magic"></i> Điền nhanh mã này
                        </button>
                    </div>

                    <!-- Form nhập mã OTP -->
                    <form method="POST" action="{{ route('verification.otp') }}">
                        @csrf
                        <div class="form-group text-center mb-4">
                            <label for="otp" class="font-weight-bold text-secondary text-uppercase" style="font-size: 13px; letter-spacing: 0.5px;">
                                Nhập 6 chữ số mã OTP vào đây:
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
                                   style="font-size: 32px; letter-spacing: 12px; height: 65px; border-radius: 12px; font-family: monospace; border: 2px solid #005fb7;">
                            @error('otp')
                                <div class="invalid-feedback font-weight-bold" style="font-size: 14px;">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary btn-block btn-lg font-weight-bold py-3 shadow-sm mb-3" style="border-radius: 30px; font-size: 16px;">
                            <i class="fa fa-check-circle"></i> Xác Nhận Kích Hoạt Tài Khoản
                        </button>
                    </form>

                    <!-- Các nút hành động phụ -->
                    <div class="row pt-2 text-center">
                        <div class="col-6 mb-2">
                            <form method="POST" action="{{ route('verification.resend_otp') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-secondary btn-sm btn-block font-weight-bold py-2" style="border-radius: 20px;">
                                    <i class="fa fa-refresh"></i> Đổi mã OTP mới
                                </button>
                            </form>
                        </div>
                        <div class="col-6 mb-2">
                            <form method="POST" action="{{ route('verification.instant') }}">
                                @csrf
                                <button type="submit" class="btn btn-outline-success btn-sm btn-block font-weight-bold py-2" style="border-radius: 20px;" title="Kích hoạt trực tiếp không cần nhập mã">
                                    <i class="fa fa-bolt"></i> Kích hoạt ngay
                                </button>
                            </form>
                        </div>
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
function fillOtp(code) {
    var input = document.getElementById('otpInput');
    if (input && code && code !== '------') {
        input.value = code;
        input.focus();
    }
}

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