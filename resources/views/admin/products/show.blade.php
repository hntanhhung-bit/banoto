@extends('layouts.admin')

@section('content')
<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="font-weight-bold text-uppercase" style="border-left: 4px solid #17a2b8; padding-left: 10px;">Chi tiết xe (Admin)</h2>
        <a class="btn btn-secondary font-weight-bold" href="{{ route('admin.products.index') }}"><i class="fa fa-arrow-left"></i> Quay lại kho xe</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <div class="row">
                <!-- Cột Hình ảnh -->
                <div class="col-md-5 mb-4 mb-md-0">
                    @php
                        $adminImages = $product->getAllImages();
                    @endphp
                    <div class="text-center mb-2">
                        <img src="{{ $adminImages[0] ?? $product->image_url }}" id="adminMainImg" class="img-fluid rounded shadow" alt="{{ $product->name }}" style="max-height: 320px; width: 100%; object-fit: cover;">
                    </div>
                    @if(count($adminImages) > 1)
                        <div class="d-flex flex-wrap mt-2" style="gap: 8px;">
                            @foreach($adminImages as $i => $aImg)
                                <img src="{{ $aImg }}" class="rounded border shadow-sm" style="width: 65px; height: 48px; object-fit: cover; cursor: pointer;"
                                     onclick="document.getElementById('adminMainImg').src='{{ $aImg }}'" alt="Ảnh {{ $i+1 }}">
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Cột Thông tin -->
                <div class="col-md-7">
                    <h3 class="font-weight-bold text-primary mb-2">{{ $product->name }}</h3>
                    <h4 class="text-danger font-weight-bold mb-4">{{ number_format($product->price) }} VNĐ</h4>

                    <table class="table table-bordered table-striped">
                        <tbody>
                            <tr>
                                <th style="width: 30%;">ID Hệ thống:</th>
                                <td>{{ $product->id }}</td>
                            </tr>
                            <tr>
                                <th>Hãng xe (Danh mục):</th>
                                <td class="font-weight-bold">{{ $product->category->name ?? 'Không xác định' }}</td>
                            </tr>
                            <tr>
                                <th>Giá thuê tự lái:</th>
                                <td class="text-danger font-weight-bold" style="font-size: 16px;">{{ number_format($product->rent_price_per_day ?: 800000) }} VNĐ / ngày</td>
                            </tr>
                            <tr>
                                <th>Phí tài xế:</th>
                                <td class="text-primary font-weight-bold" style="font-size: 16px;">+{{ number_format($product->driver_price_per_day ?: 500000) }} VNĐ / ngày</td>
                            </tr>
                            <tr>
                                <th>Tiền cọc giữ xe:</th>
                                <td class="font-weight-bold">{{ number_format($product->rental_deposit ?: 5000000) }} VNĐ</td>
                            </tr>
                            <tr>
                                <th>Trạng thái xe:</th>
                                <td>
                                    @if($product->rental_status === 'rented')
                                        <span class="badge badge-danger px-2 py-1">Đang có khách thuê</span>
                                    @elseif($product->rental_status === 'maintenance')
                                        <span class="badge badge-warning px-2 py-1">Đang bảo dưỡng</span>
                                    @else
                                        <span class="badge badge-success px-2 py-1">Sẵn sàng phục vụ</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Phân loại màu sắc & Giá:</th>
                                <td>
                                    @if($product->colors && $product->colors->count() > 0)
                                        <div class="table-responsive mt-1">
                                            <table class="table table-sm table-bordered bg-white mb-0">
                                                <thead class="bg-light">
                                                    <tr>
                                                        <th>Màu sắc</th>
                                                        <th>Phụ phí thuê</th>
                                                        <th>Giá thuê/ngày</th>
                                                        <th>Số lượng xe</th>
                                                        <th>Mặc định</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($product->colors as $c)
                                                        <tr>
                                                            <td>
                                                                <span style="display:inline-block; width:12px; height:12px; border-radius:50%; background-color:{{ $c->color_hex }}; border:1px solid #ccc; vertical-align:middle; margin-right:4px;"></span>
                                                                <strong>{{ $c->color_name }}</strong>
                                                            </td>
                                                            <td>+{{ number_format($c->extra_rent_price) }} đ</td>
                                                            <td><strong class="text-danger">{{ number_format($c->rent_price_per_day ?: ($product->rent_price_per_day + $c->extra_rent_price)) }} đ/ngày</strong></td>
                                                            <td>{{ $c->quantity }} xe</td>
                                                            <td>
                                                                @if($c->is_default)
                                                                    <span class="badge badge-success">Mặc định</span>
                                                                @else
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @else
                                        <span>{{ $product->color ?? 'Màu tiêu chuẩn' }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Mô tả chi tiết:</th>
                                <td style="white-space: pre-line;">{{ $product->description ?? 'Chưa có bài mô tả cho xe này.' }}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="mt-4">
                        <a href="{{ route('admin.products.edit', $product->id) }}" class="btn btn-primary font-weight-bold px-4"><i class="fa fa-edit"></i> Chỉnh sửa thông tin xe này</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection