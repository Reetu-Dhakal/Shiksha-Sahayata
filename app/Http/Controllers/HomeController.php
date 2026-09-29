<?php

namespace App\Http\Controllers;

use App\Enums\EducationLevel;
use App\Enums\Role;
use App\Enums\ScholarshipStatus;
use App\Models\Scholarship;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function index(Request $request): View
    {
        $filters = [
            'q' => trim((string) $request->query('q')),
            'level' => (string) $request->query('level'),
            'grade' => null,
        ];

        $grade = $request->query('grade');
        if ($grade !== null && $grade !== '' && ctype_digit((string) $grade)) {
            $filters['grade'] = (int) $grade;
        }

        $scholarships = $this->publishedQuery($filters)
            ->orderBy('application_deadline')
            ->limit(6)
            ->get();

        $published = $this->publishedQuery()->get();

        $deadlines = $published
            ->reject(fn (Scholarship $scholarship): bool => $scholarship->isExpired())
            ->values();

        $publications = $published
            ->sortByDesc('created_at')
            ->values();

        $openCount = Scholarship::query()
            ->where('status', ScholarshipStatus::PUBLISHED->value)
            ->whereDate('application_start', '<=', today())
            ->whereDate('application_deadline', '>=', today())
            ->count();

        [$applyUrl, $trackUrl] = $this->applicationUrls($request);

        return view('home', [
            'scholarships' => $scholarships,
            'levels' => EducationLevel::cases(),
            'filters' => $filters,
            'notices' => $this->notices($published),
            'deadlines' => $deadlines->take(6)->values(),
            'publications' => $publications->take(6)->values(),
            'openCount' => $openCount,
            'applyUrl' => $applyUrl,
            'trackUrl' => $trackUrl,
        ]);
    }

    /**
     * @param  array{q: string, level: string, grade: int|null}  $filters
     */
    private function publishedQuery(array $filters = ['q' => '', 'level' => '', 'grade' => null]): Builder
    {
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

        if ($filters['grade'] !== null) {
            $grade = $filters['grade'];
            $query->where(function (Builder $builder) use ($grade): void {
                $builder->whereNull('target_grade_min')->orWhere('target_grade_min', '<=', $grade);
            })->where(function (Builder $builder) use ($grade): void {
                $builder->whereNull('target_grade_max')->orWhere('target_grade_max', '>=', $grade);
            });
        }

        return $query;
    }

    /**
     * Notices derived from real scholarship publication, opening and deadline dates.
     *
     * @param  Collection<int, Scholarship>  $published
     * @return list<array{date: Carbon, type: string, label: string, url: string}>
     */
    private function notices($published): array
    {
        $events = [];

        foreach ($published as $scholarship) {
            $url = route('scholarships.show', $scholarship);

            $events[] = [
                'date' => $scholarship->created_at,
                'type' => __('home.notice_types.publication'),
                'label' => __('home.notice_labels.published', ['title' => $scholarship->title]),
                'url' => $url,
            ];

            if ($scholarship->application_start->lte(now())) {
                $events[] = [
                    'date' => $scholarship->application_start,
                    'type' => __('home.notice_types.application'),
                    'label' => __('home.notice_labels.opened', ['title' => $scholarship->title]),
                    'url' => $url,
                ];
            }

            $events[] = [
                'date' => $scholarship->application_deadline,
                'type' => __('home.notice_types.deadline'),
                'label' => __('home.notice_labels.deadline', ['title' => $scholarship->title]),
                'url' => $url,
            ];
        }

        usort($events, fn (array $a, array $b): int => $b['date']->getTimestamp() <=> $a['date']->getTimestamp());

        return array_slice($events, 0, 5);
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function applicationUrls(Request $request): array
    {
        $user = $request->user();

        if ($user === null) {
            return [route('login'), route('login')];
        }

        if ($user->hasRole(Role::COMMITTEE)) {
            return [route('dashboard'), route('dashboard')];
        }

        return [route('applications.create'), route('applications.index')];
    }
}
