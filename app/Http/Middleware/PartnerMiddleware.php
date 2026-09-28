<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class PartnerMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('partner.register')->with('info', 'Vui lòng đăng nhập hoặc nộp hồ sơ đối tác để tiếp tục.');
        }

        $user = Auth::user();

        // Admin có toàn quyền truy cập kênh đối tác
        if ($user->role === 'admin') {
            return $next($request);
        }

        // Đối tác đã được admin phê duyệt
        if ($user->role === 'partner' && $user->partner_status === 'approved') {
            return $next($request);
        }

        // Đang chờ xét duyệt
        if ($user->partner_status === 'pending') {
            return redirect()->route('partner.pending')->with('warning', 'Hồ sơ đối tác của bạn đang chờ Ban Quản Trị thẩm định và phê duyệt.');
        }

        // Bị từ chối
        if ($user->partner_status === 'rejected') {
            return redirect()->route('partner.pending')->with('error', 'Hồ sơ đối tác của bạn chưa được duyệt.');
        }

        return redirect()->route('partner.register')->with('info', 'Bạn chưa đăng ký làm Đối tác Showroom / Nhà xe. Vui lòng nộp hồ sơ để được xét duyệt.');
    }
}
