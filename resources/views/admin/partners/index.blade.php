@extends('layouts.admin')

@section('content')
<div class="container-fluid px-4 py-3">
    <!-- TIÊU ĐỀ TRANG -->
    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
        <div>
            <h3 class="font-weight-bold text-dark mb-1">
                <i class="fa fa-handshake-o text-warning mr-2"></i> THẨM ĐỊNH & DUYỆT HỒ SƠ ĐỐI TÁC SHOWROOM / NHÀ XE
            </h3>
            <p class="text-muted small mb-0">
                Kiểm tra giấy phép kinh doanh, thông tin đại diện pháp luật, số CCCD và cơ sở vật chất của Showroom trước khi cấp quyền Đối tác trên sàn.
            </p>
        </div>
    </div>

    <!-- TABS PHÂN LOẠI TRẠNG THÁI -->
    <ul class="nav nav-pills mb-3">
        <li class="nav-item">
            <a class="nav-link font-weight-bold {{ $status === 'all' ? 'active bg-dark' : 'bg-white text-dark border' }}" 
               href="{{ route('admin.partners.index', ['status' => 'all']) }}">
                Tất cả hồ sơ ({{ $countTotal }})
            </a>
        </li>
        <li class="nav-item ml-2">
            <a class="nav-link font-weight-bold {{ $status === 'pending' ? 'active bg-warning text-dark' : 'bg-white text-warning border' }}" 
               href="{{ route('admin.partners.index', ['status' => 'pending']) }}">
                <i class="fa fa-clock-o mr-1"></i> Chờ duyệt ({{ $countPending }})
                @if($countPending > 0)
                    <span class="badge badge-danger ml-1">{{ $countPending }}</span>
                @endif
            </a>
        </li>
        <li class="nav-item ml-2">
            <a class="nav-link font-weight-bold {{ $status === 'approved' ? 'active bg-success' : 'bg-white text-success border' }}" 
               href="{{ route('admin.partners.index', ['status' => 'approved']) }}">
                <i class="fa fa-check-circle mr-1"></i> Đã phê duyệt ({{ $countApproved }})
            </a>
        </li>
        <li class="nav-item ml-2">
            <a class="nav-link font-weight-bold {{ $status === 'rejected' ? 'active bg-danger' : 'bg-white text-danger border' }}" 
               href="{{ route('admin.partners.index', ['status' => 'rejected']) }}">
                <i class="fa fa-times-circle mr-1"></i> Bị từ chối ({{ $countRejected }})
            </a>
        </li>
    </ul>

    <!-- BỘ LỌC TÌM KIẾM -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body p-3">
            <form action="{{ route('admin.partners.index') }}" method="GET" class="row align-items-center">
                <input type="hidden" name="status" value="{{ $status }}">
                <div class="col-md-6 mb-2 mb-md-0">
                    <input type="text" name="keyword" value="{{ request('keyword') }}" class="form-control form-control-sm"
                           placeholder="Tìm theo tên Showroom, người đại diện, MST, CCCD, Email, SĐT...">
                </div>
                <div class="col-md-4">
                    <button type="submit" class="btn btn-primary btn-sm font-weight-bold mr-1">
                        <i class="fa fa-search"></i> Tìm kiếm
                    </button>
                    @if(request()->filled('keyword'))
                        <a href="{{ route('admin.partners.index', ['status' => $status]) }}" class="btn btn-outline-secondary btn-sm font-weight-bold">
                            <i class="fa fa-times"></i> Xóa tìm kiếm
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- THANH CÔNG CỤ THAO TÁC HÀNG LOẠT (BULK ACTIONS BAR) -->
    <div class="card border-0 shadow-sm mb-3 bg-white">
        <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between flex-wrap">
            <div class="d-flex align-items-center mb-2 mb-md-0">
                <span class="badge badge-light border px-2 py-1 font-weight-bold text-dark mr-3" style="font-size: 13px;">
                    <i class="fa fa-check-square text-primary mr-1"></i> Đã chọn: 
                    <span id="selectedCountBadge" class="text-danger font-weight-bold">0</span> hồ sơ
                </span>
                
                <!-- Nút Phê duyệt nhanh hàng loạt -->
                <button type="button" id="btnBulkApprove" class="btn btn-sm btn-success font-weight-bold mr-2 shadow-sm" disabled>
                    <i class="fa fa-check-circle mr-1"></i> Phê duyệt các mục đã chọn
                </button>

                <!-- Nút Từ chối nhanh hàng loạt -->
                <button type="button" id="btnBulkReject" class="btn btn-sm btn-danger font-weight-bold mr-2 shadow-sm" disabled data-toggle="modal" data-target="#bulkRejectModal">
                    <i class="fa fa-times-circle mr-1"></i> Từ chối các mục đã chọn
                </button>

                <!-- Dropdown thao tác khác -->
                <div class="input-group input-group-sm d-inline-flex w-auto">
                    <select id="bulkSelectAction" class="custom-select custom-select-sm" style="min-width: 170px;">
                        <option value="">-- Thao tác khác --</option>
                        <option value="pending">⏳ Đưa về Chờ duyệt</option>
                        <option value="approve">✅ Phê duyệt đối tác</option>
                        <option value="reject">❌ Từ chối đối tác</option>
                    </select>
                    <div class="input-group-append">
                        <button type="button" id="btnBulkApply" class="btn btn-secondary font-weight-bold" disabled>
                            Áp dụng
                        </button>
                    </div>
                </div>
            </div>

            <div class="text-muted small">
                <i class="fa fa-info-circle text-info mr-1"></i> Tích chọn các ô vuông để duyệt hoặc từ chối nhiều yêu cầu cùng 1 lúc.
            </div>
        </div>
    </div>

    <!-- BẢNG DANH SÁCH HỒ SƠ ĐỐI TÁC -->
    <div class="card border-0 shadow-sm rounded-lg overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 small">
                <thead class="bg-light text-uppercase text-secondary font-weight-bold" style="font-size: 12px;">
                    <tr>
                        <th class="py-3 px-3 text-center" style="width: 45px;">
                            <input type="checkbox" id="selectAll" title="Chọn tất cả trên trang này" style="cursor: pointer; width: 16px; height: 16px;">
                        </th>
                        <th class="py-3 px-2" style="width: 60px;">ID</th>
                        <th class="py-3" style="min-width: 220px;">Showroom / Doanh nghiệp</th>
                        <th class="py-3" style="min-width: 200px;">Đại diện & Pháp lý</th>
                        <th class="py-3 text-center" style="min-width: 170px;">Giấy tờ kinh doanh</th>
                        <th class="py-3 text-center" style="min-width: 130px;">Trạng thái</th>
                        <th class="py-3" style="min-width: 150px;">Thời gian nộp</th>
                        <th class="py-3 text-right px-3" style="min-width: 190px;">Thao tác phê duyệt</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($partners as $partner)
                        <tr>
                            <!-- Checkbox chọn từng dòng -->
                            <td class="text-center px-3">
                                <input type="checkbox" class="partner-checkbox" value="{{ $partner->id }}" style="cursor: pointer; width: 16px; height: 16px;">
                            </td>

                            <td class="px-2 font-weight-bold text-muted">{{ $partner->id }}</td>
                            
                            <!-- Cột 1: Thông tin Showroom -->
                            <td>
                                <div class="font-weight-bold text-dark" style="font-size: 14px;">
                                    <i class="fa fa-building-o text-primary mr-1"></i>
                                    {{ $partner->showroom_name ?: $partner->name }}
                                </div>
                                <div><a href="tel:{{ $partner->phone }}" class="text-success font-weight-bold"><i class="fa fa-phone"></i> {{ $partner->phone }}</a></div>
                                <div class="text-muted"><i class="fa fa-envelope-o"></i> {{ $partner->email }}</div>
                                @if($partner->showroom_address)
                                    <div class="text-muted small mt-1">
                                        <i class="fa fa-map-marker text-danger"></i> {{ $partner->showroom_address }}
                                    </div>
                                @endif
                            </td>

                            <!-- Cột 2: Đại diện pháp lý -->
                            <td>
                                <div><strong>Người đại diện:</strong> {{ $partner->representative_name ?: 'Chưa cập nhật' }}</div>
                                <div><strong>Số CCCD/CMND:</strong> <code class="text-dark">{{ $partner->id_card_number ?: 'Chưa có' }}</code></div>
                                <div><strong>MST / GPKD:</strong> <span class="badge badge-light border font-weight-bold">{{ $partner->tax_code ?: 'Hộ cá nhân' }}</span></div>
                            </td>

                            <!-- Cột 3: Giấy tờ hồ sơ đính kèm -->
                            <td class="text-center">
                                @if($partner->business_license_image)
                                    <a href="{{ asset('uploads/partner_docs/' . $partner->business_license_image) }}" target="_blank" 
                                       class="btn btn-xs btn-outline-primary font-weight-bold mb-1 d-block py-1" style="font-size: 11px;">
                                        <i class="fa fa-file-image-o mr-1"></i> Xem GPKD
                                    </a>
                                @else
                                    <span class="badge badge-secondary mb-1 d-block font-weight-normal py-1">Chưa có GPKD</span>
                                @endif

                                @if($partner->id_card_image)
                                    <a href="{{ asset('uploads/partner_docs/' . $partner->id_card_image) }}" target="_blank" 
                                       class="btn btn-xs btn-outline-info font-weight-bold d-block py-1" style="font-size: 11px;">
                                        <i class="fa fa-id-card-o mr-1"></i> Xem CCCD/Bãi xe
                                    </a>
                                @else
                                    <span class="badge badge-secondary d-block font-weight-normal py-1">Chưa có CCCD/Bãi xe</span>
                                @endif
                            </td>

                            <!-- Cột 4: Trạng thái -->
                            <td class="text-center">
                                @if($partner->partner_status === 'approved')
                                    <span class="badge badge-success px-2 py-1 font-weight-bold">
                                        <i class="fa fa-check-circle"></i> ĐÃ DUYỆT
                                    </span>
                                    <div class="text-muted" style="font-size: 10px;">Vai trò: Partner</div>
                                @elseif($partner->partner_status === 'rejected')
                                    <span class="badge badge-danger px-2 py-1 font-weight-bold" data-toggle="tooltip" title="{{ $partner->partner_reject_reason }}">
                                        <i class="fa fa-times-circle"></i> TỪ CHỐI
                                    </span>
                                    @if($partner->partner_reject_reason)
                                        <div class="text-danger small mt-1 text-truncate" style="max-width: 130px;" title="{{ $partner->partner_reject_reason }}">
                                            {{ $partner->partner_reject_reason }}
                                        </div>
                                    @endif
                                @else
                                    <span class="badge badge-warning px-2 py-1 font-weight-bold text-dark" style="background-color: #ffeeba; border: 1px solid #f5c6cb;">
                                        <i class="fa fa-clock-o text-danger"></i> CHỜ DUYỆT
                                    </span>
                                @endif
                            </td>

                            <!-- Cột 5: Thời gian -->
                            <td>
                                <div>Nộp: <strong>{{ $partner->partner_applied_at ? \Carbon\Carbon::parse($partner->partner_applied_at)->format('d/m/Y H:i') : ($partner->created_at ? $partner->created_at->format('d/m/Y H:i') : '-') }}</strong></div>
                                @if($partner->partner_approved_at)
                                    <div class="text-success small">Duyệt: {{ \Carbon\Carbon::parse($partner->partner_approved_at)->format('d/m/Y H:i') }}</div>
                                @endif
                            </td>

                            <!-- Cột 6: Thao tác đơn lẻ trên từng dòng -->
                            <td class="text-right px-3">
                                @if($partner->partner_status === 'approved')
                                    <!-- Đã duyệt: Cho phép Hủy quyền / Tạm ngưng -->
                                    <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold" 
                                            data-toggle="modal" data-target="#rejectModal{{ $partner->id }}">
                                        <i class="fa fa-ban mr-1"></i> Tạm ngưng / Hủy quyền
                                    </button>
                                @elseif($partner->partner_status === 'rejected')
                                    <!-- Đã từ chối: Cho phép Xét duyệt lại -->
                                    <form action="{{ route('admin.partners.approve', $partner->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Tái phê duyệt hồ sơ đối tác cho {{ $partner->showroom_name ?: $partner->name }}?')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-success font-weight-bold">
                                            <i class="fa fa-refresh mr-1"></i> Xét duyệt lại
                                        </button>
                                    </form>
                                @else
                                    <!-- Trạng thái 'pending', 'none', hoặc null: Cho phép Phê duyệt hoặc Từ chối -->
                                    <!-- NÚT DUYỆT ĐỐI TÁC -->
                                    <form action="{{ route('admin.partners.approve', $partner->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Bạn có chắc chắn muốn PHÊ DUYỆT hồ sơ đối tác cho {{ $partner->showroom_name ?: $partner->name }}? Tài khoản sẽ được nâng cấp lên quyền Đối tác.')">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success font-weight-bold mb-1">
                                            <i class="fa fa-check mr-1"></i> Duyệt Đối Tác
                                        </button>
                                    </form>

                                    <!-- NÚT TỪ CHỐI -->
                                    <button type="button" class="btn btn-sm btn-outline-danger font-weight-bold mb-1" 
                                            data-toggle="modal" data-target="#rejectModal{{ $partner->id }}">
                                        <i class="fa fa-ban mr-1"></i> Từ chối
                                    </button>
                                @endif

                                <!-- MODAL TỪ CHỐI HỒ SƠ ĐƠN LẺ -->
                                <div class="modal fade text-left" id="rejectModal{{ $partner->id }}" tabindex="-1" role="dialog">
                                    <div class="modal-dialog modal-dialog-centered" role="document">
                                        <div class="modal-content border-0 shadow">
                                            <div class="modal-header bg-danger text-white">
                                                <h6 class="modal-title font-weight-bold">
                                                    <i class="fa fa-ban mr-1"></i> TỪ CHỐI HỒ SƠ: {{ $partner->showroom_name ?: $partner->name }}
                                                </h6>
                                                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                                            </div>
                                            <form action="{{ route('admin.partners.reject', $partner->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body">
                                                    <div class="alert alert-warning small">
                                                        <i class="fa fa-info-circle mr-1"></i> Lý do từ chối sẽ hiển thị cho người đăng ký biết để chỉnh sửa và bổ sung giấy tờ.
                                                    </div>
                                                    <div class="form-group mb-0">
                                                        <label class="font-weight-bold small">Lý do từ chối hồ sơ <span class="text-danger">*</span>:</label>
                                                        <textarea name="reason" rows="3" class="form-control" required
                                                                  placeholder="VD: Ảnh chụp Giấy phép đăng ký kinh doanh bị mờ, hoặc CMND người đại diện không trùng khớp..."></textarea>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light">
                                                    <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Hủy bỏ</button>
                                                    <button type="submit" class="btn btn-danger btn-sm font-weight-bold">
                                                        <i class="fa fa-check mr-1"></i> Xác nhận từ chối
                                                    </button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="fa fa-inbox fa-2x mb-2 d-block text-muted"></i>
                                Không tìm thấy hồ sơ đăng ký đối tác nào phù hợp với bộ lọc.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- PHÂN TRANG -->
    <div class="mt-3">
        {{ $partners->links() }}
    </div>
</div>

<!-- MODAL TỪ CHỐI HÀNG LOẠT (BULK REJECT MODAL) -->
<div class="modal fade" id="bulkRejectModal" tabindex="-1" role="dialog" aria-labelledby="bulkRejectModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white">
                <h6 class="modal-title font-weight-bold" id="bulkRejectModalLabel">
                    <i class="fa fa-ban mr-1"></i> TỪ CHỐI NHIỀU HỒ SƠ ĐỐI TÁC CÙNG LÚC
                </h6>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning small">
                    <i class="fa fa-exclamation-triangle mr-1"></i> Bạn đang chuẩn bị từ chối 
                    <strong id="bulkRejectCountText" class="text-danger font-weight-bold">0</strong> hồ sơ đối tác đã được tích chọn.
                </div>
                <div class="form-group mb-0">
                    <label class="font-weight-bold small">Lý do từ chối chung cho các hồ sơ:</label>
                    <textarea id="bulkRejectReasonInput" rows="3" class="form-control"
                              placeholder="VD: Hồ sơ chưa đạt yêu cầu kiểm duyệt giấy tờ kinh doanh hoặc cơ sở Showroom..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Hủy bỏ</button>
                <button type="button" id="btnConfirmBulkReject" class="btn btn-danger btn-sm font-weight-bold">
                    <i class="fa fa-ban mr-1"></i> Xác nhận từ chối tất cả
                </button>
            </div>
        </div>
    </div>
</div>

<!-- FORM ẨN ĐỂ SUBMIT BULK ACTION (TRÁNH LỖI NESTED FORM) -->
<form id="formBulkAction" action="{{ route('admin.partners.bulkAction') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="bulk_action" id="inputBulkAction" value="">
    <input type="hidden" name="bulk_reason" id="inputBulkReason" value="">
    <div id="hiddenUserIdsContainer"></div>
</form>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var selectAll = document.getElementById('selectAll');
    var checkboxes = document.querySelectorAll('.partner-checkbox');
    var selectedCountBadge = document.getElementById('selectedCountBadge');
    var btnBulkApprove = document.getElementById('btnBulkApprove');
    var btnBulkReject = document.getElementById('btnBulkReject');
    var btnBulkApply = document.getElementById('btnBulkApply');
    var bulkSelectAction = document.getElementById('bulkSelectAction');
    
    var formBulkAction = document.getElementById('formBulkAction');
    var inputBulkAction = document.getElementById('inputBulkAction');
    var inputBulkReason = document.getElementById('inputBulkReason');
    var hiddenUserIdsContainer = document.getElementById('hiddenUserIdsContainer');

    var bulkRejectCountText = document.getElementById('bulkRejectCountText');
    var bulkRejectReasonInput = document.getElementById('bulkRejectReasonInput');
    var btnConfirmBulkReject = document.getElementById('btnConfirmBulkReject');

    // Hàm cập nhật trạng thái UI theo các checkbox được chọn
    function updateBulkUI() {
        var checkedBoxes = document.querySelectorAll('.partner-checkbox:checked');
        var count = checkedBoxes.length;

        // Cập nhật số lượng
        selectedCountBadge.innerText = count;
        if (bulkRejectCountText) {
            bulkRejectCountText.innerText = count;
        }

        // Bật/tắt các nút thao tác
        if (count > 0) {
            btnBulkApprove.disabled = false;
            btnBulkReject.disabled = false;
            btnBulkApply.disabled = false;

            btnBulkApprove.innerHTML = '<i class="fa fa-check-circle mr-1"></i> Phê duyệt (' + count + ')';
            btnBulkReject.innerHTML = '<i class="fa fa-times-circle mr-1"></i> Từ chối (' + count + ')';
        } else {
            btnBulkApprove.disabled = true;
            btnBulkReject.disabled = true;
            btnBulkApply.disabled = true;

            btnBulkApprove.innerHTML = '<i class="fa fa-check-circle mr-1"></i> Phê duyệt các mục đã chọn';
            btnBulkReject.innerHTML = '<i class="fa fa-times-circle mr-1"></i> Từ chối các mục đã chọn';
        }

        // Cập nhật trạng thái selectAll
        if (checkboxes.length > 0) {
            selectAll.checked = (checkedBoxes.length === checkboxes.length);
        }
    }

    // Sự kiện Chọn tất cả
    if (selectAll) {
        selectAll.addEventListener('change', function() {
            var isChecked = this.checked;
            checkboxes.forEach(function(cb) {
                cb.checked = isChecked;
            });
            updateBulkUI();
        });
    }

    // Sự kiện khi click từng checkbox
    checkboxes.forEach(function(cb) {
        cb.addEventListener('change', function() {
            updateBulkUI();
        });
    });

    // Helper: chuẩn bị form và submit
    function submitBulk(actionType, reason) {
        var checkedBoxes = document.querySelectorAll('.partner-checkbox:checked');
        if (checkedBoxes.length === 0) {
            alert('Vui lòng tích chọn ít nhất 1 hồ sơ đối tác để thực hiện.');
            return;
        }

        hiddenUserIdsContainer.innerHTML = '';
        checkedBoxes.forEach(function(cb) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = cb.value;
            hiddenUserIdsContainer.appendChild(input);
        });

        inputBulkAction.value = actionType;
        inputBulkReason.value = reason || '';

        formBulkAction.submit();
    }

    // Nút 1: Phê duyệt hàng loạt
    btnBulkApprove.addEventListener('click', function() {
        var count = document.querySelectorAll('.partner-checkbox:checked').length;
        if (confirm('Bạn có chắc chắn muốn PHÊ DUYỆT ' + count + ' hồ sơ đối tác đã chọn?\nCác tài khoản này sẽ được nâng cấp lên quyền Partner trên sàn.')) {
            submitBulk('approve');
        }
    });

    // Nút Xác nhận từ chối trong modal hàng loạt
    btnConfirmBulkReject.addEventListener('click', function() {
        var reason = bulkRejectReasonInput.value.trim();
        submitBulk('reject', reason);
    });

    // Nút Áp dụng từ dropdown
    btnBulkApply.addEventListener('click', function() {
        var action = bulkSelectAction.value;
        if (!action) {
            alert('Vui lòng chọn một thao tác từ danh sách thả xuống.');
            return;
        }

        var count = document.querySelectorAll('.partner-checkbox:checked').length;
        if (action === 'approve') {
            if (confirm('Bạn có chắc chắn muốn PHÊ DUYỆT ' + count + ' hồ sơ đối tác đã chọn?')) {
                submitBulk('approve');
            }
        } else if (action === 'reject') {
            $('#bulkRejectModal').modal('show');
        } else if (action === 'pending') {
            if (confirm('Chuyển ' + count + ' hồ sơ đối tác đã chọn về trạng thái Chờ duyệt?')) {
                submitBulk('pending');
            }
        }
    });
});
</script>
@endsection
