@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="font-weight-bold text-uppercase mb-0" style="border-left: 4px solid #6f42c1; padding-left: 12px;">
            <i class="fa fa-users text-purple"></i> Quản lý Thành viên & Người dùng
        </h2>
    </div>

    <!-- BỘ LỌC TÌM KIẾM NGƯỜI DÙNG -->
    <div class="card mb-4 bg-white shadow-sm border-0" style="border-radius: 10px;">
        <div class="card-body">
            <form action="{{ route('admin.users.index') }}" method="GET" class="row align-items-center">
                <div class="col-lg-4 col-md-6 mb-2 mb-lg-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light"><i class="fa fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control" placeholder="Tìm theo họ tên, email...">
                    </div>
                </div>
                <div class="col-lg-3 col-md-3 mb-2 mb-lg-0">
                    <select name="role" class="form-control">
                        <option value="">-- Tất cả vai trò --</option>
                        <option value="admin" {{ request('role') == 'admin' ? 'selected' : '' }}>Quản trị viên (Admin)</option>
                        <option value="partner" {{ request('role') == 'partner' ? 'selected' : '' }}>Đối tác (Partner)</option>
                        <option value="customer" {{ request('role') == 'customer' ? 'selected' : '' }}>Khách hàng (Customer)</option>
                    </select>
                </div>
                <div class="col-lg-3 col-md-3 mb-2 mb-lg-0">
                    <select name="verified" class="form-control">
                        <option value="">-- Trạng thái Email --</option>
                        <option value="yes" {{ request('verified') == 'yes' ? 'selected' : '' }}>Đã xác thực Email</option>
                        <option value="no" {{ request('verified') == 'no' ? 'selected' : '' }}>Chưa xác thực Email</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-12 text-right">
                    <button type="submit" class="btn btn-primary font-weight-bold mr-1">
                        <i class="fa fa-filter"></i> Lọc
                    </button>
                    @if(request()->hasAny(['keyword', 'role', 'verified']))
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary font-weight-bold">
                            <i class="fa fa-times"></i> Xóa lọc
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- BẢNG DANH SÁCH THÀNH VIÊN -->
    <div class="card shadow-sm border-0" style="border-radius: 10px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped m-0 align-middle">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="text-center" width="60px">ID</th>
                            <th>Họ và Tên</th>
                            <th>Địa chỉ Email</th>
                            <th class="text-center">Vai trò</th>
                            <th class="text-center">Xác thực Email</th>
                            <th class="text-center">Ngày đăng ký</th>
                            <th class="text-center" width="240px">Đổi vai trò & Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($users as $user)
                        <tr>
                            <td class="text-center align-middle font-weight-bold">{{ $user->id }}</td>
                            <td class="align-middle font-weight-bold text-dark">
                                <i class="fa fa-user-circle-o mr-1 text-secondary"></i> {{ $user->name }}
                                @if($user->id === Auth::id())
                                    <span class="badge badge-success ml-1">Bạn</span>
                                @endif
                            </td>
                            <td class="align-middle text-muted">{{ $user->email }}</td>
                            <td class="text-center align-middle">
                                @if($user->role === 'admin')
                                    <span class="badge badge-danger px-3 py-2 font-weight-bold" style="font-size: 12px; border-radius: 20px;">
                                        <i class="fa fa-shield"></i> ADMIN
                                    </span>
                                @elseif($user->role === 'partner')
                                    <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 12px; border-radius: 20px;">
                                        <i class="fa fa-handshake-o"></i> ĐỐI TÁC
                                    </span>
                                @else
                                    <span class="badge badge-primary px-3 py-2 font-weight-bold" style="font-size: 12px; border-radius: 20px;">
                                        <i class="fa fa-user"></i> KHÁCH HÀNG
                                    </span>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                @if($user->email_verified_at)
                                    <span class="badge badge-success px-2 py-1" style="font-size: 12px;">
                                        <i class="fa fa-check-circle"></i> Đã xác thực
                                    </span>
                                    <div class="small text-muted" style="font-size: 11px;">
                                        {{ $user->email_verified_at->format('d/m/Y H:i') }}
                                    </div>
                                @else
                                    <span class="badge badge-warning px-2 py-1 text-dark" style="font-size: 12px;">
                                        <i class="fa fa-clock-o"></i> Chưa xác thực
                                    </span>
                                @endif
                            </td>
                            <td class="text-center align-middle small text-muted">
                                {{ $user->created_at ? $user->created_at->format('d/m/Y') : 'N/A' }}
                            </td>
                            <td class="text-center align-middle">
                                @if($user->id !== Auth::id())
                                    <div class="d-flex justify-content-center align-items-center">
                                        <!-- Form Đổi Vai Trò Trực Tiếp (Customer / Partner / Admin) -->
                                        <form action="{{ route('admin.users.updateRole', $user->id) }}" method="POST" class="mr-2 mb-0">
                                            @csrf
                                            <select name="role" class="form-control form-control-sm font-weight-bold text-dark border-secondary" 
                                                    onchange="if(confirm('Xác nhận đổi vai trò tài khoản {{ $user->name }} sang ' + this.options[this.selectedIndex].text + '?')) { this.form.submit(); } else { this.value='{{ $user->role }}'; }" 
                                                    style="border-radius: 6px; font-size: 12px; height: 32px; min-width: 135px;">
                                                <option value="customer" {{ $user->role === 'customer' ? 'selected' : '' }}>Khách hàng</option>
                                                <option value="partner" {{ $user->role === 'partner' ? 'selected' : '' }}>Đối tác (Partner)</option>
                                                <option value="admin" {{ $user->role === 'admin' ? 'selected' : '' }}>Quản trị (Admin)</option>
                                            </select>
                                        </form>

                                        <!-- Nút Nhắn Tin Trực Tiếp -->
                                        <button type="button" class="btn btn-sm btn-outline-primary mr-2 font-weight-bold" 
                                                title="Nhắn tin trực tiếp với người dùng này" 
                                                onclick="openAdminChatWith({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email) }}')" 
                                                style="height: 32px; border-radius: 6px;">
                                            <i class="fa fa-comments"></i> Chat
                                        </button>

                                        <!-- Nút Xóa Tài Khoản -->
                                        <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="mb-0" onsubmit="return confirm('CẢNH BÁO: Bạn có chắc chắn muốn xóa vĩnh viễn tài khoản {{ $user->name }}?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="Xóa tài khoản" style="height: 32px; border-radius: 6px;">
                                                <i class="fa fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <span class="badge badge-light border text-muted px-2 py-1"><i class="fa fa-lock"></i> Tài khoản của bạn</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">Không tìm thấy thành viên nào phù hợp.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($users->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center">
                {{ $users->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@endsection
