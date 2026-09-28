@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="font-weight-bold text-uppercase mb-0" style="border-left: 4px solid #17a2b8; padding-left: 12px;">
            <i class="fa fa-tag text-info"></i> Chi tiết Hãng Xe: {{ $category->name }}
        </h2>
        <div>
            <a class="btn btn-primary font-weight-bold" href="{{ route('admin.categories.edit', $category->id) }}">
                <i class="fa fa-edit"></i> Chỉnh sửa tên hãng
            </a>
            <a class="btn btn-secondary font-weight-bold ml-2" href="{{ route('admin.categories.index') }}">
                <i class="fa fa-arrow-left"></i> Quay lại danh sách
            </a>
        </div>
    </div>

    <!-- Thông tin hãng xe -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 10px;">
        <div class="card-body p-4">
            <div class="row align-items-center">
                <div class="col-md-4 text-center border-right">
                    <h5 class="text-muted text-uppercase small font-weight-bold">Tên thương hiệu</h5>
                    <h3 class="font-weight-bold text-dark">{{ $category->name }}</h3>
                </div>
                <div class="col-md-4 text-center border-right">
                    <h5 class="text-muted text-uppercase small font-weight-bold">Số lượng xe trong kho</h5>
                    <h3 class="font-weight-bold text-primary">{{ $products->total() }} chiếc</h3>
                </div>
                <div class="col-md-4 text-center">
                    <h5 class="text-muted text-uppercase small font-weight-bold">Ngày tạo danh mục</h5>
                    <h5 class="font-weight-bold text-muted">{{ $category->created_at ? $category->created_at->format('d/m/Y H:i') : 'N/A' }}</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Danh sách các xe thuộc hãng này -->
    <h4 class="font-weight-bold text-dark mb-3">
        <i class="fa fa-car mr-1 text-primary"></i> Các mẫu xe thuộc hãng {{ $category->name }}
    </h4>

    <div class="card shadow-sm border-0" style="border-radius: 10px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped m-0 align-middle">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="text-center" width="50px">ID</th>
                            <th>Hình ảnh</th>
                            <th>Tên xe</th>
                            <th>Giá bán</th>
                            <th>Màu sắc</th>
                            <th class="text-center" width="180px">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                        <tr>
                            <td class="text-center align-middle font-weight-bold">{{ $product->id }}</td>
                            <td class="align-middle">
                                @if($product->image)
                                    <img src="{{ asset('images/'.$product->image) }}" class="rounded" width="70px" height="50px" style="object-fit: cover;">
                                @else
                                    <span class="badge badge-secondary">Chưa có ảnh</span>
                                @endif
                            </td>
                            <td class="align-middle font-weight-bold text-dark">{{ $product->name }}</td>
                            <td class="align-middle text-danger font-weight-bold">{{ number_format($product->price) }} đ</td>
                            <td class="align-middle">{{ $product->color ?: 'Mặc định' }}</td>
                            <td class="text-center align-middle">
                                <a class="btn btn-info btn-sm text-white" href="{{ route('admin.products.show', $product->id) }}" title="Xem chi tiết">
                                    <i class="fa fa-eye"></i>
                                </a>
                                <a class="btn btn-primary btn-sm text-white" href="{{ route('admin.products.edit', $product->id) }}" title="Sửa xe">
                                    <i class="fa fa-edit"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Hiện chưa có xe nào thuộc hãng này trong kho.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($products->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center">
                {{ $products->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@endsection