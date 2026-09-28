@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white text-center py-3">
                    <h4 class="mb-0 text-danger font-weight-bold">ĐĂNG KÝ TÀI KHOẢN</h4>
                </div>
                <div class="card-body p-4">
                    @if($errors->any())
                        <div class="alert alert-danger">{{ $errors->first() }}</div>
                    @endif

                    <form action="{{ route('register') }}" method="POST">
                        @csrf
                        <div class="form-group mb-3">
                            <label>Họ và Tên</label>
                            <input type="text" name="name" class="form-control" placeholder="VD: Duy Nguyễn" required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" placeholder="Nhập email hợp lệ..." required>
                        </div>
                        <div class="form-group mb-3">
                            <label>Mật khẩu (Ít nhất 6 ký tự)</label>
                            <input type="password" name="password" class="form-control" placeholder="Tạo mật khẩu..." required>
                        </div>
                        <div class="form-group mb-4">
                            <label>Nhập lại Mật khẩu</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Nhập lại mật khẩu..." required>
                        </div>
                        <button type="submit" class="btn btn-danger w-100 mb-3" style="height: 45px; font-weight: bold;">Đăng Ký</button>
                        
                        <div class="text-center mt-3">
                            <span>Đã có tài khoản? </span>
                            <a href="{{ route('login') }}" class="text-primary font-weight-bold">Đăng nhập ngay</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection