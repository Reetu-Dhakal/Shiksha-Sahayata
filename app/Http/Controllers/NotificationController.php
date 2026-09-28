<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private readonly NotificationService $notifications) {}

    public function index(Request $request): View
    {
        $items = $this->notifications->forUser($request->user());

        return view('notifications.index', [
            'notifications' => $items,
            'unreadCount' => $items->filter(fn (Notification $notification): bool => $notification->isUnread())->count(),
        ]);
    }

    public function markAllRead(Request $request): RedirectResponse
    {
        $this->notifications->markAllRead($request->user());

        return back()->with('status', 'All notifications marked as read.');
    }

    public function markRead(Request $request, Notification $notification): RedirectResponse
    {
        abort_unless($notification->user_id === $request->user()->id, 403);

        $this->notifications->markRead($request->user(), $notification);

        return redirect($notification->link ?? route('notifications.index'));
    }
}
