@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h2 class="font-weight-bold text-uppercase mb-0" style="border-left: 4px solid #28a745; padding-left: 12px;">
            <i class="fa fa-tags text-success"></i> Quản lý Hãng Xe (Danh mục)
        </h2>
        <div>
            <a class="btn btn-success font-weight-bold shadow-sm" href="{{ route('admin.categories.create') }}">
                <i class="fa fa-plus-circle"></i> Thêm Hãng Xe Mới
            </a>
            <a class="btn btn-secondary font-weight-bold ml-2 shadow-sm" href="{{ route('admin.products.index') }}">
                <i class="fa fa-car"></i> Quản lý Kho xe
            </a>
        </div>
    </div>

    <!-- BỘ LỌC TÌM KIẾM HÃNG XE -->
    <div class="card mb-4 bg-white shadow-sm border-0" style="border-radius: 10px;">
        <div class="card-body">
            <form action="{{ route('admin.categories.index') }}" method="GET" class="row align-items-center">
                <div class="col-md-6 mb-2 mb-md-0">
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text bg-light"><i class="fa fa-search text-muted"></i></span>
                        </div>
                        <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control" placeholder="Tìm kiếm theo tên hãng xe...">
                    </div>
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary font-weight-bold mr-1">
                        <i class="fa fa-filter"></i> Lọc
                    </button>
                    @if(request()->filled('keyword'))
                        <a href="{{ route('admin.categories.index') }}" class="btn btn-outline-secondary font-weight-bold">
                            <i class="fa fa-times"></i> Xóa lọc
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- BẢNG DANH SÁCH DANH MỤC -->
    <div class="card shadow-sm border-0" style="border-radius: 10px; overflow: hidden;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-striped m-0 align-middle">
                    <thead class="bg-dark text-white">
                        <tr>
                            <th class="text-center" width="80px">ID</th>
                            <th>Tên Hãng Xe (Danh mục)</th>
                            <th class="text-center" width="180px">Số lượng xe trong kho</th>
                            <th class="text-center" width="220px">Hành động</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($categories as $category)
                        <tr>
                            <td class="text-center align-middle font-weight-bold">{{ $category->id }}</td>
                            <td class="align-middle" style="font-size: 16px;">
                                <strong class="text-dark">{{ $category->name }}</strong>
                            </td>
                            <td class="text-center align-middle">
                                <span class="badge badge-pill badge-info px-3 py-2" style="font-size: 13px;">
                                    <i class="fa fa-car mr-1"></i> {{ $category->products_count }} xe
                                </span>
                            </td>
                            <td class="text-center align-middle">
                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="m-0">
                                    <a class="btn btn-info btn-sm text-white" href="{{ route('admin.categories.show', $category->id) }}" title="Xem các xe thuộc hãng">
                                        <i class="fa fa-eye"></i> Xem xe
                                    </a>
                                    <a class="btn btn-primary btn-sm text-white" href="{{ route('admin.categories.edit', $category->id) }}" title="Sửa tên hãng">
                                        <i class="fa fa-edit"></i>
                                    </a>
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm" title="Xóa hãng" onclick="return confirm('Chú ý: Không thể xóa nếu hãng xe đang có xe trong kho. Bạn có chắc chắn?')">
                                        <i class="fa fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted">Chưa có hãng xe nào phù hợp.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($categories->hasPages())
            <div class="card-footer bg-white d-flex justify-content-center">
                {{ $categories->links('pagination::bootstrap-4') }}
            </div>
        @endif
    </div>
</div>
@endsection