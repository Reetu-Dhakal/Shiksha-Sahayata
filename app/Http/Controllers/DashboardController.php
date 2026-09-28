<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function show(Request $request): View
    {
        $user = $request->user();

        [$view, $nav] = match ($user->role) {
            Role::ADMIN => ['dashboard.admin', 'admin'],
            Role::SCHOOL_OFFICER => ['dashboard.school', 'school'],
            Role::LOCAL_OFFICER => ['dashboard.local', 'local'],
            Role::COMMITTEE => ['dashboard.committee', 'committee'],
            default => ['dashboard.student', 'student'],
        };

        return view($view, [
            'nav' => $nav,
            'notifications' => $user->unreadNotifications()->latest()->limit(5)->get(),
        ]);
    }
}
