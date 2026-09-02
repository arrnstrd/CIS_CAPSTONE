<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /**
     * Display a listing of all notifications for the authenticated teacher.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->paginate(15);
        $unreadCount = $user->unreadNotifications()->count();

        return view('teacher-modules.notifications.notifications-index', [
            'notifications' => $notifications,
            'unreadCount' => $unreadCount,
        ]);
    }

    /**
     * Mark a specific notification as read.
     */
    public function markAsRead(Request $request, string $id)
    {
        $user = $request->user();
        $notification = $user->notifications()->where('id', $id)->firstOrFail();

        if (is_null($notification->read_at)) {
            $notification->markAsRead();
        }

        $actionUrl = $notification->data['data']['url'] ?? $notification->data['url'] ?? null;

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => $user->unreadNotifications()->count(),
                'action_url' => $actionUrl,
            ]);
        }

        if ($actionUrl && filter_var($actionUrl, FILTER_VALIDATE_URL)) {
            return redirect()->to($actionUrl);
        }

        return redirect()->back()->with('success', 'Notification marked as read.');
    }

    /**
     * Mark all unread notifications for the authenticated teacher as read.
     */
    public function markAllAsRead(Request $request)
    {
        $user = $request->user();
        $user->unreadNotifications->markAsRead();

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'unread_count' => 0,
            ]);
        }

        return redirect()->back()->with('success', 'All notifications marked as read.');
    }
}