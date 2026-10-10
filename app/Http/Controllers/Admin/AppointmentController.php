<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    // Danh sách toàn bộ lịch hẹn xem xe
    public function index(Request $request)
    {
        $query = Appointment::with(['product.category', 'user', 'partner', 'order']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('deal_status')) {
            $query->where('deal_status', $request->deal_status);
        }

        if ($request->filled('partner_id')) {
            $query->where('partner_id', $request->partner_id);
        }

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('appointment_code', 'like', "%{$keyword}%")
                    ->orWhere('customer_name', 'like', "%{$keyword}%")
                    ->orWhere('customer_phone', 'like', "%{$keyword}%")
                    ->orWhere('customer_email', 'like', "%{$keyword}%")
                    ->orWhereHas('product', function ($pq) use ($keyword) {
                        $pq->where('name', 'like', "%{$keyword}%");
                    })
                    ->orWhereHas('partner', function ($prtq) use ($keyword) {
                        $prtq->where('name', 'like', "%{$keyword}%")
                            ->orWhere('partner_showroom_name', 'like', "%{$keyword}%");
                    });
            });
        }

        $appointments = $query->orderBy('appointment_date', 'desc')->orderBy('id', 'desc')->paginate(15)->withQueryString();

        $stats = [
            'total' => Appointment::count(),
            'pending' => Appointment::where('status', 'pending')->count(),
            'confirmed' => Appointment::where('status', 'confirmed')->count(),
            'completed' => Appointment::where('status', 'completed')->count(),
            'deal_won' => Appointment::where('deal_status', 'deal_won')->count(),
            'deal_lost' => Appointment::where('deal_status', 'deal_lost')->count(),
        ];

        $partners = \App\Models\User::where('role', 'partner')->orderBy('name')->get();

        return view('admin.appointments.index', compact('appointments', 'stats', 'partners'));
    }

    // Chi tiết lịch hẹn
    public function show(Appointment $appointment)
    {
        $appointment->load(['product', 'user']);
        return view('admin.appointments.show', compact('appointment'));
    }

    // Cập nhật trạng thái lịch hẹn & hoa hồng giới thiệu bán xe
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $request->validate([
            'status' => 'required|in:pending,confirmed,completed,cancelled',
            'deal_status' => 'nullable|in:negotiating,deal_won,deal_lost',
            'deal_price' => 'nullable|numeric|min:0',
            'commission_amount' => 'nullable|numeric|min:0',
            'commission_status' => 'nullable|in:pending,paid',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $dealStatus = $request->input('deal_status', $appointment->deal_status ?: 'negotiating');
        $dealPrice = $request->filled('deal_price') ? (float) $request->deal_price : $appointment->deal_price;
        $commissionAmount = $appointment->commission_amount;

        if ($dealStatus === 'deal_won') {
            if ($request->filled('commission_amount') && (float) $request->commission_amount > 0) {
                $commissionAmount = (float) $request->commission_amount;
            } elseif ($dealPrice > 0) {
                $commissionAmount = round($dealPrice * 0.01);
            } else {
                $basePrice = $appointment->product?->price ?: 500000000;
                $commissionAmount = round($basePrice * 0.01);
            }
        }

        $appointment->update([
            'status' => $request->status,
            'deal_status' => $dealStatus,
            'deal_price' => $dealPrice,
            'commission_amount' => $commissionAmount,
            'commission_status' => $request->input('commission_status', $appointment->commission_status ?: 'pending'),
            'admin_note' => $request->admin_note,
        ]);

        if ($dealStatus === 'deal_won') {
            $appointment->product?->update([
                'rental_status' => 'sold',
                'quantity' => 0,
            ]);

            // Đồng bộ Đơn mua xe (Order)
            $paymentMethod = $request->input('payment_method', 'bank_transfer');
            \App\Models\Order::syncFromAppointment($appointment, $paymentMethod, 'paid');
        } elseif ($dealStatus === 'deal_lost' || $request->status === 'cancelled') {
            $existingOrder = \App\Models\Order::where('appointment_id', $appointment->id)->first();
            if ($existingOrder) {
                $existingOrder->update(['order_status' => 'cancelled']);
            }

            if ($appointment->product && $appointment->product->rental_status === 'sold') {
                $hasOtherPaidWon = Appointment::where('product_id', $appointment->product_id)
                    ->where('id', '!=', $appointment->id)
                    ->where('deal_status', 'deal_won')
                    ->where('commission_status', 'paid')
                    ->exists();
                if (!$hasOtherPaidWon) {
                    $appointment->product->update([
                        'rental_status' => 'available',
                        'quantity' => 1,
                    ]);
                }
            }
        }

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái lịch hẹn & hoa hồng giới thiệu mua xe thành công!');
    }

    // Cập nhật trạng thái hàng loạt cho nhiều lịch hẹn cùng lúc
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'appointment_ids' => 'required|array',
            'appointment_ids.*' => 'exists:appointments,id',
            'status' => 'nullable|in:pending,confirmed,completed,cancelled',
            'commission_status' => 'nullable|in:pending,paid',
        ]);

        $updateData = [];
        if ($request->filled('status')) {
            $updateData['status'] = $request->status;
        }
        if ($request->filled('commission_status')) {
            $updateData['commission_status'] = $request->commission_status;
        }

        if (empty($updateData)) {
            return redirect()->back()->with('error', 'Vui lòng chọn ít nhất một trạng thái để cập nhật hàng loạt!');
        }

        Appointment::whereIn('id', $request->appointment_ids)->update($updateData);

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái thành công cho ' . count($request->appointment_ids) . ' lịch hẹn được chọn!');
    }

    // Xóa lịch hẹn
    public function destroy(Appointment $appointment)
    {
        $appointment->delete();
        return redirect()->route('admin.appointments.index')->with('success', 'Đã xóa lịch hẹn khỏi hệ thống.');
    }
}
