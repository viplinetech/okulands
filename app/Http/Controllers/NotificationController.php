<?php

/**
 * Crafted & Developed by Vipline Technologies Limited - ViplineTech (www.viplinetech.com)
 */

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** The notification centre, shared by the realtor app and the admin (each with its own layout). */
class NotificationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $notifications = $user->notifications()->paginate(20);
        $unreadIds = $user->unreadNotifications()->pluck('id')->all();

        // Opening the page counts as reading; "new" markers use the ids captured above.
        $user->unreadNotifications()->update(['read_at' => now()]);

        return view('notifications.index', [
            'notifications' => $notifications,
            'unreadIds' => $unreadIds,
            'isAdmin' => $user->isAdmin(),
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $request->user()->unreadNotifications()->update(['read_at' => now()]);

        return back();
    }
}
