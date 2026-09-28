<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Gửi tin nhắn từ User tới Admin
     */
    public function send(Request $request)
    {
        $messageText = $request->input('message') ?? $request->input('content');

        if (empty(trim($messageText))) {
            return response()->json(['error' => 'Nội dung tin nhắn không được để trống'], 400);
        }

        // Ưu tiên tìm user có role là admin, nếu không thấy thì mặc định lấy ID 1
        $admin = User::where('role', 'admin')->first();
        $receiverId = $admin ? $admin->id : 1;

        try {
            $message = Message::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $receiverId,
                'content' => $messageText,
                'is_read' => false,
            ]);

            $message->load('sender');

            return response()->json($message);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Không thể gửi tin nhắn: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Lấy lịch sử chat giữa User hiện tại và Admin
     */
    public function getMessages(Request $request = null)
    {
        $request = $request ?? request();
        $userId = Auth::id();

        // Tìm Admin để lọc tin nhắn qua lại
        $admin = User::where('role', 'admin')->first();
        $adminId = $admin ? $admin->id : 1;

        // Nếu không phải là gọi kiểm tra ngầm (check_only), đánh dấu các tin nhắn Admin gửi cho User là đã đọc
        if (!$request->has('check_only')) {
            Message::where('sender_id', $adminId)
                ->where('receiver_id', $userId)
                ->where('is_read', false)
                ->update(['is_read' => true]);
        }

        $unreadCount = Message::where('sender_id', $adminId)
            ->where('receiver_id', $userId)
            ->where('is_read', false)
            ->count();

        $messages = Message::with(['sender', 'receiver'])
            ->where(function ($q) use ($userId, $adminId) {
                $q->where('sender_id', $userId)->where('receiver_id', $adminId);
            })
            ->orWhere(function ($q) use ($userId, $adminId) {
                $q->where('sender_id', $adminId)->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json([
            'messages' => $messages,
            'unread_count' => $unreadCount,
        ]);
    }
}
