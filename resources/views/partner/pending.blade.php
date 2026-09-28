@extends('partner.layout')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="fa fa-check-circle mr-2"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="fa fa-exclamation-circle mr-2"></i> {{ session('warning') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-4" role="alert">
                    <i class="fa fa-times-circle mr-2"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">&times;</button>
                </div>
            @endif

            <div class="card shadow border-0 rounded-lg overflow-hidden">
                @if($user->partner_status === 'rejected')
                    <!-- TRƯỜNG HỢP HỒ SƠ BỊ TỪ CHỐI -->
                    <div class="card-header bg-danger text-white text-center py-4">
                        <i class="fa fa-times-circle fa-4x mb-2"></i>
                        <h4 class="font-weight-bold mb-1">HỒ SƠ ĐĂNG KÝ ĐỐI TÁC CHƯA ĐƯỢC PHÊ DUYỆT</h4>
                        <p class="mb-0 text-white-50">Rất tiếc, hồ sơ của Quý đối tác chưa thỏa mãn điều kiện tham gia sàn AutoCar</p>
                    </div>

                    <div class="card-body p-4">
                        <div class="alert alert-danger border-danger">
                            <h6 class="font-weight-bold mb-2"><i class="fa fa-info-circle mr-1"></i> Lý do từ chối từ Ban Quản Trị:</h6>
                            <p class="mb-0 font-weight-bold text-dark">{{ $user->partner_reject_reason ?: 'Hồ sơ pháp lý chưa đầy đủ hoặc thông tin giấy phép kinh doanh không khớp.' }}</p>
                        </div>

                        <div class="text-center mt-4">
                            <p class="text-muted">Quý đối tác có thể kiểm tra lại giấy tờ và liên hệ trực tiếp Tổng đài hỗ trợ để được hướng dẫn bổ sung hồ sơ.</p>
                            <div class="d-flex justify-content-center" style="gap: 10px;">
                                <a href="tel:19008888" class="btn btn-outline-danger font-weight-bold">
                                    <i class="fa fa-phone mr-1"></i> Tổng đài: 1900 8888
                                </a>
                                <a href="{{ route('welcome') }}" class="btn btn-secondary font-weight-bold">
                                    <i class="fa fa-home mr-1"></i> Về Trang chủ
                                </a>
                            </div>
                        </div>
                    </div>

                @else
                    <!-- TRƯỜNG HỢP HỒ SƠ ĐANG CHỜ DUYỆT (PENDING) -->
                    <div class="card-header bg-warning text-dark text-center py-4" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);">
                        <i class="fa fa-hourglass-half fa-3x text-warning mb-2" style="color: #d97706 !important;"></i>
                        <h4 class="font-weight-bold mb-1 text-dark">HỒ SƠ ĐỐI TÁC ĐANG CHỜ PHÊ DUYỆT</h4>
                        <span class="badge badge-warning px-3 py-1 font-weight-bold text-dark" style="font-size: 13px;">
                            <i class="fa fa-clock-o mr-1"></i> Trạng thái: Chờ Ban Quản Trị Thẩm Định (Pending)
                        </span>
                    </div>

                    <div class="card-body p-4">
                        <div class="alert alert-info border-info small">
                            <i class="fa fa-shield mr-1"></i>
                            Hồ sơ đăng ký của Quý Đối tác <strong>{{ $user->showroom_name ?: $user->name }}</strong> đã được ghi nhận trên hệ thống lúc 
                            <strong>{{ $user->partner_applied_at ? \Carbon\Carbon::parse($user->partner_applied_at)->format('H:i d/m/Y') : date('d/m/Y') }}</strong>. 
                            Ban Quản Trị AutoCar đang tiến hành thẩm định thông tin giấy phép kinh doanh và điều kiện cơ sở vật chất theo quy chế sàn.
                        </div>

                        <h6 class="font-weight-bold text-dark border-bottom pb-2 mb-3">
                            <i class="fa fa-file-text-o text-primary mr-1"></i> Thông Tin Hồ Sơ Đã Nộp:
                        </h6>

                        <table class="table table-bordered table-sm small mb-4">
                            <tbody>
                                <tr>
                                    <th class="bg-light" style="width: 35%;">Tên Showroom / Doanh nghiệp:</th>
                                    <td class="font-weight-bold text-dark">{{ $user->showroom_name ?: $user->name }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Người đại diện pháp luật:</th>
                                    <td>{{ $user->representative_name ?: 'Chưa cập nhật' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Số CMND / CCCD đại diện:</th>
                                    <td>{{ $user->id_card_number ?: 'Chưa cập nhật' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Mã số thuế / GPKD:</th>
                                    <td>{{ $user->tax_code ?: 'Không có / Hộ KD' }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Số điện thoại liên hệ:</th>
                                    <td>{{ $user->phone }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Địa chỉ Showroom / Bãi xe:</th>
                                    <td>{{ $user->showroom_address }}</td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Giấy phép ĐKKD:</th>
                                    <td>
                                        @if($user->business_license_image)
                                            <a href="{{ asset('uploads/partner_docs/' . $user->business_license_image) }}" target="_blank" class="btn btn-xs btn-outline-primary font-weight-bold py-0">
                                                <i class="fa fa-eye mr-1"></i> Xem ảnh GPKD đã nộp
                                            </a>
                                        @else
                                            <span class="text-muted">Chưa đính kèm</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th class="bg-light">Ảnh CCCD / Bãi xe:</th>
                                    <td>
                                        @if($user->id_card_image)
                                            <a href="{{ asset('uploads/partner_docs/' . $user->id_card_image) }}" target="_blank" class="btn btn-xs btn-outline-primary font-weight-bold py-0">
                                                <i class="fa fa-eye mr-1"></i> Xem ảnh CCCD / Bãi xe đã nộp
                                            </a>
                                        @else
                                            <span class="text-muted">Chưa đính kèm</span>
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>

                        <div class="p-3 bg-light rounded text-center">
                            <p class="small text-muted mb-2">
                                <i class="fa fa-headphones text-success mr-1"></i> 
                                Cần hỗ trợ thẩm định nhanh hoặc cập nhật thêm tài liệu chứng minh?
                            </p>
                            <a href="tel:19008888" class="btn btn-sm btn-success font-weight-bold px-3">
                                <i class="fa fa-phone mr-1"></i> Hotline Ban Thẩm Định: 1900 8888
                            </a>
                        </div>
                    </div>
                @endif

                <div class="card-footer bg-white border-top text-center py-3">
                    <a href="{{ route('welcome') }}" class="btn btn-outline-secondary font-weight-bold btn-sm mr-2">
                        <i class="fa fa-home mr-1"></i> Về Trang chủ Website
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="d-inline">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger font-weight-bold btn-sm">
                            <i class="fa fa-sign-out mr-1"></i> Đăng xuất
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
