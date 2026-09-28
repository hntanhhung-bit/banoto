<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Message;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    /**
     * Lấy danh sách User để Admin nhắn tin (hỗ trợ lọc đã nhắn tin, tất cả khách hàng, và tìm kiếm)
     */
    public function getUsers(Request $request = null)
    {
        $request = $request ?? request();
        $adminId = Auth::id();
        $tab = $request->input('tab', 'recent'); // 'recent' hoặc 'all'
        $search = trim($request->input('q', ''));

        if ($tab === 'all' || !empty($search)) {
            // Lấy tất cả khách hàng hoặc theo từ khóa tìm kiếm
            $query = User::where('id', '!=', $adminId)
                ->where('role', '!=', 'admin');

            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('showroom_name', 'like', "%{$search}%");
                });
            }

            $users = $query->latest()->take(30)->get(['id', 'name', 'email', 'showroom_name', 'role']);
        } else {
            // Tab 'recent': Những người đã từng nhắn tin với Admin
            $userIds = Message::where('receiver_id', $adminId)
                ->orWhere('sender_id', $adminId)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($msg) use ($adminId) {
                    return $msg->sender_id == $adminId ? $msg->receiver_id : $msg->sender_id;
                })
                ->unique()
                ->toArray();

            $users = User::whereIn('id', $userIds)
                ->where('id', '!=', $adminId)
                ->select('id', 'name', 'email', 'showroom_name', 'role')
                ->get();

            // Nếu chưa có ai nhắn, tự động gợi ý các khách hàng mới nhất để Admin chủ động nhắn
            if ($users->isEmpty()) {
                $users = User::where('id', '!=', $adminId)
                    ->where('role', '!=', 'admin')
                    ->latest()
                    ->take(10)
                    ->select('id', 'name', 'email', 'showroom_name', 'role')
                    ->get();
            }
        }

        // Đính kèm số tin nhắn chưa đọc và thông tin tin cuối cùng
        foreach ($users as $u) {
            $u->unread_count = Message::where('sender_id', $u->id)
                ->where('receiver_id', $adminId)
                ->where('is_read', false)
                ->count();

            $lastMsg = Message::where(function ($q) use ($u, $adminId) {
                $q->where('sender_id', $u->id)->where('receiver_id', $adminId);
            })->orWhere(function ($q) use ($u, $adminId) {
                $q->where('sender_id', $adminId)->where('receiver_id', $u->id);
            })->latest()->first();

            $u->last_message = $lastMsg ? $lastMsg->content : '';
            $u->last_message_time = $lastMsg ? $lastMsg->created_at->format('H:i d/m') : '';
            $u->has_chatted = $lastMsg ? true : false;
        }

        // Sắp xếp: Ưu tiên có tin chưa đọc lên đầu, sau đó đến có tin nhắn mới nhất
        $sorted = $users->sort(function ($a, $b) {
            if ($a->unread_count !== $b->unread_count) {
                return $b->unread_count <=> $a->unread_count;
            }
            if ($a->has_chatted !== $b->has_chatted) {
                return $b->has_chatted <=> $a->has_chatted;
            }
            return 0;
        })->values();

        return response()->json($sorted);
    }

    /**
     * Lấy lịch sử tin nhắn của một User cụ thể
     */
    public function getMessages($userId)
    {
        $adminId = Auth::id();

        // Đánh dấu tin nhắn từ user này gửi cho admin là đã đọc
        Message::where('sender_id', $userId)
            ->where('receiver_id', $adminId)
            ->where('is_read', false)
            ->update(['is_read' => true]);

        $messages = Message::with('sender')
            ->where(function ($q) use ($userId, $adminId) {
                $q->where('sender_id', $userId)->where('receiver_id', $adminId);
            })
            ->orWhere(function ($q) use ($userId, $adminId) {
                $q->where('sender_id', $adminId)->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * Admin gửi tin nhắn phản hồi cho User
     */
    public function send(Request $request)
    {
        $userId = $request->input('user_id') ?? $request->input('receiver_id');
        $content = $request->input('message') ?? $request->input('content');

        if (!$userId || empty(trim($content))) {
            return response()->json(['error' => 'Thiếu người nhận hoặc nội dung tin nhắn'], 400);
        }

        $message = Message::create([
            'sender_id' => Auth::id(),
            'receiver_id' => $userId,
            'content' => $content,
            'is_read' => false, // Khách hàng chưa đọc
        ]);

        $message->load('sender');

        return response()->json($message);
    }
}
