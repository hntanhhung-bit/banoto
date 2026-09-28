@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white text-center py-3">
                    <h4 class="mb-0 text-primary font-weight-bold">ĐĂNG NHẬP</h4>
                </div>
                <div class="card-body p-4">
                    <!-- Hiển thị thông báo thành công hoặc lỗi -->
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif
                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label>Email của bạn</label>
                            <input type="email" name="email" value="{{ old('email') }}" class="form-control" placeholder="Nhập email..." required autofocus>
                        </div>
                        <div class="form-group mb-4">
                            <label>Mật khẩu</label>
                            <input type="password" name="password" class="form-control" placeholder="Nhập mật khẩu..." required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 mb-3" style="height: 45px; font-weight: bold;">Đăng Nhập</button>
                        
                        <div class="text-center mt-3">
                            <span>Chưa có tài khoản? </span>
                            <a href="{{ route('register') }}" class="text-danger font-weight-bold">Đăng ký ngay</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection