<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolRequest;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SchoolController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();

        $schools = School::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('school_code', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.schools.index', [
            'schools' => $schools,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.schools.form', [
            'school' => new School(['status' => School::STATUS_ACTIVE]),
        ]);
    }

    public function store(SchoolRequest $request): RedirectResponse
    {
        School::query()->create($request->validated());

        return redirect()
            ->route('admin.schools.index')
            ->with('status', 'School created.');
    }

    public function edit(School $school): View
    {
        return view('admin.schools.form', [
            'school' => $school,
        ]);
    }

    public function update(SchoolRequest $request, School $school): RedirectResponse
    {
        $school->update($request->validated());

        return redirect()
            ->route('admin.schools.index')
            ->with('status', 'School updated.');
    }
}
