<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{

    public function index(Request $request)
    {
        // استلام user ID من الـ request
        $userId = $request->user_id;

        // البحث عن المستخدم باستخدام الـ user ID
        $user = User::find($userId);

        // التحقق إذا كان المستخدم موجودًا
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // إرجاع الإشعارات غير المقروءة والإشعارات الكاملة
        return response()->json([
            'unread' => $user->unreadNotifications->isEmpty() ? [] : $user->unreadNotifications,
            'unread_count'=> $user->unreadNotifications->count(),
            'all' => $user->notifications->isEmpty() ? [] : $user->notifications,
        ]);
    }

    public function markAsRead(Request $request)
    {
        // استلام user ID من الـ request
        $userId = $request->user_id;

        // البحث عن المستخدم باستخدام الـ user ID
        $user = User::find($userId);

        // التحقق إذا كان المستخدم موجودًا
        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        // تحديد جميع الإشعارات غير المقروءة كمقروءة
        $user->unreadNotifications->markAsRead();

        return response()->json(['message' => 'Notifications marked as read']);
    }
}
