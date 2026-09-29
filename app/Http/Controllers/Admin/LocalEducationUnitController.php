<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\LocalEducationUnitRequest;
use App\Models\LocalEducationUnit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LocalEducationUnitController extends Controller
{
    public function index(Request $request): View
    {
        $search = $request->string('q')->toString();

        $units = LocalEducationUnit::query()
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('district', 'like', "%{$search}%")
                        ->orWhere('municipality', 'like', "%{$search}%");
                });
            })
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.local-education-units.index', [
            'units' => $units,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('admin.local-education-units.form', [
            'unit' => new LocalEducationUnit(['status' => LocalEducationUnit::STATUS_ACTIVE]),
        ]);
    }

    public function store(LocalEducationUnitRequest $request): RedirectResponse
    {
        LocalEducationUnit::query()->create($request->validated());

        return redirect()
            ->route('admin.local-education-units.index')
            ->with('status', __('admin.flash.unit_created'));
    }

    public function edit(LocalEducationUnit $unit): View
    {
        return view('admin.local-education-units.form', [
            'unit' => $unit,
        ]);
    }

    public function update(LocalEducationUnitRequest $request, LocalEducationUnit $unit): RedirectResponse
    {
        $unit->update($request->validated());

        return redirect()
            ->route('admin.local-education-units.index')
            ->with('status', __('admin.flash.unit_updated'));
    }
}
