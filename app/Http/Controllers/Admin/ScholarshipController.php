<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ScholarshipStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\ScholarshipRequest;
use App\Models\Scholarship;
use App\Models\User;
use App\Services\ScholarshipService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScholarshipController extends Controller
{
    public function __construct(private readonly ScholarshipService $scholarshipService) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $search = $request->string('q')->toString();

        $scholarships = Scholarship::query()
            ->when($status !== '' && ScholarshipStatus::tryFrom($status) !== null, fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                        ->orWhere('provider', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('application_deadline')
            ->paginate(15)
            ->withQueryString();

        return view('admin.scholarships.index', [
            'scholarships' => $scholarships,
            'status' => $status,
            'search' => $search,
            'statuses' => ScholarshipStatus::cases(),
        ]);
    }

    public function create(): View
    {
        $scholarship = new Scholarship([
            'status' => ScholarshipStatus::DRAFT,
            'application_start' => now()->toDateString(),
            'application_deadline' => now()->addDays(30)->toDateString(),
        ]);
        $scholarship->setRelation('criteria', collect());
        $scholarship->setRelation('eligibilityRules', collect());
        $scholarship->setRelation('requiredDocuments', collect());
        $scholarship->setRelation('committeeMembers', collect());

        return view('admin.scholarships.form', [
            'scholarship' => $scholarship,
            'committeeMembers' => $this->committeeMembers(),
        ]);
    }

    public function store(ScholarshipRequest $request): RedirectResponse
    {
        $scholarship = $this->scholarshipService->create($request->user(), $request->validated());

        return redirect()
            ->route('admin.scholarships.edit', $scholarship)
            ->with('status', 'Scholarship created. Define eligibility rules, selection criteria, required documents and committee members before publishing.');
    }

    public function edit(Scholarship $scholarship): View
    {
        $scholarship->load(['criteria', 'eligibilityRules', 'requiredDocuments', 'committeeMembers']);

        return view('admin.scholarships.form', [
            'scholarship' => $scholarship,
            'committeeMembers' => $this->committeeMembers(),
        ]);
    }

    public function update(ScholarshipRequest $request, Scholarship $scholarship): RedirectResponse
    {
        $this->scholarshipService->update($scholarship, $request->validated());

        return redirect()
            ->route('admin.scholarships.edit', $scholarship)
            ->with('status', 'Scholarship updated.');
    }

    public function updateStatus(Request $request, Scholarship $scholarship): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', 'in:PUBLISHED,CLOSED,COMPLETED'],
        ]);

        $this->scholarshipService->changeStatus($scholarship, ScholarshipStatus::from($data['status']));

        return back()->with('status', 'Scholarship status changed to '.ScholarshipStatus::from($data['status'])->label().'.');
    }

    /**
     * @return Collection<int, User>
     */
    private function committeeMembers()
    {
        return User::query()->where('role', 'committee')->orderBy('name')->get();
    }
}
