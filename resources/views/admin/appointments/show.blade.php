@extends('layouts.admin')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h3 class="font-weight-bold text-dark mb-1">
                    <i class="fa fa-calendar-check-o text-success mr-2"></i> CHI TIẾT LỊCH HẸN XEM XE
                    #{{ $appointment->appointment_code }}
                </h3>
                <p class="text-muted small mb-0">Cập nhật trạng thái duyệt lịch và ghi chú hướng dẫn khách hàng</p>
            </div>
            <a href="{{ route('admin.appointments.index') }}" class="btn btn-outline-secondary font-weight-bold">
                <i class="fa fa-arrow-left mr-1"></i> Quay lại danh sách
            </a>
        </div>

        <div class="row">
            <!-- Cột trái: Thông tin lịch hẹn & Xe -->
            <div class="col-lg-7 mb-4">
                <div class="card border-0 shadow-sm rounded-lg mb-4">
                    <div class="card-header bg-white py-3">
                        <h5 class="font-weight-bold text-dark mb-0"><i class="fa fa-car text-primary mr-1"></i> Thông tin xe
                            hẹn xem</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <img src="{{ $appointment->product ? $appointment->product->image_url : 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=200&auto=format&fit=crop&q=60' }}"
                                class="rounded mr-3" style="width: 100px; height: 75px; object-fit: cover;"
                                alt="{{ $appointment->product->name ?? 'Xe' }}"
                                onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=200&auto=format&fit=crop&q=60';">
                            <div>
                                <h5 class="font-weight-bold text-dark mb-1">
                                    {{ $appointment->product->name ?? 'Xe không tồn tại' }}
                                </h5>
                                <span
                                    class="badge badge-info mr-1">{{ $appointment->product->category->name ?? 'Dòng xe' }}</span>
                                @if($appointment->selected_color)
                                    <span class="badge badge-dark px-2 py-1"><i class="fa fa-paint-brush"></i> Màu đã chọn:
                                        {{ $appointment->selected_color }}</span>
                                @else
                                    <span
                                        class="badge badge-secondary">{{ $appointment->product->color ?? 'Màu tiêu chuẩn' }}</span>
                                @endif
                                <div class="mt-1 small text-muted">Giá bán tham khảo: <strong
                                        class="text-danger">{{ number_format($appointment->product->price ?? 0) }}
                                        VNĐ</strong></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-lg">
                    <div class="card-header bg-white py-3">
                        <h5 class="font-weight-bold text-dark mb-0"><i class="fa fa-user text-info mr-1"></i> Thông tin
                            khách hàng & Yêu cầu</h5>
                    </div>
                    <div class="card-body">
                        <table class="table table-borderless small mb-0">
                            <tr>
                                <td class="text-muted font-weight-bold" style="width: 35%;">Họ tên khách:</td>
                                <td><strong class="text-dark">{{ $appointment->customer_name }}</strong></td>
                            </tr>
                            <tr>
                                <td class="text-muted font-weight-bold">Số điện thoại:</td>
                                <td><a href="tel:{{ $appointment->customer_phone }}" class="text-success font-weight-bold"
                                        style="font-size: 15px;"><i class="fa fa-phone"></i>
                                        {{ $appointment->customer_phone }}</a></td>
                            </tr>
                            <tr>
                                <td class="text-muted font-weight-bold">Email:</td>
                                <td>{{ $appointment->customer_email ?: 'Chưa cung cấp' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted font-weight-bold">Thời gian hẹn:</td>
                                <td><strong
                                        class="text-primary">{{ date('d/m/Y', strtotime($appointment->appointment_date)) }}</strong>
                                    (Khung giờ: <strong>{{ $appointment->appointment_time }}</strong>)</td>
                            </tr>
                            <tr>
                                <td class="text-muted font-weight-bold">Hình thức xem xe:</td>
                                <td>
                                    @if($appointment->location_type === 'at_showroom')
                                        <span class="badge badge-primary px-2 py-1">Tại Showroom AutoCar</span>
                                    @else
                                        <span class="badge badge-info px-2 py-1">Mang xe tận nhà</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td class="text-muted font-weight-bold">Địa chỉ:</td>
                                <td>{{ $appointment->address }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted font-weight-bold">Ghi chú của khách:</td>
                                <td><span
                                        class="text-muted font-italic">{{ $appointment->note ?: 'Không có ghi chú' }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Cột phải: Xử lý & Cập nhật trạng thái -->
            <div class="col-lg-5">
                <div class="card border-0 shadow-sm rounded-lg sticky-top" style="top: 80px;">
                    <div class="card-header bg-white py-3">
                        <h5 class="font-weight-bold text-dark mb-0"><i class="fa fa-cogs text-warning mr-1"></i> Xử lý lịch
                            hẹn</h5>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('admin.appointments.updateStatus', $appointment->id) }}" method="POST">
                            @csrf
                            @method('PATCH')

                            <div class="form-group mb-3">
                                <label class="font-weight-bold small text-muted">Trạng thái lịch hẹn:</label>
                                <select name="status" class="form-control font-weight-bold" style="height: 45px;">
                                    <option value="pending" {{ $appointment->status === 'pending' ? 'selected' : '' }}>⏳ Chờ
                                        xác nhận</option>
                                    <option value="confirmed" {{ $appointment->status === 'confirmed' ? 'selected' : '' }}>✓
                                        Đã duyệt hẹn (Nhân viên sẵn sàng)</option>
                                    <option value="completed" {{ $appointment->status === 'completed' ? 'selected' : '' }}>🏆
                                        Đã hoàn thành (Khách đã xem/thử xe)</option>
                                    <option value="cancelled" {{ $appointment->status === 'cancelled' ? 'selected' : '' }}>✗
                                        Hủy lịch hẹn</option>
                                </select>
                            </div>

                            <div class="form-group mb-4">
                                <label class="font-weight-bold small text-muted">Ghi chú của Admin / Nhân viên tư
                                    vấn:</label>
                                <textarea name="admin_note" class="form-control" rows="3"
                                    placeholder="Ví dụ: Đã gọi điện cho khách lúc 10h, phân công NV Hoàng đón tiếp tại Showroom...">{{ $appointment->admin_note }}</textarea>
                            </div>

                            <button type="submit" class="btn btn-success btn-block font-weight-bold py-2 shadow-sm">
                                <i class="fa fa-save mr-1"></i> CẬP NHẬT TRẠNG THÁI LỊCH HẸN
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection