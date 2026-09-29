<?php

namespace App\Http\Controllers;

use App\Enums\EducationLevel;
use App\Enums\ScholarshipStatus;
use App\Models\Scholarship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ScholarshipController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q')),
            'level' => (string) $request->query('level'),
            'open' => $request->query('open') === '1',
            'sort' => $request->query('sort') === 'newest' ? 'newest' : 'deadline',
        ];

        $query = Scholarship::query()->where('status', '!=', ScholarshipStatus::DRAFT->value);

        if ($filters['q'] !== '') {
            $like = '%'.$filters['q'].'%';
            $query->where(function (Builder $builder) use ($like): void {
                $builder->where('title', 'like', $like)
                    ->orWhere('title_np', 'like', $like)
                    ->orWhere('provider', 'like', $like)
                    ->orWhere('description', 'like', $like);
            });
        }

        if (EducationLevel::tryFrom($filters['level']) !== null) {
            $query->where('education_level', $filters['level']);
        }

        if ($filters['open']) {
            $query->where('status', ScholarshipStatus::PUBLISHED->value)
                ->whereDate('application_start', '<=', today())
                ->whereDate('application_deadline', '>=', today());
        }

        if ($filters['sort'] === 'newest') {
            $query->orderByDesc('created_at');
        } else {
            $query->orderBy('application_deadline');
        }

        return view('scholarships.index', [
            'scholarships' => $query->paginate(9)->withQueryString(),
            'levels' => EducationLevel::cases(),
            'filters' => $filters,
        ]);
    }

    public function show(Scholarship $scholarship): View
    {
        abort_if($scholarship->status === ScholarshipStatus::DRAFT, 404);

        $scholarship->load(['criteria', 'eligibilityRules', 'requiredDocuments']);

        return view('scholarships.show', [
            'scholarship' => $scholarship,
            'canApply' => $scholarship->isAcceptingApplications(),
        ]);
    }
}
