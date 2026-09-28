<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Phiếu Đơn Thuê Xe & Biên Bản Bàn Giao #{{ $rental->rental_code }}</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&family=Roboto+Mono:wght@500;700&display=swap" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
            font-family: 'Montserrat', sans-serif;
            color: #2d3748;
            padding: 20px 0 50px 0;
        }
        .contract-paper {
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.08);
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 50px;
            position: relative;
        }
        .code-box {
            font-family: 'Roboto Mono', monospace;
            letter-spacing: 4px;
            font-size: 28px;
            font-weight: 700;
            background: #fff9db;
            border: 2px dashed #f59f00;
            color: #d9480f;
            padding: 10px 20px;
            display: inline-block;
            border-radius: 8px;
        }
        .header-national {
            text-align: center;
            border-bottom: 2px solid #1a202c;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .section-title {
            font-size: 15px;
            font-weight: 700;
            text-transform: uppercase;
            color: #1e3a8a;
            border-left: 4px solid #1e3a8a;
            padding-left: 10px;
            margin: 25px 0 15px 0;
        }
        .table-compact th, .table-compact td {
            padding: 8px 12px;
            font-size: 13px;
        }
        .signature-box {
            min-height: 120px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: center;
        }
        .stamp-mark {
            border: 3px double #e53e3e;
            color: #e53e3e;
            padding: 6px 14px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 8px;
            transform: rotate(-5deg);
            display: inline-block;
            font-size: 12px;
        }
        .stamp-verified {
            border: 3px double #2f855a;
            color: #2f855a;
            transform: rotate(-3deg);
        }
        @media print {
            body {
                background: #fff;
                padding: 0;
            }
            .contract-paper {
                box-shadow: none;
                padding: 20px;
                max-width: 100%;
            }
            .no-print {
                display: none !important;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <!-- NÚT ĐIỀU HƯỚNG & IN ẤN (ẨN KHI IN) -->
    <div class="mb-4 d-flex justify-content-between align-items-center no-print" style="max-width: 900px; margin: 0 auto;">
        <div>
            @if(Auth::user()->role === 'partner')
                <a href="{{ route('partner.rentals') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-arrow-left mr-1"></i> Quay lại Kênh Đối tác
                </a>
            @elseif(Auth::user()->role === 'admin')
                <a href="{{ route('admin.rentals.show', $rental->id) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-arrow-left mr-1"></i> Quay lại Admin
                </a>
            @else
                <a href="{{ route('rentals.my') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fa fa-arrow-left mr-1"></i> Danh sách xe tôi thuê
                </a>
            @endif
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary btn-sm font-weight-bold shadow-sm">
                <i class="fa fa-print mr-1"></i> In Phiếu Bàn Giao / Xuất PDF
            </button>
        </div>
    </div>

    <!-- TỜ ĐƠN / BIÊN BẢN HỢP ĐỒNG BÀN GIAO -->
    <div class="contract-paper">
        <!-- QUỐC HIỆU & TIÊU NGỮ -->
        <div class="header-national">
            <h6 class="font-weight-bold mb-1 text-uppercase" style="letter-spacing: 1px;">CỘNG HÒA XÃ HỘI CHỦ NGHĨA VIỆT NAM</h6>
            <p class="font-weight-bold mb-3" style="font-size: 13px;">Độc lập - Tự do - Hạnh phúc</p>
            <h4 class="font-weight-bold text-uppercase text-dark mt-3 mb-1">
                PHIẾU ĐƠN THUÊ XE & BIÊN BẢN BÀN GIAO ĐIỆN TỬ
            </h4>
            <p class="text-muted small mb-0">
                (Dịch vụ Đặt xe trung gian Bên Thứ Ba & Ký quỹ bảo lãnh cọc an toàn)
            </p>
            <div class="mt-2 font-weight-bold text-muted" style="font-size: 12px;">
                Mã hợp đồng: <span class="text-primary font-weight-bold">#{{ $rental->rental_code }}</span> | 
                Ngày lập: {{ $rental->created_at->format('d/m/Y H:i') }}
            </div>
        </div>

        <!-- KHỐI MÃ BẢO MẬT ĐỐI CHIẾU (SECURITY VERIFICATION BOX) -->
        <div class="p-3 mb-4 rounded border {{ $rental->handover_status === 'verified' || in_array($rental->rental_status, ['in_progress', 'returned']) ? 'bg-light border-success' : 'bg-light border-warning' }}">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <div class="d-flex align-items-center mb-2">
                        <i class="fa fa-shield fa-2x text-warning mr-3"></i>
                        <div>
                            <h6 class="font-weight-bold text-dark mb-0">MÃ BẢO MẬT ĐỐI CHIẾU BÀN GIAO XE (HANDOVER OTP)</h6>
                            <span class="text-muted small">Cung cấp mã này cho Showroom / Nhà xe đối tác khi nhận chìa khóa xe</span>
                        </div>
                    </div>
                    
                    <div class="my-2">
                        @if($rental->handover_code)
                            <div class="code-box">{{ $rental->handover_code }}</div>
                        @else
                            <div class="code-box" style="color: #dc3545; border-color: #dc3545; letter-spacing: 2px; font-size: 18px;">
                                CHƯA CẤP MÃ (CHƯA NỘP CỌC)
                            </div>
                        @endif
                    </div>

                    @if($rental->handover_status === 'verified' || in_array($rental->rental_status, ['in_progress', 'returned']))
                        <div class="mt-2 text-success font-weight-bold small">
                            <i class="fa fa-check-circle mr-1"></i> ĐÃ ĐỐI CHIẾU & BÀN GIAO XE HỢP LỆ
                            <div class="text-muted font-weight-normal" style="font-size: 11px;">
                                Thời gian bàn giao: {{ $rental->handover_verified_at ? $rental->handover_verified_at->format('d/m/Y H:i') : 'Đã duyệt' }} 
                                | Người xác nhận: {{ $rental->handover_verified_by ?: 'Showroom Đối tác' }}
                                @if($rental->handover_odo) | Odo lúc giao: {{ number_format($rental->handover_odo) }} km @endif
                                @if($rental->handover_fuel) | Xăng: {{ $rental->handover_fuel }}% @endif
                            </div>
                        </div>
                    @elseif($rental->handover_code)
                        <div class="mt-2 text-danger small">
                            <i class="fa fa-lock mr-1"></i> <strong>Lưu ý bảo mật:</strong> Để phòng tránh mạo danh, Showroom chỉ giao xe khi bạn cung cấp đúng mã này.
                        </div>
                    @else
                        <div class="mt-2 text-danger small">
                            <i class="fa fa-exclamation-triangle mr-1"></i> <strong>Chưa kích hoạt:</strong> Đơn chưa được nộp cọc hoặc cọc chưa được xác nhận. Vui lòng thanh toán cọc hoặc liên hệ Admin xác nhận cọc để được cấp mã OTP nhận xe.
                        </div>
                    @endif
                </div>

                <!-- QR CODE ĐỐI CHIẾU -->
                <div class="col-md-4 text-center border-left">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=120x120&data={{ urlencode(route('rentals.voucher', $rental->id)) }}" 
                         alt="QR Code Đơn Thuê" class="img-thumbnail" style="width: 110px; height: 110px;">
                    <div class="text-muted small mt-1" style="font-size: 10px;">Quét QR để kiểm tra tính hợp lệ của phiếu đơn</div>
                </div>
            </div>
        </div>

        <!-- 1. THÔNG TIN CÁC BÊN THAM GIA -->
        <div class="section-title">I. THÔNG TIN CÁC BÊN THAM GIA HỢP ĐỒNG</div>
        <div class="row">
            <!-- BÊN A: BÊN THỨ BA BẢO LÃNH -->
            <div class="col-md-4 mb-3">
                <div class="p-3 bg-light rounded h-100 border">
                    <strong class="text-primary small d-block mb-1 text-uppercase">Bên A: Sàn Điều Phối & Bảo Lãnh</strong>
                    <div class="font-weight-bold text-dark" style="font-size: 13px;">AutoCar Platform</div>
                    <div class="text-muted small mt-1">
                        Vai trò: Bên trung gian điều phối & bảo lãnh 100% tiền cọc thế chân cho khách hàng.<br>
                        Hotline CSKH: 1900 8888 (24/7)
                    </div>
                </div>
            </div>

            <!-- BÊN B: ĐỐI TÁC CUNG CẤP XE -->
            <div class="col-md-4 mb-3">
                <div class="p-3 bg-light rounded h-100 border">
                    <strong class="text-success small d-block mb-1 text-uppercase">Bên B: Đối Tác Showroom / Nhà Xe</strong>
                    @php
                        $showroom = $rental->product->partner_showroom;
                        $partnerUser = $rental->partner;
                    @endphp
                    <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ $partnerUser->company_name ?: ($showroom->name ?? 'Showroom AutoCar Đối Tác') }}</div>
                    <div class="text-muted small mt-1">
                        Địa chỉ nhận xe: {{ $partnerUser->address ?: ($showroom->address ?? 'Cầu Giấy, Hà Nội') }}<br>
                        Hotline bàn giao: <strong class="text-success">{{ $partnerUser->phone ?: ($showroom->phone ?? '0988 888 888') }}</strong>
                    </div>
                </div>
            </div>

            <!-- BÊN C: KHÁCH HÀNG THUÊ XE -->
            <div class="col-md-4 mb-3">
                <div class="p-3 bg-light rounded h-100 border">
                    <strong class="text-danger small d-block mb-1 text-uppercase">Bên C: Khách Hàng Thuê Xe</strong>
                    <div class="font-weight-bold text-dark" style="font-size: 13px;">{{ $rental->customer_name }}</div>
                    <div class="text-muted small mt-1">
                        Điện thoại: <strong>{{ $rental->customer_phone }}</strong><br>
                        Email: {{ $rental->customer_email ?: 'N/A' }}<br>
                        Địa điểm giao nhận: {{ $rental->customer_address }}
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. THÔNG TIN PHƯƠNG TIỆN & LỊCH TRÌNH -->
        <div class="section-title">II. PHƯƠNG TIỆN VẬN CHUYỂN & LỊCH TRÌNH THUÊ</div>
        <table class="table table-bordered table-compact mb-4">
            <tbody>
                <tr>
                    <td class="bg-light font-weight-bold" style="width: 25%;">Mẫu xe bàn giao:</td>
                    <td class="font-weight-bold text-primary">{{ $rental->product->name ?? 'Xe ô tô du lịch' }}</td>
                    <td class="bg-light font-weight-bold" style="width: 25%;">Hãng & Phân khúc:</td>
                    <td>{{ $rental->product->category->name ?? 'Chính hãng' }}</td>
                </tr>
                <tr>
                    <td class="bg-light font-weight-bold">Màu sắc đăng ký:</td>
                    <td><span class="badge badge-info">{{ $rental->selected_color ?: 'Màu chuẩn' }}</span></td>
                    <td class="bg-light font-weight-bold">Hình thức dịch vụ:</td>
                    <td>
                        @if($rental->rental_type === 'with_driver')
                            <span class="badge badge-primary"><i class="fa fa-id-badge"></i> Thuê có tài xế riêng</span>
                            @if($rental->driver_name)
                                <div class="small mt-1 text-muted">Tài xế: <strong>{{ $rental->driver_name }}</strong> ({{ $rental->driver_phone }})</div>
                            @endif
                        @else
                            <span class="badge badge-secondary"><i class="fa fa-key"></i> Thuê tự lái</span>
                        @endif
                    </td>
                </tr>
                <tr>
                    <td class="bg-light font-weight-bold">Thời gian nhận xe:</td>
                    <td class="text-success font-weight-bold">{{ date('d/m/Y', strtotime($rental->start_date)) }} (08:00 AM)</td>
                    <td class="bg-light font-weight-bold">Thời gian trả xe:</td>
                    <td class="text-danger font-weight-bold">{{ date('d/m/Y', strtotime($rental->end_date)) }} (20:00 PM)</td>
                </tr>
                <tr>
                    <td class="bg-light font-weight-bold">Tổng thời gian thuê:</td>
                    <td colspan="3"><strong class="text-dark">{{ $rental->total_days }} ngày</strong></td>
                </tr>
            </tbody>
        </table>

        <!-- 3. BẢNG PHÂN BỔ CHI PHÍ & BẢO LÃNH KÝ QUỸ -->
        <div class="section-title">III. CHI PHÍ DỊCH VỤ & KÝ QUỸ BẢO LÃNH HOÀN CỌC</div>
        <table class="table table-bordered table-compact mb-3">
            <thead class="bg-light font-weight-bold">
                <tr>
                    <th>Hạng mục dịch vụ</th>
                    <th class="text-center">Đơn giá / ngày</th>
                    <th class="text-center">Số ngày</th>
                    <th class="text-right">Thành tiền</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Tiền thuê phương tiện ({{ $rental->product->name ?? 'Xe' }})</td>
                    <td class="text-center">{{ number_format($rental->daily_price) }} đ</td>
                    <td class="text-center">{{ $rental->total_days }}</td>
                    <td class="text-right font-weight-bold">{{ number_format($rental->total_rental_fee) }} đ</td>
                </tr>
                @if($rental->rental_type === 'with_driver' && $rental->total_driver_fee > 0)
                <tr>
                    <td>Phí dịch vụ tài xế chuyên nghiệp</td>
                    <td class="text-center">{{ number_format($rental->driver_fee_per_day) }} đ</td>
                    <td class="text-center">{{ $rental->total_days }}</td>
                    <td class="text-right font-weight-bold">{{ number_format($rental->total_driver_fee) }} đ</td>
                </tr>
                @endif
                <tr class="table-warning">
                    <td colspan="3">
                        <strong>Tiền cọc thế chân ký quỹ (Platform Escrow Guarantee)</strong>
                        <div class="text-muted small">Được bảo lãnh an toàn tại Sàn AutoCar, hoàn trả 100% khi kết thúc hợp đồng trả xe.</div>
                    </td>
                    <td class="text-right font-weight-bold text-danger">{{ number_format($rental->deposit_amount) }} đ</td>
                </tr>
                <tr class="table-light">
                    <td colspan="3" class="text-right font-weight-bold text-uppercase">Tổng giá trị thanh toán:</td>
                    <td class="text-right font-weight-bold text-success" style="font-size: 16px;">{{ number_format($rental->total_amount) }} VNĐ</td>
                </tr>
            </tbody>
        </table>

        <!-- THÔNG TIN TÀI KHOẢN HOÀN CỌC -->
        <div class="p-3 bg-light rounded border mb-4 small">
            <div class="font-weight-bold text-dark mb-1"><i class="fa fa-university text-primary mr-1"></i> TÀI KHOẢN NHẬN HOÀN CỌC KÝ QUỸ CỦA KHÁCH HÀNG:</div>
            <div class="row">
                <div class="col-md-4">Ngân hàng: <strong>{{ $rental->refund_bank_name ?: 'Theo yêu cầu khách hàng' }}</strong></div>
                <div class="col-md-4">Số tài khoản: <strong class="text-primary">{{ $rental->refund_account_number ?: 'Theo yêu cầu khách hàng' }}</strong></div>
                <div class="col-md-4">Chủ tài khoản: <strong>{{ $rental->refund_account_holder ?: $rental->customer_name }}</strong></div>
            </div>
            <div class="text-muted mt-1" style="font-size: 11px;">
                Trạng thái cọc: 
                @if($rental->refund_status === 'refunded')
                    <span class="badge badge-success">Đã hoàn cọc cho khách ({{ number_format($rental->refund_amount) }}đ)</span>
                @elseif($rental->refund_status === 'holding')
                    <span class="badge badge-warning">Sàn đang ký quỹ bảo lãnh ({{ number_format($rental->deposit_amount) }}đ)</span>
                @else
                    <span class="badge badge-secondary">Chưa thanh toán cọc</span>
                @endif
            </div>
        </div>

        <!-- 4. BIÊN BẢN KIỂM TRA HIỆN TRẠNG PHƯƠNG TIỆN -->
        <div class="section-title">IV. BIÊN BẢN KIỂM TRA HIỆN TRẠNG PHƯƠNG TIỆN (CHECKLIST)</div>
        <div class="row small mb-4">
            <div class="col-md-6">
                <div class="border p-2 rounded h-100">
                    <div class="font-weight-bold text-dark border-bottom pb-1 mb-2">1. LÚC BÀN GIAO XE (Nhận xe)</div>
                    <div>- Đồng hồ ODO lúc giao: <strong>{{ $rental->handover_odo ? number_format($rental->handover_odo) . ' km' : '............ km' }}</strong></div>
                    <div>- Mức nhiên liệu (Xăng/Điện): <strong>{{ $rental->handover_fuel ? $rental->handover_fuel . '%' : '............ %' }}</strong></div>
                    <div>- Tình trạng thân vỏ / trầy xước: <strong>{{ $rental->handover_notes ?: 'Nguyên vẹn theo tiêu chuẩn' }}</strong></div>
                    <div>- Giấy tờ kèm theo: Đăng kiểm, Bảo hiểm TNDS, Giấy đi đường.</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="border p-2 rounded h-100">
                    <div class="font-weight-bold text-dark border-bottom pb-1 mb-2">2. LÚC HOÀN TRẢ XE (Kết thúc hợp đồng)</div>
                    <div>- Đồng hồ ODO lúc trả: <strong>{{ $rental->return_odo ? number_format($rental->return_odo) . ' km' : '............ km' }}</strong></div>
                    <div>- Mức nhiên liệu hoàn trả: <strong>{{ $rental->return_fuel ? $rental->return_fuel . '%' : '............ %' }}</strong></div>
                    <div>- Tình trạng khi trả: <strong>{{ $rental->refund_notes ?: 'Nguyên vẹn theo bàn giao ban đầu' }}</strong></div>
                    <div>- Khấu trừ phạt nguội (nếu có): <strong>{{ number_format($rental->refund_holding_fee) }} đ</strong></div>
                </div>
            </div>
        </div>

        <!-- 5. CHỮ KÝ ĐIỆN TỬ CỦA CÁC BÊN -->
        <div class="row text-center mt-5">
            <div class="col-4">
                <div class="signature-box">
                    <div class="font-weight-bold small text-uppercase">BÊN THUÊ XE (KHÁCH HÀNG)</div>
                    <div class="stamp-mark stamp-verified">
                        ✓ XÁC NHẬN OTP
                    </div>
                    <div class="font-weight-bold text-dark small">{{ $rental->customer_name }}</div>
                </div>
            </div>

            <div class="col-4">
                <div class="signature-box">
                    <div class="font-weight-bold small text-uppercase">BÊN CHO THUÊ (SHOWROOM)</div>
                    @if($rental->handover_status === 'verified' || in_array($rental->rental_status, ['in_progress', 'returned']))
                        <div class="stamp-mark stamp-verified">
                            ✓ ĐÃ ĐỐI CHIẾU
                        </div>
                    @else
                        <div class="text-muted small italic">Chờ đối chiếu mã khi giao xe</div>
                    @endif
                    <div class="font-weight-bold text-dark small">{{ $partnerUser->company_name ?: ($showroom->name ?? 'Showroom Đối Tác') }}</div>
                </div>
            </div>

            <div class="col-4">
                <div class="signature-box">
                    <div class="font-weight-bold small text-uppercase">ĐẠI DIỆN SÀN BẢO LÃNH</div>
                    <div class="stamp-mark">
                        ★ BẢO LÃNH 100%
                    </div>
                    <div class="font-weight-bold text-dark small">AutoCar Escrow System</div>
                </div>
            </div>
        </div>

        <!-- FOOTER PHÁP LÝ -->
        <div class="border-top mt-5 pt-3 text-center text-muted" style="font-size: 11px;">
            Phiếu bàn giao điện tử này có giá trị đối chiếu chứng từ pháp lý và giải quyết khiếu nại giữa Khách hàng, Đối tác Showroom và Nền tảng Bên thứ ba.<br>
            Hệ thống AutoCar tự động lưu trữ dữ liệu xác thực bàn giao tại máy chủ trung tâm.
        </div>
    </div>
</div>

</body>
</html>
