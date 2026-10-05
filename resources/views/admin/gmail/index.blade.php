@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="font-weight-bold text-uppercase mb-0" style="border-left: 4px solid #0066cc; padding-left: 12px;">
            <i class="fa fa-envelope text-primary"></i> Cấu hình Hệ thống Gửi Email & OTP
        </h2>
        <div class="d-flex gap-2">
            <span class="badge {{ !empty($matBaoSmtp['is_enabled']) ? 'badge-success' : 'badge-secondary' }} px-3 py-2 font-weight-bold" style="font-size: 13px; border-radius: 20px;">
                <i class="fa fa-globe"></i> MẮT BÃO: {{ !empty($matBaoSmtp['is_enabled']) ? 'ĐANG BẬT' : 'CHƯA BẬT' }}
            </span>
            <span class="badge {{ $isConnected ? 'badge-success' : 'badge-warning text-dark' }} px-3 py-2 font-weight-bold ml-2" style="font-size: 13px; border-radius: 20px;">
                <i class="fa fa-google"></i> GMAIL API: {{ $isConnected ? 'ĐÃ KẾT NỐI' : 'CHƯA KẾT NỐI' }}
            </span>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
            <i class="fa fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
            <i class="fa fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius: 10px;">
            <i class="fa fa-info-circle mr-1"></i> {{ session('warning') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    <!-- BANNER TỔNG QUAN -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); color: white;">
        <div class="card-body p-4">
            <h5 class="font-weight-bold mb-2"><i class="fa fa-shield"></i> Cơ chế gửi Email Kép (Dual-Channel) Đảm Bảo 100% Không Lạc Mất OTP</h5>
            <p class="mb-0 text-white-50" style="font-size: 14px; line-height: 1.6;">
                1. <strong>Mắt Bão Email Pro v4 (thueotovn.id.vn):</strong> Gửi thư xác thực với tên miền showroom chuyên nghiệp.<br>
                2. <strong>Google Gmail API (HTTPS 443):</strong> Tự động kích hoạt dự phòng (Fallback) khi hosting Render chặn cổng SMTP, đảm bảo mã OTP luôn đến hộp thư người dùng.
            </p>
        </div>
    </div>

    <div class="row">
        <!-- CỘT 1: CẤU HÌNH MẮT BÃO EMAIL PRO V4 -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; border-top: 4px solid #0066cc !important;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold mb-0 text-dark">
                        <i class="fa fa-server text-primary mr-1"></i> 1. Email Mắt Bão Pro v4 (thueotovn.id.vn)
                    </h5>
                    @if(!empty($matBaoSmtp['is_enabled']))
                        <span class="badge badge-success px-2 py-1">Đang kích hoạt</span>
                    @endif
                </div>
                <div class="card-body p-4">
                    <!-- HƯỚNG DẪN MẮT BÃO -->
                    <div class="alert alert-light border mb-4" style="font-size: 13px; border-radius: 8px;">
                        <strong class="text-primary"><i class="fa fa-info-circle"></i> Thông tin quản trị từ Mắt Bão:</strong>
                        <ul class="mb-1 pl-3 mt-1">
                            <li>Trang quản trị Webmail Admin: <a href="https://s129d209.emailserver.vn/admin" target="_blank" class="font-weight-bold text-primary">s129d209.emailserver.vn/admin <i class="fa fa-external-link"></i></a></li>
                            <li>Tài khoản quản trị Admin: Mắt Bão đã gửi trực tiếp tới <strong>hntanhhung@gmail.com</strong>.</li>
                            <li>Đăng nhập vào đó để tạo hòm thư gửi mail (Ví dụ: <code>cskh@thueotovn.id.vn</code> hoặc <code>noreply@thueotovn.id.vn</code>).</li>
                        </ul>
                    </div>

                    <!-- FORM CẤU HÌNH SMTP -->
                    <form action="{{ route('admin.smtp.save') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" class="custom-control-input" id="is_enabled" name="is_enabled" value="1" {{ !empty($matBaoSmtp['is_enabled']) ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold text-dark" for="is_enabled">
                                    Ưu tiên gửi qua hòm thư Mắt Bão này
                                </label>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-8 mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Máy chủ SMTP (Host):</label>
                                <input type="text" name="host" class="form-control" value="{{ $matBaoSmtp['host'] ?? 's129d209.emailserver.vn' }}" required>
                            </div>
                            <div class="form-group col-md-4 mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Cổng (Port):</label>
                                <input type="number" name="port" class="form-control" value="{{ $matBaoSmtp['port'] ?? 465 }}" required>
                            </div>
                        </div>

                        <div class="form-row">
                            <div class="form-group col-md-6 mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Mã hóa (Encryption):</label>
                                <select name="encryption" class="form-control">
                                    <option value="ssl" {{ ($matBaoSmtp['encryption'] ?? '') == 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                    <option value="tls" {{ ($matBaoSmtp['encryption'] ?? '') == 'tls' ? 'selected' : '' }}>TLS / STARTTLS (Port 587)</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6 mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Tên người gửi (From Name):</label>
                                <input type="text" name="from_name" class="form-control" value="{{ $matBaoSmtp['from_name'] ?? 'Auto Car Vietnam' }}">
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted text-uppercase">Tài khoản Email đăng nhập (*):</label>
                            <input type="email" name="username" class="form-control" placeholder="Ví dụ: cskh@thueotovn.id.vn" value="{{ $matBaoSmtp['username'] ?? '' }}" required>
                        </div>

                        <div class="form-group mb-3">
                            <label class="small font-weight-bold text-muted text-uppercase">Mật khẩu hòm thư (*):</label>
                            <input type="password" name="password" class="form-control" placeholder="Mật khẩu của hòm thư đã tạo ở Webmail Admin" value="{{ $matBaoSmtp['password'] ?? '' }}" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block font-weight-bold py-2 mb-3 shadow-sm">
                            <i class="fa fa-save"></i> Lưu cấu hình Email Mắt Bão
                        </button>
                    </form>

                    <!-- FORM TEST SEND MẮT BÃO -->
                    <hr>
                    <h6 class="font-weight-bold text-dark mb-2"><i class="fa fa-paper-plane text-primary"></i> Gửi thử nghiệm qua Mắt Bão:</h6>
                    <form action="{{ route('admin.smtp.sendTest') }}" method="POST">
                        @csrf
                        <div class="input-group">
                            <input type="email" name="test_email" class="form-control" placeholder="Nhập email nhận thử..." value="{{ Auth::user()->email ?? 'hntanhhung@gmail.com' }}" required>
                            <div class="input-group-append">
                                <button class="btn btn-outline-primary font-weight-bold" type="submit">
                                    <i class="fa fa-send"></i> Gửi Thử
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- CỘT 2: CẤU HÌNH GOOGLE GMAIL API (HTTPS 443) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100" style="border-radius: 12px; border-top: 4px solid #ea4335 !important;">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="font-weight-bold mb-0 text-dark">
                        <i class="fa fa-google text-danger mr-1"></i> 2. Google Gmail API (Port 443 Fallback)
                    </h5>
                    <span class="badge {{ $isConnected ? 'badge-success' : 'badge-warning text-dark' }} px-2 py-1">
                        {{ $isConnected ? 'Đã kết nối' : 'Chưa kết nối' }}
                    </span>
                </div>
                <div class="card-body p-4">
                    @if($isConnected)
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3 bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 45px; height: 45px; font-size: 20px;">
                                <i class="fa fa-check"></i>
                            </div>
                            <div>
                                <h6 class="font-weight-bold text-success mb-0">Gmail API Đang Sẵn Sàng!</h6>
                                <small class="text-muted">Hoạt động qua cổng HTTPS 443 (100% không bao giờ bị Render chặn).</small>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded mb-3 border small">
                            <div class="row mb-1">
                                <div class="col-4 text-muted">Email gửi:</div>
                                <div class="col-8 font-weight-bold text-primary">{{ $connectedEmail ?: 'Tài khoản Google đã cấp quyền' }}</div>
                            </div>
                            <div class="row">
                                <div class="col-4 text-muted">Cổng gửi:</div>
                                <div class="col-8 font-weight-bold text-success"><i class="fa fa-lock"></i> HTTPS 443</div>
                            </div>
                        </div>

                        <!-- GỬI TEST GMAIL API -->
                        <h6 class="font-weight-bold text-dark mb-2"><i class="fa fa-paper-plane text-danger"></i> Gửi thử nghiệm qua Gmail API:</h6>
                        <form action="{{ route('admin.gmail.sendTest') }}" method="POST" class="mb-3">
                            @csrf
                            <div class="input-group">
                                <input type="email" name="test_email" class="form-control" placeholder="Nhập email nhận thử..." value="{{ Auth::user()->email ?? 'hntanhhung@gmail.com' }}" required>
                                <div class="input-group-append">
                                    <button class="btn btn-danger font-weight-bold" type="submit">
                                        <i class="fa fa-send"></i> Gửi Test
                                    </button>
                                </div>
                            </div>
                        </form>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <small class="text-muted">Đổi tài khoản Gmail khác?</small>
                            <form action="{{ route('admin.gmail.disconnect') }}" method="POST" onsubmit="return confirm('Ngắt kết nối Gmail API?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm font-weight-bold">
                                    <i class="fa fa-unlink"></i> Ngắt kết nối
                                </button>
                            </form>
                        </div>
                    @else
                        <p class="text-muted" style="font-size: 13px;">
                            Nếu muốn cấp quyền gửi trực tiếp từ Gmail cá nhân qua cổng HTTPS 443, hãy nhập thông tin Client ID & Secret từ Google Cloud Console:
                        </p>
                        <form action="{{ route('admin.gmail.connect') }}" method="GET">
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Google Client ID (*):</label>
                                <input type="text" name="client_id" class="form-control" placeholder="123456...apps.googleusercontent.com" value="{{ $clientId }}" required>
                            </div>
                            <div class="form-group mb-3">
                                <label class="small font-weight-bold text-muted text-uppercase">Google Client Secret (*):</label>
                                <input type="password" name="client_secret" class="form-control" placeholder="GOCSPX-..." value="{{ $clientSecret }}" required>
                            </div>
                            <button type="submit" class="btn btn-danger btn-block font-weight-bold py-2 shadow-sm">
                                <i class="fa fa-google mr-1"></i> Đăng nhập Google & Cấp quyền
                            </button>
                        </form>
                    @endif

                    <div class="mt-4 pt-3 border-top small text-muted">
                        <strong>Redirect URI cần cấu hình trên Google Console:</strong>
                        <div class="input-group my-1">
                            <input type="text" class="form-control form-control-sm bg-light" value="{{ $redirectUri }}" readonly id="redirectUriBox">
                            <div class="input-group-append">
                                <button class="btn btn-sm btn-secondary" type="button" onclick="navigator.clipboard.writeText(document.getElementById('redirectUriBox').value); alert('Đã sao chép link!');">
                                    <i class="fa fa-copy"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
