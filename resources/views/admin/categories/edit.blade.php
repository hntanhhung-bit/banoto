@extends('layouts.admin')

@section('content')
<div class="container mt-4 mb-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="font-weight-bold text-uppercase" style="border-left: 4px solid #005fb7; padding-left: 10px;">Cập Nhật Hãng Xe</h2>
        <!-- Đã sửa route Quay lại -->
        <a class="btn btn-secondary font-weight-bold" href="{{ route('admin.categories.index') }}"><i class="fa fa-arrow-left"></i> Quay lại</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger shadow-sm">
            <strong>Khoan đã!</strong> Dữ liệu bạn nhập đang có lỗi:
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <!-- Đã sửa route Action của Form -->
            <form action="{{ route('admin.categories.update', $category->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="form-group mb-4">
                    <label class="font-weight-bold">Tên Hãng Xe (Danh mục) <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control" value="{{ old('name', $category->name) }}" placeholder="VD: Toyota, Mazda, Ford..." required>
                </div>
                
                <hr>
                <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2"><i class="fa fa-save"></i> Cập Nhật Lại</button>
            </form>
        </div>
    </div>
</div>
@endsection