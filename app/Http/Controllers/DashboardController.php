<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Services\DashboardService;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly DashboardService $dashboard,
        private readonly NotificationService $notifications,
    ) {}

    public function show(Request $request): View
    {
        $user = $request->user();

        [$view, $nav] = match ($user->role) {
            Role::ADMIN => ['dashboard.admin', 'admin'],
            Role::SCHOOL_OFFICER => ['dashboard.school', 'school'],
            Role::LOCAL_OFFICER => ['dashboard.local', 'local'],
            Role::COMMITTEE => ['dashboard.committee', 'committee'],
            Role::GUARDIAN => ['dashboard.student', 'guardian'],
            default => ['dashboard.student', 'student'],
        };

        return view($view, [
            'nav' => $nav,
            'dashboard' => $this->dashboard->forUser($user),
            'notifications' => $this->notifications->forUser($user)
                ->filter(fn ($notification): bool => $notification->isUnread())
                ->take(5)
                ->values(),
        ]);
    }
}
