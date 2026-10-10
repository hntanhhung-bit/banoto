<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class PartnerApprovalController extends Controller
{
    // Danh sách hồ sơ đăng ký đối tác Showroom & Nhà xe
    public function index(Request $request)
    {
        $query = User::where('role', '!=', 'admin');

        // Lọc theo trạng thái phê duyệt
        $status = $request->input('status', 'all');
        if (in_array($status, ['pending', 'approved', 'rejected'])) {
            if ($status === 'pending') {
                $query->where(function($q) {
                    $q->where('partner_status', 'pending')
                      ->orWhere('partner_status', 'none')
                      ->orWhereNull('partner_status');
                });
            } else {
                $query->where('partner_status', $status);
            }
        }

        // Tìm kiếm theo từ khóa
        if ($request->filled('keyword')) {
            $kw = trim($request->keyword);
            $query->where(function ($q) use ($kw) {
                $q->where('name', 'like', "%{$kw}%")
                  ->orWhere('showroom_name', 'like', "%{$kw}%")
                  ->orWhere('email', 'like', "%{$kw}%")
                  ->orWhere('phone', 'like', "%{$kw}%")
                  ->orWhere('representative_name', 'like', "%{$kw}%")
                  ->orWhere('tax_code', 'like', "%{$kw}%")
                  ->orWhere('id_card_number', 'like', "%{$kw}%");
            });
        }

        // Đếm số lượng theo trạng thái
        $countPending = User::where(function($q) {
            $q->where('partner_status', 'pending')
              ->orWhere('partner_status', 'none')
              ->orWhereNull('partner_status');
        })->where('role', '!=', 'admin')->count();

        $countApproved = User::where('partner_status', 'approved')->where('role', '!=', 'admin')->count();
        $countRejected = User::where('partner_status', 'rejected')->where('role', '!=', 'admin')->count();
        $countTotal = User::where('role', '!=', 'admin')->count();

        $partners = $query->orderByRaw("CASE 
            WHEN partner_status = 'pending' THEN 1 
            WHEN partner_status = 'none' OR partner_status IS NULL THEN 2 
            WHEN partner_status = 'approved' THEN 3 
            WHEN partner_status = 'rejected' THEN 4 
            ELSE 5 
        END")
            ->orderBy('id', 'desc')
            ->paginate(15)
            ->withQueryString();

        return view('admin.partners.index', compact(
            'partners',
            'status',
            'countPending',
            'countApproved',
            'countRejected',
            'countTotal'
        ));
    }

    // Thao tác hàng loạt thay đổi trạng thái nhiều hồ sơ đối tác
    public function bulkAction(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'bulk_action' => 'required|in:approve,reject,pending',
            'bulk_reason' => 'nullable|string|max:500',
        ], [
            'user_ids.required' => 'Vui lòng tích chọn ít nhất 1 hồ sơ đối tác để thực hiện.',
            'user_ids.min' => 'Vui lòng tích chọn ít nhất 1 hồ sơ đối tác để thực hiện.',
            'bulk_action.required' => 'Vui lòng chọn thao tác cần thực hiện.',
        ]);

        $userIds = $request->input('user_ids', []);
        $action = $request->input('bulk_action');
        $reason = $request->input('bulk_reason');
        $count = count($userIds);

        if ($action === 'approve') {
            User::whereIn('id', $userIds)->update([
                'partner_status' => 'approved',
                'role' => 'partner',
                'partner_approved_at' => now(),
                'partner_reject_reason' => null,
            ]);
            return redirect()->back()->with('success', "✅ Đã PHÊ DUYỆT thành công {$count} hồ sơ đối tác được chọn! Các tài khoản đã được cấp quyền Partner trên sàn.");
        } elseif ($action === 'reject') {
            User::whereIn('id', $userIds)->update([
                'partner_status' => 'rejected',
                'role' => 'user',
                'partner_reject_reason' => $reason ?: 'Hồ sơ chưa đạt tiêu chuẩn theo quy chế xét duyệt của Sàn AutoCar.',
            ]);
            return redirect()->back()->with('success', "❌ Đã TỪ CHỐI {$count} hồ sơ đối tác được chọn.");
        } elseif ($action === 'pending') {
            User::whereIn('id', $userIds)->update([
                'partner_status' => 'pending',
                'role' => 'user',
                'partner_reject_reason' => null,
            ]);
            return redirect()->back()->with('success', "⏳ Đã chuyển {$count} hồ sơ về trạng thái Chờ xét duyệt.");
        }

        return redirect()->back();
    }

    // Phê duyệt hồ sơ đối tác
    public function approve(User $user)
    {
        $user->update([
            'partner_status' => 'approved',
            'role' => 'partner',
            'partner_approved_at' => now(),
            'partner_reject_reason' => null,
        ]);

        $displayName = $user->showroom_name ?: $user->name;
        return redirect()->back()->with('success', "✅ Đã phê duyệt thành công hồ sơ đối tác '{$displayName}'. Tài khoản đã được nâng cấp lên Partner để quản lý Showroom và đón khách trên sàn!");
    }

    // Từ chối hồ sơ đối tác (kèm lý do giải trình)
    public function reject(Request $request, User $user)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
        ], [
            'reason.required' => 'Vui lòng nhập lý do từ chối hồ sơ đối tác để thông báo cho người đăng ký.',
        ]);

        $user->update([
            'partner_status' => 'rejected',
            'role' => 'user', // Rút quyền partner nếu có
            'partner_reject_reason' => $request->reason,
        ]);

        $displayName = $user->showroom_name ?: $user->name;
        return redirect()->back()->with('success', "❌ Đã từ chối hồ sơ của đối tác '{$displayName}'. Lý do từ chối đã được lưu và gửi tới người đăng ký.");
    }
}
