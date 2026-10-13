@extends('layouts.app')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
                <div class="card-header bg-success text-white text-center py-4">
                    <i class="fa fa-check-circle fa-4x mb-2"></i>
                    <h3 class="font-weight-bold mb-1">
                        @if($rental->rental_type === 'with_driver')
                            ĐĂNG KÝ THUÊ XE CÓ TÀI XẾ THÀNH CÔNG!
                        @else
                            ĐĂNG KÝ THUÊ XE TỰ LÁI THÀNH CÔNG!
                        @endif
                    </h3>
                    <p class="mb-0">Mã hợp đồng / Đơn thuê xe: <strong class="badge badge-light text-dark px-3 py-1 font-weight-bold" style="font-size: 15px;">#{{ $rental->rental_code }}</strong></p>
                </div>

                <div class="card-body p-4">
                    <!-- KHỐI MÃ BẢO MẬT ĐỐI CHIẾU NHẬN XE -->
                    <div class="p-3 mb-4 rounded border bg-light text-center {{ $rental->handover_code ? 'border-success' : 'border-warning' }}">
                        <div class="d-flex justify-content-center align-items-center mb-2">
                            <i class="fa fa-shield fa-2x {{ $rental->handover_code ? 'text-success' : 'text-warning' }} mr-2"></i>
                            <h5 class="font-weight-bold text-dark mb-0">MÃ BẢO MẬT ĐỐI CHIẾU NHẬN XE (HANDOVER OTP)</h5>
                        </div>

                        @if($rental->handover_code)
                            <div class="my-2">
                                <span class="badge badge-success text-white px-4 py-2 font-weight-bold shadow-sm" style="font-size: 26px; letter-spacing: 5px; font-family: monospace;">
                                    {{ $rental->handover_code }}
                                </span>
                            </div>
                            <p class="text-success small mb-3">
                                <i class="fa fa-check-circle mr-1"></i> <strong>Đã kích hoạt:</strong> Bạn hãy giữ mã này và chỉ cung cấp cho Showroom / Nhà xe đối tác khi nhận chìa khóa xe thực tế. Đối tác sẽ đối chiếu mã trên hệ thống để đảm bảo chính chủ.
                            </p>
                            <a href="{{ route('rentals.voucher', $rental->id) }}" target="_blank" class="btn btn-warning btn-sm font-weight-bold text-dark px-3 py-2 shadow-sm">
                                <i class="fa fa-file-text-o mr-1"></i> MỞ PHIẾU ĐƠN THUÊ XE & MÃ QR ĐỐI CHIẾU
                            </a>
                        @else
                            <div class="my-2">
                                <span class="badge badge-secondary text-white px-4 py-2 font-weight-bold" style="font-size: 20px; letter-spacing: 4px; font-family: monospace;">
                                    CHƯA CẤP MÃ
                                </span>
                            </div>
                            <div class="alert alert-warning py-2 px-3 small border mb-3 text-left">
                                <i class="fa fa-exclamation-triangle text-danger mr-1"></i> <strong>Chưa đủ điều kiện cấp mã:</strong> Quý khách chưa hoàn tất đặt cọc hoặc giao dịch cọc bị hủy/thất bại. Mã OTP 6 số chỉ được cấp sau khi thanh toán cọc thành công, hoặc khi <strong>Quản trị viên (Admin) xác nhận đã nhận cọc</strong> (áp dụng khi khách thanh toán/chuyển khoản trễ tránh mất tiền).
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3 py-2" disabled>
                                <i class="fa fa-lock mr-1"></i> VUI LÒNG NỘP CỌC ĐỂ MỞ KHÓA MÃ ĐỐI CHIẾU
                            </button>
                        @endif
                    </div>

                    <div class="alert alert-info border-0 rounded-lg">
                        <i class="fa fa-info-circle mr-1"></i> Nhân viên điều hành và đối tác Showroom của AutoCar sẽ liên hệ với bạn trong vòng 15-30 phút để xác nhận thông tin nhận xe và bàn giao.
                    </div>

                    <div class="row mt-4">
                        <!-- Thông tin đơn thuê -->
                        <div class="col-md-7">
                            <h5 class="font-weight-bold text-dark border-bottom pb-2 mb-3">
                                <i class="fa fa-file-text-o text-primary mr-1"></i> Chi tiết đơn thuê xe
                            </h5>

                            <table class="table table-borderless small mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 40%;">Mẫu xe:</td>
                                    <td>
                                        <strong>{{ $rental->product->name ?? 'Mẫu xe đã chọn' }}</strong>
                                        @if($rental->selected_color)
                                            <span class="badge badge-secondary ml-1 px-2 py-1"><i class="fa fa-paint-brush"></i> {{ $rental->selected_color }}</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Loại hình dịch vụ:</td>
                                    <td>
                                        @if($rental->rental_type === 'with_driver')
                                            <span class="badge badge-primary px-2 py-1"><i class="fa fa-user-circle"></i> Thuê kèm tài xế riêng</span>
                                        @else
                                            <span class="badge badge-danger px-2 py-1"><i class="fa fa-key"></i> Thuê xe tự lái</span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Khách hàng:</td>
                                    <td><strong>{{ $rental->customer_name }}</strong> ({{ $rental->customer_phone }})</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Thời gian thuê:</td>
                                    <td>
                                        Từ <strong>{{ date('d/m/Y', strtotime($rental->start_date)) }}</strong> 
                                        đến <strong>{{ date('d/m/Y', strtotime($rental->end_date)) }}</strong>
                                        ({{ $rental->total_days }} ngày)
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Điểm đón / giao xe:</td>
                                    <td>{{ $rental->customer_address }}</td>
                                </tr>
                                @if($rental->destination_address)
                                <tr>
                                    <td class="text-muted">Lộ trình di chuyển:</td>
                                    <td>{{ $rental->destination_address }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="text-muted">Phương thức thanh toán:</td>
                                    <td>
                                        @if($rental->payment_method === 'sepay' || $rental->payment_method === 'momo')
                                            <span class="badge badge-primary px-2 py-1 text-white" style="background-color: #0d6efd;">
                                                <i class="fa fa-qrcode mr-1"></i> Quét mã QR SePay (TPBank)
                                            </span>
                                        @else
                                            <span class="badge badge-secondary px-2 py-1">
                                                <i class="fa fa-money mr-1"></i> Tiền mặt khi nhận xe
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Tiền thuê xe:</td>
                                    <td>{{ number_format($rental->total_rental_fee) }} VNĐ</td>
                                </tr>
                                @if($rental->total_driver_fee > 0)
                                <tr>
                                    <td class="text-muted">Phí tài xế:</td>
                                    <td>{{ number_format($rental->total_driver_fee) }} VNĐ</td>
                                </tr>
                                @endif
                                <tr>
                                    <td class="text-muted">Tiền đặt cọc xe:</td>
                                    <td><strong class="text-danger">{{ number_format($rental->deposit_amount) }} VNĐ</strong></td>
                                </tr>
                                <tr class="border-top">
                                    <td class="font-weight-bold text-dark" style="font-size: 15px;">Tổng cộng:</td>
                                    <td><strong class="text-danger font-weight-bold" style="font-size: 17px;">{{ number_format($rental->total_amount) }} VNĐ</strong></td>
                                </tr>
                            </table>
                        </div>

                        <!-- Cột Thanh toán cọc qua SePay VietQR -->
                        <div class="col-md-5 text-center border-left pl-md-4">
                            <h6 class="font-weight-bold mb-3 text-primary">
                                <i class="fa fa-qrcode mr-1"></i> Đặt Cọc Quét Mã QR SePay
                            </h6>

                            @if($rental->payment_status === 'deposit_paid' || $rental->payment_status === 'fully_paid')
                                <div class="p-4 bg-light rounded text-center border border-success mb-3 shadow-sm">
                                    <i class="fa fa-check-circle fa-3x text-success mb-2"></i>
                                    <h5 class="font-weight-bold text-success">Đã Đặt Cọc Thành Công!</h5>
                                    <p class="text-muted small mb-0">Hệ thống đã ghi nhận tiền cọc <strong>{{ number_format($rental->deposit_amount) }} đ</strong> qua QR SePay (TPBank).</p>
                                    <div class="mt-2 text-success font-weight-bold small">
                                        <i class="fa fa-key mr-1"></i> Mã đối chiếu nhận xe đã được cấp thành công ở trên!
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning py-2 px-3 mb-3 small text-left border">
                                    <i class="fa fa-clock-o mr-1"></i> Trạng thái cọc: <strong class="text-danger">Chưa nhận được tiền cọc</strong>
                                </div>

                                <a href="{{ route('rentals.sepay.pay', $rental->id) }}" class="btn btn-primary font-weight-bold shadow-sm w-100 py-3 mb-3 text-center text-white" style="border-radius: 8px; font-size: 15px; white-space: normal; line-height: 1.4; display: block;">
                                    <i class="fa fa-qrcode mr-1"></i> Mở trang quét mã QR SePay &rarr;
                                </a>

                                <div class="p-3 bg-light rounded border small text-muted text-left mb-2">
                                    <i class="fa fa-info-circle text-primary mr-1"></i> Quét mã VietQR bằng App ngân hàng bất kỳ (TPBank, Vietcombank, MB, Techcombank...). Hệ thống sẽ tự động xác nhận tiền cọc sau 3 giây.
                                </div>

                                <div class="p-2 bg-light rounded border text-muted small text-left" style="font-size: 11px;">
                                    <i class="fa fa-shield text-success mr-1"></i> <strong>Bảo vệ quyền lợi:</strong> Khi cọc thành công, hệ thống tự động sinh Mã nhận xe 6 số. Nếu chuyển khoản ngoài giờ hoặc mạng chậm, Admin sàn có quyền bấm <strong>"Xác nhận nhận cọc"</strong> để kích hoạt mã ngay cho bạn.
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-4 pt-3 border-top d-flex justify-content-between flex-wrap">
                        <a href="{{ route('welcome') }}" class="btn btn-outline-secondary font-weight-bold">
                            <i class="fa fa-arrow-left mr-1"></i> Quay về trang chủ
                        </a>
                        <a href="{{ route('rentals.my') }}" class="btn btn-primary font-weight-bold">
                            <i class="fa fa-list-alt mr-1"></i> Xem danh sách đơn thuê của tôi
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
