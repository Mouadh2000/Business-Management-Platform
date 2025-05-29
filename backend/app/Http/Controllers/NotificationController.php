<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use App\Models\Admin;

class NotificationController extends Controller
{
    public function index(Request $request)
    {
        /** @var Admin $admin */
        $admin = $request->user();
        return response()->json([
            'notifications' => $admin->notifications()->take(10)->get(),
            'unread_count' => $admin->unreadNotifications->count()
        ]);
    }

    public function markAsRead(Request $request, $id)
    {
        /** @var Admin $admin */
        $admin = $request->user();
        $notification = $admin->notifications()->where('id', $id)->first();
        if ($notification) {
            $notification->markAsRead();
        }
        return response()->json(['success' => true]);
    }

    public function markAllAsRead(Request $request)
    {
        /** @var Admin $admin */
        $admin = $request->user();
        $admin->unreadNotifications->markAsRead();
        return response()->json(['success' => true]);
    }
}