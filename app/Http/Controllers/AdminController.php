<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Appointment;
use App\Models\Rental;
use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;

class AdminController extends Controller
{
    public function dashboard()
    {
        // 1. Thống kê Lịch hẹn xem xe & Lái thử
        $totalAppointments = Appointment::count();
        $pendingAppointments = Appointment::where('status', 'pending')->count();
        $confirmedAppointments = Appointment::where('status', 'confirmed')->count();

        // 2. Thống kê Đơn thuê xe & Thuê tài xế
        $totalRentals = Rental::count();
        $selfDriveRentals = Rental::where('rental_type', 'self_drive')->count();
        $withDriverRentals = Rental::where('rental_type', 'with_driver')->count();
        $activeRentals = Rental::whereIn('rental_status', ['confirmed', 'in_progress'])->count();
        $totalRentalRevenue = Rental::whereIn('payment_status', ['deposit_paid', 'fully_paid'])->sum('total_rental_fee');

        // 3. Thống kê Đơn mua xe & Thanh toán MoMo
        $totalOrders = Order::count();
        $pendingOrders = Order::where('order_status', 'pending')->count();
        $totalOrderRevenue = Order::where('payment_status', 'paid')->sum('total_amount');

        $totalMomoRevenue = PaymentTransaction::where('gateway', 'momo')->where('status', 'paid')->sum('amount');
        $totalMomoCount = PaymentTransaction::where('gateway', 'momo')->count();
        $totalMomoSuccess = PaymentTransaction::where('gateway', 'momo')->where('status', 'paid')->count();
        $latestMomoTransactions = PaymentTransaction::with(['order', 'rental'])->orderBy('id', 'desc')->take(5)->get();

        // 4. Thống kê xe & người dùng
        $totalProducts = Product::count();
        $totalCategories = Category::count();
        $totalUsers = User::count();

        // 5. Danh sách mới nhất
        $latestAppointments = Appointment::with(['product', 'user'])->orderBy('id', 'desc')->take(5)->get();
        $latestRentals = Rental::with(['product', 'user'])->orderBy('id', 'desc')->take(5)->get();
        $latestOrders = Order::with(['user', 'items'])->orderBy('id', 'desc')->take(5)->get();

        return view('admin.dashboard', compact(
            'totalAppointments',
            'pendingAppointments',
            'confirmedAppointments',
            'totalRentals',
            'selfDriveRentals',
            'withDriverRentals',
            'activeRentals',
            'totalRentalRevenue',
            'totalOrders',
            'pendingOrders',
            'totalOrderRevenue',
            'totalMomoRevenue',
            'totalMomoCount',
            'totalMomoSuccess',
            'latestMomoTransactions',
            'latestOrders',
            'totalProducts',
            'totalCategories',
            'totalUsers',
            'latestAppointments',
            'latestRentals'
        ));
    }
}