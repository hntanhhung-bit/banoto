<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function showRegistrationForm()
    {
        if (Auth::check()) {
            return Auth::user()->role === 'admin' 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('welcome');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);

        try {
            Log::info('Registering user with email: ' . $request->email);
            
            $isOwner = strtolower(trim($request->email)) === 'hntanhhung@gmail.com';

            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => $request->password,
                'role' => 'customer',
                'email_verified_at' => $isOwner ? null : now(), // Tự động kích hoạt đối với các email thử nghiệm
            ]);

            if ($isOwner) {
                try {
                    event(new \Illuminate\Auth\Events\Registered($user));
                } catch (\Exception $mailEx) {
                    Log::warning('Could not send verification email: ' . $mailEx->getMessage());
                }
                return redirect()->route('login')->with('success', 'Đăng ký thành công! Vui lòng đăng nhập và kiểm tra hộp thư email hntanhhung@gmail.com.');
            }

            // Với mọi tài khoản Gmail khác: tự động đăng nhập và trải nghiệm ngay
            Auth::login($user);
            return redirect()->route('welcome')->with('success', 'Đăng ký tài khoản thành công! Tài khoản của bạn đã được tự động xác thực để sử dụng ngay.');
        } catch (\Exception $e) {
            Log::error('Registration failed: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Đăng ký thất bại: ' . $e->getMessage());
        }
    }

    public function showLoginForm()
    {
        if (Auth::check()) {
            return Auth::user()->role === 'admin' 
                ? redirect()->route('admin.dashboard') 
                : redirect()->route('welcome');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // 1. Kiểm tra tài khoản có tồn tại không
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->with('error', 'Email "' . $request->email . '" không tồn tại trong hệ thống.');
        }

        // 2. Kiểm tra mật khẩu có khớp không
        if (!Hash::check($request->password, $user->password)) {
            return redirect()->back()
                ->withInput($request->only('email'))
                ->with('error', 'Mật khẩu bạn nhập không chính xác. Vui lòng kiểm tra lại.');
        }

        // 3. Tiến hành đăng nhập phiên làm việc
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();

        // 4. Chuyển hướng chính xác theo vai trò (Role)
        if ($user->role === 'admin') {
            return redirect()->route('admin.dashboard')->with('success', 'Đăng nhập thành công với quyền Quản trị viên!');
        }

        return redirect()->route('welcome')->with('success', 'Đăng nhập thành công! Chào mừng ' . $user->name . '.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Bạn đã đăng xuất thành công.');
    }
}