@extends('layouts.app')

@section('content')
<div class="container my-5">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">
                <i class="fa fa-calendar-check-o text-success mr-2"></i> Lịch hẹn xem xe đặt hộ của tôi
            </h3>
            <p class="text-muted small mb-0">Nền tảng Bên thứ ba hỗ trợ đặt hẹn với Showroom đối tác, điều phối xe lái thử và cử chuyên viên kiểm định đi cùng</p>
        </div>
        <a href="{{ route('welcome', ['service' => 'view']) }}" class="btn btn-outline-success font-weight-bold">
            <i class="fa fa-plus-circle mr-1"></i> Đặt hẹn xem xe mới
        </a>
    </div>

    @if($appointments->count() > 0)
        <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase text-secondary font-weight-bold" style="font-size: 13px;">
                        <tr>
                            <th class="py-3 px-4">Mã lịch hẹn</th>
                            <th class="py-3">Mẫu xe & Showroom</th>
                            <th class="py-3">Ngày & Giờ hẹn</th>
                            <th class="py-3">Địa điểm xem xe</th>
                            <th class="py-3 text-center">Tiến độ Bên thứ 3</th>
                            <th class="py-3 text-right px-4">Thao tác</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($appointments as $app)
                            <tr>
                                <td class="px-4 font-weight-bold text-primary" style="font-size: 14px;">
                                    #{{ $app->appointment_code }}
                                    <span class="badge badge-light border text-success d-block small font-weight-normal mt-1"><i class="fa fa-handshake-o"></i> Bên thứ 3 đặt hộ</span>
                                    <div class="small text-muted font-weight-normal mt-1">{{ $app->created_at->format('d/m/Y H:i') }}</div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $app->product ? $app->product->image_url : 'https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=100&auto=format&fit=crop&q=60' }}" 
                                             class="rounded mr-3" style="width: 60px; height: 45px; object-fit: cover;" 
                                             alt="{{ $app->product->name ?? 'Xe' }}"
                                             onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1549399542-7e3f8b79c341?w=100&auto=format&fit=crop&q=60';">
                                        <div>
                                            <strong class="text-dark d-block" style="font-size: 14px;">
                                                <a href="{{ route('products.show', $app->product_id) }}" class="text-dark text-decoration-none">
                                                    {{ $app->product->name ?? 'Xe đã bị gỡ' }}
                                                </a>
                                            </strong>
                                            <span class="badge badge-info px-2 py-0 small"><i class="fa fa-paint-brush"></i> Màu: {{ $app->selected_color ?: 'Trắng ngọc trai' }}</span>
                                            @if($app->product && $app->product->partner_showroom)
                                                <small class="text-primary font-weight-bold d-block mt-1">
                                                    <i class="fa fa-building"></i> {{ $app->product->partner_showroom->name }}
                                                </small>
                                            @endif
                                            @if(str_contains($app->note ?? '', 'Chuyên viên Kỹ thuật'))
                                                <span class="badge badge-success px-2 py-0 small mt-1"><i class="fa fa-user-secret"></i> Kèm thợ check xe hộ</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong class="text-dark"><i class="fa fa-calendar text-info mr-1"></i> {{ date('d/m/Y', strtotime($app->appointment_date)) }}</strong>
                                    <div class="small text-muted"><i class="fa fa-clock-o text-warning mr-1"></i> {{ $app->appointment_time }}</div>
                                </td>
                                <td>
                                    @if($app->location_type === 'at_showroom')
                                        <span class="badge badge-primary px-2 py-1"><i class="fa fa-building"></i> Tại Showroom đối tác</span>
                                        <div class="small text-muted mt-1" style="max-width: 250px;">{{ $app->address }}</div>
                                    @else
                                        <span class="badge badge-info px-2 py-1"><i class="fa fa-home"></i> Xem xe tận nơi</span>
                                        <div class="small text-muted mt-1" style="max-width: 250px;">{{ $app->address }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($app->status === 'pending')
                                        <span class="badge badge-warning px-3 py-2 font-weight-bold" style="font-size: 12px;">
                                            <i class="fa fa-hourglass-half"></i> Chờ kết nối Showroom
                                        </span>
                                        <div class="small text-muted mt-1">Bên thứ 3 đang book xe</div>
                                    @elseif($app->status === 'confirmed')
                                        <span class="badge badge-success px-3 py-2 font-weight-bold" style="font-size: 12px;">
                                            <i class="fa fa-check-circle"></i> Showroom đã duyệt
                                        </span>
                                        <div class="small text-success mt-1 font-weight-bold">Xe sẵn sàng đón bạn</div>
                                    @elseif($app->status === 'completed')
                                        <span class="badge badge-primary px-3 py-2 font-weight-bold" style="font-size: 12px;">
                                            <i class="fa fa-flag-checkered"></i> Đã xem xe xong
                                        </span>
                                    @else
                                        <span class="badge badge-secondary px-3 py-2 font-weight-bold" style="font-size: 12px;">
                                            <i class="fa fa-times"></i> Đã hủy
                                        </span>
                                    @endif

                                    @if($app->admin_note)
                                        <div class="small text-info mt-1 font-italic">
                                            <i class="fa fa-commenting-o"></i> {{ $app->admin_note }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-right px-4">
                                    @if(in_array($app->status, ['pending', 'confirmed']))
                                        <form action="{{ route('appointments.cancel', $app->id) }}" method="POST" onsubmit="return confirm('Bạn có chắc chắn muốn hủy lịch hẹn xem xe này không?');" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-danger btn-sm font-weight-bold">
                                                <i class="fa fa-times"></i> Hủy hẹn
                                            </button>
                                        </form>
                                    @else
                                        <span class="text-muted small">Không thể hủy</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            
            @if($appointments->hasPages())
                <div class="p-3 border-top d-flex justify-content-center">
                    {{ $appointments->links('pagination::bootstrap-4') }}
                </div>
            @endif
        </div>
    @else
        <div class="text-center py-5 bg-white rounded shadow-sm border">
            <i class="fa fa-calendar-times-o text-muted fa-4x mb-3"></i>
            <h5 class="text-secondary font-weight-bold">Bạn chưa có lịch hẹn xem xe nào</h5>
            <p class="text-muted small">Hãy chọn mẫu xe ưng ý và đặt lịch trải nghiệm lái thử miễn phí ngay hôm nay.</p>
            <a href="{{ route('welcome', ['service' => 'view']) }}" class="btn btn-success px-4 font-weight-bold">
                <i class="fa fa-car mr-1"></i> Khám phá danh sách xe
            </a>
        </div>
    @endif
</div>
@endsection
