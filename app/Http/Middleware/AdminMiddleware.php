<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // <-- Dòng bạn đang bị thiếu đây!
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra nếu đã đăng nhập VÀ có quyền admin
        if (Auth::check() && Auth::user()->role == 'admin') {
            return $next($request);
        }

        // Nếu không phải admin, đẩy về trang chủ kèm thông báo lỗi
        return redirect()->route('welcome')->with('error', 'Bạn không có quyền truy cập trang này!');
    }
}