@extends('partner.layout')

@section('content')
<div class="mb-4 d-flex justify-content-between align-items-center flex-wrap">
    <div>
        <h3 class="font-weight-bold text-dark mb-1">
            <i class="fa fa-car text-success mr-2"></i> QUẢN LÝ ĐỘI XE SHOWROOM
        </h3>
        <p class="text-muted small mb-0">Quản lý, thêm mới, sửa giá và cập nhật trạng thái các dòng xe thuộc sở hữu của Showroom bạn trên sàn</p>
    </div>
    <div class="mt-2 mt-md-0">
        <a href="{{ route('partner.cars.create') }}" class="btn btn-success font-weight-bold shadow-sm">
            <i class="fa fa-plus-circle mr-1"></i> THÊM XE MỚI VÀO SHOWROOM
        </a>
    </div>
</div>

<div class="row">
    @forelse($cars as $car)
    <div class="col-lg-4 col-md-6 mb-4">
        <div class="card shadow-sm h-100 border-0 rounded-lg">
            <div class="position-relative">
                <img src="{{ $car->image_url }}" class="card-img-top" 
                     style="height: 200px; object-fit: cover; border-top-left-radius: 12px; border-top-right-radius: 12px;" 
                     alt="{{ $car->name }}"
                     onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=500&auto=format&fit=crop&q=60';">
                
                <!-- Huy hiệu Trạng thái Phê duyệt của Admin -->
                <div class="position-absolute" style="top: 12px; left: 12px;">
                    @if($car->approval_status === 'pending')
                        <span class="badge badge-warning text-dark px-2 py-1 font-weight-bold shadow-sm" style="background-color: #fff3cd; border: 1px solid #ffeeba;">
                            <i class="fa fa-clock-o text-danger mr-1"></i> Chờ Admin duyệt
                        </span>
                    @elseif($car->approval_status === 'rejected')
                        <span class="badge badge-danger px-2 py-1 font-weight-bold shadow-sm" title="{{ $car->admin_feedback }}">
                            <i class="fa fa-times-circle mr-1"></i> Bị từ chối
                        </span>
                    @else
                        <span class="badge badge-success px-2 py-1 font-weight-bold shadow-sm">
                            <i class="fa fa-check-circle mr-1"></i> Đã duyệt sàn
                        </span>
                    @endif
                </div>

                <div class="position-absolute" style="top: 12px; right: 12px;">
                    @if($car->rental_status === 'available' || empty($car->rental_status))
                        <span class="badge badge-success px-3 py-2 font-weight-bold shadow-sm"><i class="fa fa-check-circle mr-1"></i> Sẵn sàng đón khách</span>
                    @elseif($car->rental_status === 'rented')
                        <span class="badge badge-warning px-3 py-2 font-weight-bold shadow-sm"><i class="fa fa-clock-o mr-1"></i> Đang có khách</span>
                    @else
                        <span class="badge badge-secondary px-3 py-2 font-weight-bold shadow-sm"><i class="fa fa-wrench mr-1"></i> Bảo dưỡng</span>
                    @endif
                </div>
            </div>

            <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <h5 class="font-weight-bold text-dark mb-1">{{ $car->name }}</h5>
                        <div>
                            @if($car->car_plate)
                                <span class="badge badge-dark px-2 py-1 font-weight-bold mr-1">{{ $car->car_plate }}</span>
                            @endif
                            @if($car->car_year)
                                <span class="badge badge-light border text-muted font-weight-bold mr-1">Đời {{ $car->car_year }}</span>
                            @endif
                            <small class="text-muted"><i class="fa fa-paint-brush mr-1"></i> {{ $car->color ?: 'Màu chuẩn' }}</small>
                        </div>
                    </div>
                    <span class="badge badge-light border text-muted font-weight-bold">{{ $car->category->name ?? 'Dòng xe' }}</span>
                </div>

                @if($car->approval_status === 'pending')
                    <div class="alert alert-warning p-2 small mb-2 border-warning text-dark">
                        <i class="fa fa-info-circle text-danger mr-1"></i> <strong>Chờ kiểm định:</strong> Mẫu xe sẽ hiển thị trên sàn ngay khi Admin phê duyệt.
                    </div>
                @elseif($car->approval_status === 'rejected')
                    <div class="alert alert-danger p-2 small mb-2 border-danger">
                        <i class="fa fa-times-circle mr-1"></i> <strong>Lý do từ chối:</strong> {{ $car->admin_feedback ?: 'Thông tin chưa đạt chuẩn.' }}
                    </div>
                @endif

                <div class="p-2 bg-light rounded small mb-3">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Giá thuê tự lái:</span>
                        <strong class="text-danger">{{ number_format($car->rent_price_per_day ?: 800000) }} đ/ngày</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Phí tài xế riêng:</span>
                        <strong class="text-primary">{{ number_format($car->driver_price_per_day ?: 500000) }} đ/ngày</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span class="text-muted">Tiền cọc thế chân:</span>
                        <strong class="text-dark">{{ number_format($car->rental_deposit ?: 5000000) }} đ</strong>
                    </div>
                </div>

                <!-- Form cập nhật trạng thái nhanh -->
                <form action="{{ route('partner.cars.updateStatus', $car->id) }}" method="POST" class="mb-3">
                    @csrf
                    @method('PATCH')
                    <div class="form-group mb-0">
                        <label class="small text-muted font-weight-bold mb-1">Cập nhật nhanh tình trạng xe:</label>
                        <select name="rental_status" class="form-control form-control-sm font-weight-bold" onchange="this.form.submit()">
                            <option value="available" {{ ($car->rental_status === 'available' || empty($car->rental_status)) ? 'selected' : '' }}>✓ Sẵn sàng nhận khách</option>
                            <option value="rented" {{ $car->rental_status === 'rented' ? 'selected' : '' }}>🚗 Đang có khách thuê</option>
                            <option value="maintenance" {{ $car->rental_status === 'maintenance' ? 'selected' : '' }}>🔧 Tạm ngưng / Bảo dưỡng</option>
                        </select>
                    </div>
                </form>

                <!-- CÁC NÚT THAO TÁC SỬA / GỠ XE / XEM -->
                <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                    <a href="{{ route('partner.cars.edit', $car->id) }}" class="btn btn-sm btn-outline-warning font-weight-bold flex-grow-1 mr-1 text-dark">
                        <i class="fa fa-pencil mr-1"></i> Sửa xe
                    </a>

                    <a href="{{ route('products.show', $car->id) }}" target="_blank" class="btn btn-sm btn-outline-info font-weight-bold flex-grow-1 mr-1" title="Xem trên sàn">
                        <i class="fa fa-external-link mr-1"></i> Xem sàn
                    </a>

                    <form action="{{ route('partner.cars.destroy', $car->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn gỡ mẫu xe {{ $car->name }} khỏi Showroom không?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger font-weight-bold" title="Gỡ xe">
                            <i class="fa fa-trash"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5">
        <div class="p-5 bg-white rounded shadow-sm border">
            <i class="fa fa-car fa-4x text-muted mb-3"></i>
            <h5 class="text-dark font-weight-bold">Showroom của bạn chưa có xe nào</h5>
            <p class="small text-muted mb-4">Hãy thêm các mẫu xe của Showroom/Nhà xe bạn lên hệ thống để bắt đầu tiếp cận hàng ngàn khách hàng tiềm năng.</p>
            <a href="{{ route('partner.cars.create') }}" class="btn btn-success font-weight-bold px-4 py-2 shadow-sm">
                <i class="fa fa-plus-circle mr-1"></i> Thêm Chiếc Xe Đầu Tiên Ngay
            </a>
        </div>
    </div>
    @endforelse
</div>

<div class="mt-3">
    {{ $cars->links() }}
</div>
@endsection
