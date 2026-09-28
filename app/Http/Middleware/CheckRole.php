<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Restrict a route to the given roles, e.g. ->middleware('role:admin,school_officer').
     *
     * @param  string  $roles  Comma separated role values.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(Response::HTTP_UNAUTHORIZED);
        }

        if (! $user->isActive()) {
            auth()->logout();

            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            return redirect()->route('login')->withErrors([
                'email' => __('auth.disabled'),
            ]);
        }

        $allowed = array_map('trim', $roles);

        if (! in_array($user->role->value, $allowed, true)) {
            abort(Response::HTTP_FORBIDDEN, __('auth.forbidden'));
        }

        return $next($request);
    }
}
