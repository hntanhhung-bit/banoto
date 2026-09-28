<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    // Danh sách người dùng
    public function index(Request $request)
    {
        $query = User::query();

        // Tìm kiếm theo tên hoặc email
        if ($request->filled('keyword')) {
            $keyword = trim($request->input('keyword'));
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'like', "%{$keyword}%")
                  ->orWhere('email', 'like', "%{$keyword}%");
            });
        }

        // Lọc theo vai trò (Role)
        if ($request->filled('role')) {
            $query->where('role', $request->input('role'));
        }

        // Lọc theo trạng thái xác thực email
        if ($request->filled('verified')) {
            if ($request->input('verified') === 'yes') {
                $query->whereNotNull('email_verified_at');
            } elseif ($request->input('verified') === 'no') {
                $query->whereNull('email_verified_at');
            }
        }

        $users = $query->orderBy('id', 'desc')->paginate(10)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    // Đổi vai trò tài khoản (Admin / Partner / Customer)
    public function toggleRole(Request $request, User $user)
    {
        // Không cho phép tự đổi quyền của chính mình
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Bạn không thể tự thay đổi vai trò của chính tài khoản mình đang đăng nhập!');
        }

        if ($request->filled('role')) {
            $request->validate([
                'role' => 'required|in:admin,partner,customer',
            ]);
            $newRole = $request->input('role');
        } else {
            // Chu kỳ chuyển đổi nếu click nút nhanh: customer -> partner -> admin -> customer
            if ($user->role === 'customer') {
                $newRole = 'partner';
            } elseif ($user->role === 'partner') {
                $newRole = 'admin';
            } else {
                $newRole = 'customer';
            }
        }

        $roleLabels = [
            'admin' => 'Quản trị viên (Admin)',
            'partner' => 'Đối tác Showroom / Nhà xe (Partner)',
            'customer' => 'Khách hàng (Customer)',
        ];

        $user->role = $newRole;
        $user->save();

        return redirect()->back()->with('success', 'Đã cập nhật vai trò của tài khoản "' . $user->name . '" thành: ' . ($roleLabels[$newRole] ?? strtoupper($newRole)));
    }

    // Xóa tài khoản người dùng
    public function destroy(User $user)
    {
        // Không cho phép tự xóa tài khoản của chính mình
        if ($user->id === Auth::id()) {
            return redirect()->back()->with('error', 'Bạn không thể tự xóa tài khoản của chính mình!');
        }

        $userName = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'Đã xóa người dùng "' . $userName . '" khỏi hệ thống.');
    }
}
