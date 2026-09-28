<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class RegisteredUserController extends Controller
{
    public function create()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $user = DB::transaction(fn () => User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->filled('email') ? $request->string('email')->toString() : null,
            'phone' => $request->filled('phone') ? $request->string('phone')->toString() : null,
            'password' => $request->string('password')->toString(),
            'role' => $request->accountRole(),
            'status' => User::STATUS_ACTIVE,
        ]));

        event(new Registered($user));

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
