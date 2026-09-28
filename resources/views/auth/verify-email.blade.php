@extends('layouts.app')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white text-center py-4" style="border-bottom: 2px solid #005fb7;">
                    <h4 class="mb-0 text-primary font-weight-bold text-uppercase">Xác minh địa chỉ Email</h4>
                </div>
                <div class="card-body p-5 text-center">
                    
                    <!-- Icon Email -->
                    <div class="mb-4">
                        <i class="fa fa-envelope-open-o text-success" style="font-size: 70px;"></i>
                    </div>

                    <h5 class="font-weight-bold mb-3">Cảm ơn bạn đã đăng ký tài khoản!</h5>
                    
                    <p style="font-size: 16px; line-height: 1.6; color: #555;">
                        Chúng tôi vừa gửi một đường link xác minh bảo mật đến địa chỉ email mà bạn đã đăng ký.
                    </p>
                    
                    <div class="alert alert-warning mt-4 mb-4" style="border-radius: 8px;">
                        <strong>Lưu ý:</strong> Bạn cần xác minh địa chỉ email trước khi có thể thêm xe vào giỏ hàng hoặc thực hiện giao dịch. Vui lòng kiểm tra hộp thư đến (hoặc thư mục Spam/Thư rác) và click vào đường link xác nhận.
                    </div>

                    <div class="d-flex justify-content-center align-items-center flex-wrap mt-4">
                        <form method="POST" action="{{ route('verification.send') }}" class="mr-2 mb-2">
                            @csrf
                            <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2" style="border-radius: 30px;">
                                <i class="fa fa-paper-plane"></i> Gửi lại link xác minh
                            </button>
                        </form>

                        <a href="{{ route('welcome') }}" class="btn btn-outline-secondary font-weight-bold px-4 py-2 mb-2" style="border-radius: 30px;">
                            <i class="fa fa-home"></i> Quay lại Trang chủ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection