@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="font-weight-bold text-uppercase mb-0" style="border-left: 4px solid #ea4335; padding-left: 12px;">
            <i class="fa fa-envelope text-danger"></i> Cấu hình Google Gmail API
        </h2>
        <span class="badge {{ $isConnected ? 'badge-success' : 'badge-warning text-dark' }} px-3 py-2 font-weight-bold" style="font-size: 14px; border-radius: 20px;">
            <i class="fa {{ $isConnected ? 'fa-check-circle' : 'fa-exclamation-triangle' }}"></i> 
            {{ $isConnected ? 'ĐÃ KẾT NỐI GMAIL API' : 'CHƯA KẾT NỐI' }}
        </span>
    </div>

    <!-- THÔNG BÁO FLASH MESSAGE -->
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

    <div class="row">
        <!-- CỘT TRÁI: FORM KẾT NỐI VÀ TEST GMAIL API -->
        <div class="col-lg-6 mb-4">
            @if($isConnected)
                <!-- TRẠNG THÁI ĐÃ KẾT NỐI -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; border-top: 4px solid #28a745 !important;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3 bg-success text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 50px; height: 50px; font-size: 24px;">
                                <i class="fa fa-check"></i>
                            </div>
                            <div>
                                <h5 class="font-weight-bold text-success mb-1">Gmail API Đang Hoạt Động!</h5>
                                <div class="text-muted" style="font-size: 14px;">Mọi email xác thực tài khoản và thông báo sẽ được gửi qua Google HTTPS.</div>
                            </div>
                        </div>

                        <div class="bg-light p-3 rounded mb-4 border">
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Email gửi đi:</div>
                                <div class="col-8 font-weight-bold text-primary">{{ $connectedEmail ?: 'Chính tài khoản Google đã ủy quyền' }}</div>
                            </div>
                            <div class="row mb-2">
                                <div class="col-4 text-muted">Cổng gửi:</div>
                                <div class="col-8 font-weight-bold text-success"><i class="fa fa-lock"></i> HTTPS (Port 443 - Không bao giờ bị chặn)</div>
                            </div>
                            <div class="row">
                                <div class="col-4 text-muted">Client ID:</div>
                                <div class="col-8 text-break small text-secondary">{{ substr($clientId, 0, 25) }}...</div>
                            </div>
                        </div>

                        <!-- GỬI THỬ NGHIỆM EMAIL TEST -->
                        <h6 class="font-weight-bold text-dark mb-3"><i class="fa fa-paper-plane text-primary"></i> Gửi thử nghiệm một email:</h6>
                        <form action="{{ route('admin.gmail.sendTest') }}" method="POST" class="mb-4">
                            @csrf
                            <div class="input-group mb-2">
                                <input type="email" name="test_email" class="form-control" placeholder="Nhập địa chỉ Gmail muốn nhận thử..." value="{{ Auth::user()->email }}" required>
                                <div class="input-group-append">
                                    <button class="btn btn-primary font-weight-bold px-3" type="submit">
                                        <i class="fa fa-send"></i> Gửi Test Ngay
                                    </button>
                                </div>
                            </div>
                            <small class="text-muted"><i class="fa fa-info-circle"></i> Bấm nút để kiểm tra thư có bay vào hộp thư đến của bạn không.</small>
                        </form>

                        <hr>

                        <!-- NÚT NGẮT KẾT NỐI -->
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="text-muted small">Muốn đổi tài khoản Google khác?</span>
                            <form action="{{ route('admin.gmail.disconnect') }}" method="POST" onsubmit="return confirm('Bạn có chắc muốn ngắt kết nối Gmail API này không?');">
                                @csrf
                                <button type="submit" class="btn btn-outline-danger btn-sm font-weight-bold px-3" style="border-radius: 20px;">
                                    <i class="fa fa-unlink"></i> Ngắt kết nối
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @else
                <!-- FORM ĐĂNG NHẬP / KẾT NỐI GOOGLE OAUTH -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px; border-top: 4px solid #ea4335 !important;">
                    <div class="card-header bg-white py-3">
                        <h5 class="font-weight-bold mb-0 text-dark"><i class="fa fa-key text-danger"></i> Bước Kết Nối Gmail API</h5>
                    </div>
                    <div class="card-body p-4">
                        <p class="text-muted" style="font-size: 14px;">
                            Để gửi email qua tài khoản Gmail thật của bạn mà không bị Render chặn cổng, hãy nhập <strong>Google Client ID</strong> và <strong>Client Secret</strong> (lấy từ Google Cloud Console) rồi bấm nút kết nối:
                        </p>

                        <form action="{{ route('admin.gmail.connect') }}" method="GET">
                            <div class="form-group mb-3">
                                <label class="font-weight-bold text-secondary small text-uppercase">Google Client ID (*):</label>
                                <input type="text" name="client_id" class="form-control" placeholder="Ví dụ: 123456789-xxxx.apps.googleusercontent.com" value="{{ $clientId }}" required>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold text-secondary small text-uppercase">Google Client Secret (*):</label>
                                <input type="password" name="client_secret" class="form-control" placeholder="Ví dụ: GOCSPX-xxxxxxxxxxxx" value="{{ $clientSecret }}" required>
                            </div>

                            <button type="submit" class="btn btn-danger btn-block btn-lg font-weight-bold py-3 shadow-sm" style="border-radius: 30px;">
                                <i class="fa fa-google mr-2"></i> Đăng nhập Google & Cấp quyền gửi Mail
                            </button>
                        </form>
                    </div>
                </div>
            @endif
        </div>

        <!-- CỘT PHẢI: HƯỚNG DẪN LẤY CLIENT ID TRÊN GOOGLE CLOUD CONSOLE -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 d-flex align-items-center">
                    <img src="https://www.gstatic.com/images/branding/product/2x/googleg_48dp.png" alt="Google" style="width: 24px; height: 24px;" class="mr-2">
                    <h5 class="font-weight-bold mb-0 text-dark">Hướng dẫn lấy Client ID & Secret (3 Phút)</h5>
                </div>
                <div class="card-body p-4" style="line-height: 1.7; font-size: 14px;">
                    <div class="mb-3">
                        <strong class="text-primary">Bước 1: Tạo dự án trên Google Cloud</strong>
                        <ol class="pl-3 mb-1">
                            <li>Truy cập vào: <a href="https://console.cloud.google.com/" target="_blank" class="font-weight-bold">console.cloud.google.com <i class="fa fa-external-link"></i></a></li>
                            <li>Đăng nhập bằng tài khoản Gmail của bạn (<code>hntanhhung@gmail.com</code>).</li>
                            <li>Bấm nút chọn dự án ở góc trên cùng -> Chọn <strong>"New Project"</strong> -> Đặt tên (ví dụ: <code>AutoCar-Mail</code>) -> Bấm <strong>Create</strong>.</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <strong class="text-primary">Bước 2: Bật Gmail API</strong>
                        <ol class="pl-3 mb-1">
                            <li>Vào thanh tìm kiếm trên cùng gõ <strong>"Gmail API"</strong>.</li>
                            <li>Bấm vào kết quả <strong>Gmail API</strong> và bấm nút xanh <strong>"ENABLE"</strong> (Bật).</li>
                        </ol>
                    </div>

                    <div class="mb-3">
                        <strong class="text-primary">Bước 3: Tạo thông tin xác thực OAuth (Credentials)</strong>
                        <ol class="pl-3 mb-1">
                            <li>Vào menu bên trái: <strong>APIs & Services</strong> -> <strong>Credentials</strong>.</li>
                            <li>Bấm <strong>"+ CREATE CREDENTIALS"</strong> -> Chọn <strong>"OAuth client ID"</strong>.</li>
                            <li>(Nếu Google bắt cấu hình màn hình đồng ý <em>OAuth consent screen</em>: chọn <strong>External</strong> -> Điền tên ứng dụng: <code>Auto Car</code> -> Điền email của bạn -> Bấm Lưu đến hết).</li>
                            <li>Tại mục <strong>Application type</strong>: Chọn <strong>Web application</strong>.</li>
                            <li>Tại mục <strong>Authorized redirect URIs</strong>: Bấm <strong>"+ ADD URI"</strong> và dán chính xác đường link sau:
                                <div class="input-group my-2">
                                    <input type="text" id="redirectUriInput" class="form-control form-control-sm bg-light font-weight-bold text-danger" value="{{ $redirectUri }}" readonly>
                                    <div class="input-group-append">
                                        <button class="btn btn-sm btn-dark" type="button" onclick="navigator.clipboard.writeText('{{ $redirectUri }}'); alert('Đã sao chép link Redirect URI!');">
                                            <i class="fa fa-copy"></i> Copy
                                        </button>
                                    </div>
                                </div>
                            </li>
                            <li>Bấm <strong>CREATE</strong>. Google sẽ hiện ra <strong>Client ID</strong> và <strong>Client Secret</strong>.</li>
                        </ol>
                    </div>

                    <div class="alert alert-info border-0 mb-0 py-2" style="border-radius: 8px;">
                        <i class="fa fa-lightbulb-o"></i> <strong>Hoàn tất:</strong> Copy 2 mã đó dán vào ô bên trái rồi bấm nút <strong>"Đăng nhập Google"</strong> là xong 100%!
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
