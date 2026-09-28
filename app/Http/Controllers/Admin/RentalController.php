<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Rental;
use App\Models\PaymentTransaction;
use Illuminate\Http\Request;

class RentalController extends Controller
{
    // Danh sách toàn bộ đơn thuê xe
    public function index(Request $request)
    {
        $query = Rental::with(['product.partner', 'user', 'partner']);

        if ($request->filled('rental_type')) {
            $query->where('rental_type', $request->rental_type);
        }

        if ($request->filled('rental_status')) {
            $query->where('rental_status', $request->rental_status);
        }

        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        // Lọc theo tiến trình đối tác & trạng thái hoàn cọc
        if ($request->filled('progress_filter')) {
            $filter = $request->progress_filter;
            if ($filter === 'waiting_refund') {
                $query->where(function ($q) {
                    $q->where('refund_status', 'waiting_admin')
                      ->orWhere(function ($sub) {
                          $sub->where('rental_status', 'returned')
                              ->where('refund_status', '!=', 'refunded');
                      });
                });
            } elseif ($filter === 'in_progress') {
                $query->where('rental_status', 'in_progress');
            } elseif ($filter === 'pending_handover') {
                $query->where('rental_status', 'pending');
            } elseif ($filter === 'refunded') {
                $query->where('refund_status', 'refunded');
            }
        }

        if ($request->filled('keyword')) {
            $keyword = trim($request->keyword);
            $query->where(function ($q) use ($keyword) {
                $q->where('rental_code', 'like', "%{$keyword}%")
                  ->orWhere('customer_name', 'like', "%{$keyword}%")
                  ->orWhere('customer_phone', 'like', "%{$keyword}%");
            });
        }

        $rentals = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('admin.rentals.index', compact('rentals'));
    }

    // Chi tiết đơn thuê xe
    public function show(Rental $rental)
    {
        $rental->load(['product.partner', 'user', 'partner', 'paymentTransactions']);
        return view('admin.rentals.show', compact('rental'));
    }

    // Cập nhật trạng thái thuê xe, thanh toán & gán tài xế
    public function updateStatus(Request $request, Rental $rental)
    {
        $request->validate([
            'rental_status' => 'required|in:pending,confirmed,in_progress,returned,cancelled',
            'payment_status' => 'required|in:unpaid,deposit_paid,fully_paid',
            'driver_name' => 'nullable|string|max:255',
            'driver_phone' => 'nullable|string|max:50',
            'admin_note' => 'nullable|string|max:1000',
        ]);

        $newPaymentStatus = $request->payment_status;

        $updateData = [
            'rental_status' => $request->rental_status,
            'payment_status' => $newPaymentStatus,
            'driver_name' => $request->driver_name,
            'driver_phone' => $request->driver_phone,
            'admin_note' => $request->admin_note,
        ];

        // Nếu admin chuyển sang đã nhận cọc hoặc đã thanh toán đủ mà chưa có mã bàn giao -> tự sinh mã 6 số
        if (in_array($newPaymentStatus, ['deposit_paid', 'fully_paid']) && empty($rental->handover_code)) {
            $updateData['handover_code'] = (string) rand(100000, 999999);
        } elseif ($newPaymentStatus === 'unpaid' && $rental->handover_status !== 'verified') {
            // Nếu admin đổi lại là chưa nhận cọc và xe chưa được đối tác bàn giao -> thu hồi / ẩn mã
            $updateData['handover_code'] = null;
        }

        $rental->update($updateData);

        // Cập nhật trạng thái xe tương ứng
        if (in_array($request->rental_status, ['confirmed', 'in_progress'])) {
            $rental->product?->update(['rental_status' => 'rented']);
        } elseif (in_array($request->rental_status, ['returned', 'cancelled'])) {
            $rental->product?->update(['rental_status' => 'available']);
        }

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái đơn thuê xe thành công!');
    }

    // Admin xác nhận đã nhận cọc (Tránh trường hợp khách thanh toán/chuyển khoản trễ mà không nhận được xe)
    public function confirmDeposit(Request $request, Rental $rental)
    {
        if (in_array($rental->payment_status, ['deposit_paid', 'fully_paid'])) {
            return redirect()->back()->with('warning', 'Đơn thuê xe này đã được xác nhận đã nhận cọc trước đó.');
        }

        $handoverCode = $rental->handover_code ?: (string) rand(100000, 999999);

        $rental->update([
            'payment_status' => 'deposit_paid',
            'handover_code' => $handoverCode,
        ]);

        PaymentTransaction::create([
            'rental_id' => $rental->id,
            'gateway' => 'admin_manual_deposit',
            'gateway_order_id' => 'ADMIN_CONFIRM_' . $rental->rental_code . '_' . time(),
            'amount' => $rental->deposit_amount,
            'status' => 'paid',
            'message' => 'Admin xác nhận đã nhận tiền cọc ' . number_format($rental->deposit_amount) . 'đ thành công (tránh trường hợp thanh toán trễ khách mất quyền nhận xe). Đã kích hoạt mã OTP đối chiếu: ' . $handoverCode,
            'paid_at' => now(),
        ]);

        return redirect()->back()->with('success', "✅ Đã xác nhận nhận cọc thành công cho đơn #{$rental->rental_code}! Mã đối chiếu nhận xe cấp cho khách là: {$handoverCode}");
    }

    // Xử lý Hoàn cọc Ký quỹ cho khách hàng (Admin Escrow Refund)
    public function refund(Request $request, Rental $rental)
    {
        $request->validate([
            'refund_amount' => 'required|numeric|min:0',
            'refund_holding_fee' => 'nullable|numeric|min:0',
            'refund_notes' => 'nullable|string|max:1000',
        ]);

        $refundAmount = (float) $request->refund_amount;
        $holdingFee = (float) $request->input('refund_holding_fee', 0);
        $refundNotes = $request->refund_notes;

        $status = ($holdingFee > 0 && $refundAmount == 0) ? 'holding' : 'refunded';

        $rental->update([
            'refund_amount' => $refundAmount,
            'refund_holding_fee' => $holdingFee,
            'refund_notes' => $refundNotes,
            'refund_status' => $status,
            'refunded_at' => now(),
            'rental_status' => 'returned',
        ]);

        // Giải phóng trạng thái xe nếu chưa mở
        $rental->product?->update(['rental_status' => 'available']);

        PaymentTransaction::create([
            'rental_id' => $rental->id,
            'gateway' => 'admin_escrow_refund',
            'gateway_order_id' => 'ADMIN_REFUND_' . $rental->rental_code,
            'amount' => $refundAmount,
            'status' => 'success',
            'message' => "Admin Sàn AutoCar đã thực hiện chuyển khoản hoàn cọc " . number_format($refundAmount) . "đ cho khách hàng {$rental->customer_name} vào TK {$rental->refund_account_number} ({$rental->refund_bank_name} - {$rental->refund_account_holder}). Giữ lại: " . number_format($holdingFee) . "đ." . ($refundNotes ? " Ghi chú: {$refundNotes}" : ''),
            'paid_at' => now(),
        ]);

        return redirect()->back()->with('success', '✅ Đã hoàn tất thanh toán hoàn cọc ' . number_format($refundAmount) . 'đ cho khách hàng thành công!');
    }

    // Cập nhật trạng thái hàng loạt cho nhiều đơn thuê xe cùng lúc
    public function bulkStatus(Request $request)
    {
        $request->validate([
            'rental_ids' => 'required|array',
            'rental_ids.*' => 'exists:rentals,id',
            'rental_status' => 'nullable|in:pending,confirmed,in_progress,returned,cancelled',
            'payment_status' => 'nullable|in:unpaid,deposit_paid,fully_paid',
        ]);

        $updateData = [];
        if ($request->filled('rental_status')) {
            $updateData['rental_status'] = $request->rental_status;
        }
        if ($request->filled('payment_status')) {
            $updateData['payment_status'] = $request->payment_status;
        }

        if (empty($updateData)) {
            return redirect()->back()->with('error', 'Vui lòng chọn ít nhất một trạng thái cần cập nhật hàng loạt!');
        }

        $rentals = Rental::whereIn('id', $request->rental_ids)->get();
        foreach ($rentals as $rental) {
            $dataToSave = $updateData;
            if (isset($updateData['payment_status']) && in_array($updateData['payment_status'], ['deposit_paid', 'fully_paid']) && empty($rental->handover_code)) {
                $dataToSave['handover_code'] = (string) rand(100000, 999999);
            }
            $rental->update($dataToSave);

            if (isset($updateData['rental_status'])) {
                if (in_array($updateData['rental_status'], ['confirmed', 'in_progress'])) {
                    $rental->product?->update(['rental_status' => 'rented']);
                } elseif (in_array($updateData['rental_status'], ['returned', 'cancelled'])) {
                    $rental->product?->update(['rental_status' => 'available']);
                }
            }
        }

        return redirect()->back()->with('success', 'Đã cập nhật trạng thái thành công cho ' . count($request->rental_ids) . ' đơn thuê xe được chọn!');
    }

    // Xóa đơn thuê xe
    public function destroy(Rental $rental)
    {
        $rental->delete();
        return redirect()->route('admin.rentals.index')->with('success', 'Đã xóa đơn thuê xe.');
    }
}
