<?php

namespace App\Http\Controllers;

use App\Models\Award;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AwardVerificationController extends Controller
{
    public function form(Request $request): View|RedirectResponse
    {
        $code = trim((string) $request->query('code'));

        if ($code !== '') {
            return redirect()->route('verify.award.show', ['code' => $code]);
        }

        return view('verify.award.form');
    }

    public function show(Request $request, string $code): View
    {
        $award = Award::query()
            ->with(['application.scholarship', 'application.student'])
            ->where('verification_code', strtoupper(trim($code)))
            ->first();

        return view('verify.award.result', [
            'code' => trim($code),
            'award' => $award,
        ]);
    }
}
