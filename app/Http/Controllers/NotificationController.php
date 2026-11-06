<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function markRead(Request $request)
    {
        $request->user()->roomNotifications()->where('is_read', false)->update(['is_read' => true]);

        return back();
    }
}
