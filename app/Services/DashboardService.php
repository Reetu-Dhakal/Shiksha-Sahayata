<?php

namespace App\Services;

use App\Enums\ApplicationStatus;
use App\Enums\Decision;
use App\Enums\Role;
use App\Enums\VerificationStage;
use App\Models\Appeal;
use App\Models\Application;
use App\Models\AuditLog;
use App\Models\Award;
use App\Models\Notification;
use App\Models\Scholarship;
use App\Models\SelectionDecision;
use App\Models\Student;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Support\Collection;

class DashboardService
{
    public function __construct(
        private readonly VerificationService $verifications,
        private readonly SelectionService $selections,
    ) {}

    /**
     * @return array{
     *     stats: list<array{label: string, value: int|string, href: ?string}>,
     *     byStatus: Collection<string, int>,
     *     rows: Collection<int, mixed>,
     *     rowType: ?string,
     *     recent: Collection<int, mixed>,
     *     recentType: ?string,
     * }
     */
    public function forUser(User $user): array
    {
        return match (true) {
            $user->isAdmin() => $this->admin(),
            $user->hasRole(Role::SCHOOL_OFFICER) => $this->school($user),
            $user->hasRole(Role::LOCAL_OFFICER) => $this->local($user),
            $user->hasRole(Role::COMMITTEE) => $this->committee($user),
            default => $this->applicant($user),
        };
    }

    /**
     * @return array{stats: list<array{label: string, value: int|string, href: ?string}>, byStatus: Collection<string, int>, rows: Collection<int, mixed>, rowType: ?string, recent: Collection<int, mixed>, recentType: ?string}
     */
    private function admin(): array
    {
        $byStatus = Application::query()
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->orderByDesc('total')
            ->pluck('total', 'status')
            ->mapWithKeys(fn ($count, $status): array => [
                ApplicationStatus::from((string) $status)->label() => (int) $count,
            ]);

        return [
            'stats' => [
                ['label' => __('dashboard.stats.published_scholarships'), 'value' => Scholarship::query()->where('status', 'PUBLISHED')->count(), 'href' => route('admin.scholarships.index')],
                ['label' => __('dashboard.stats.applications_received'), 'value' => Application::query()->count(), 'href' => null],
                ['label' => __('dashboard.stats.awaiting_verification'), 'value' => Application::query()->whereIn('status', [
                    ApplicationStatus::SUBMITTED->value,
                    ApplicationStatus::SCHOOL_VERIFICATION->value,
                    ApplicationStatus::LOCAL_VERIFICATION->value,
                ])->count(), 'href' => null],
                ['label' => __('dashboard.stats.under_selection_review'), 'value' => Application::query()->where('status', ApplicationStatus::UNDER_REVIEW->value)->count(), 'href' => null],
                ['label' => __('dashboard.stats.open_appeals'), 'value' => Appeal::query()->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW'])->count(), 'href' => route('appeals.index')],
                ['label' => __('dashboard.stats.awards_issued'), 'value' => Award::query()->count(), 'href' => route('admin.awards.index')],
                ['label' => __('dashboard.stats.disbursements_confirmed'), 'value' => Application::query()->where('status', ApplicationStatus::DISBURSEMENT_CONFIRMED->value)->count(), 'href' => route('admin.awards.index')],
                ['label' => __('dashboard.stats.registered_users'), 'value' => User::query()->count(), 'href' => null],
            ],
            'byStatus' => $byStatus,
            'rows' => collect(),
            'rowType' => null,
            'recent' => AuditLog::query()->with('actor')->orderByDesc('created_at')->orderByDesc('id')->limit(6)->get(),
            'recentType' => 'audit',
        ];
    }

    /**
     * @return array{stats: list<array{label: string, value: int|string, href: ?string}>, byStatus: Collection<string, int>, rows: Collection<int, mixed>, rowType: ?string, recent: Collection<int, mixed>, recentType: ?string}
     */
    private function school(User $officer): array
    {
        $queue = $this->verifications->queueFor($officer);
        $pending = (clone $queue)->count();

        $verified = Verification::query()
            ->where('stage', VerificationStage::SCHOOL->value)
            ->where('status', 'VERIFIED')
            ->whereHas('application', fn ($query) => $query->whereHas('student', fn ($student) => $student
                ->where('school_id', $officer->school_id)))
            ->count();

        $returned = Application::query()
            ->where('status', ApplicationStatus::RETURNED_FOR_CORRECTION->value)
            ->whereHas('student', fn ($student) => $student->where('school_id', $officer->school_id))
            ->count();

        $assisted = Application::query()
            ->where('is_assisted', true)
            ->whereHas('student', fn ($student) => $student->where('school_id', $officer->school_id))
            ->count();

        return [
            'stats' => [
                ['label' => __('dashboard.stats.pending_verifications'), 'value' => $pending, 'href' => route('verifications.index')],
                ['label' => __('dashboard.stats.verifications_completed'), 'value' => $verified, 'href' => route('verifications.index')],
                ['label' => __('dashboard.stats.returned_for_correction'), 'value' => $returned, 'href' => route('verifications.index')],
                ['label' => __('dashboard.stats.assisted_applications'), 'value' => $assisted, 'href' => route('applications.index')],
                ['label' => __('dashboard.stats.students_at_school'), 'value' => Student::query()->where('school_id', $officer->school_id)->count(), 'href' => null],
            ],
            'byStatus' => collect(),
            'rows' => (clone $queue)->limit(5)->get(),
            'rowType' => 'verification',
            'recent' => collect(),
            'recentType' => null,
        ];
    }

    /**
     * @return array{stats: list<array{label: string, value: int|string, href: ?string}>, byStatus: Collection<string, int>, rows: Collection<int, mixed>, rowType: ?string, recent: Collection<int, mixed>, recentType: ?string}
     */
    private function local(User $officer): array
    {
        $queue = $this->verifications->queueFor($officer);
        $pending = (clone $queue)->count();
        $unit = $officer->localEducationUnit;

        $verified = Verification::query()
            ->where('stage', VerificationStage::LOCAL->value)
            ->where('status', 'VERIFIED')
            ->whereHas('application', fn ($query) => $query->whereHas('student', fn ($student) => $student
                ->where('district', $unit?->district)
                ->where('municipality', $unit?->municipality)))
            ->count();

        $returned = Application::query()
            ->where('status', ApplicationStatus::RETURNED_FOR_CORRECTION->value)
            ->whereHas('student', fn ($student) => $student
                ->where('district', $unit?->district)
                ->where('municipality', $unit?->municipality))
            ->count();

        return [
            'stats' => [
                ['label' => __('dashboard.stats.pending_verifications'), 'value' => $pending, 'href' => route('verifications.index')],
                ['label' => __('dashboard.stats.verifications_completed'), 'value' => $verified, 'href' => route('verifications.index')],
                ['label' => __('dashboard.stats.returned_for_correction'), 'value' => $returned, 'href' => route('verifications.index')],
                ['label' => __('dashboard.stats.students_in_jurisdiction'), 'value' => Student::query()
                    ->where('district', $unit?->district)
                    ->where('municipality', $unit?->municipality)
                    ->count(), 'href' => null],
            ],
            'byStatus' => collect(),
            'rows' => (clone $queue)->limit(5)->get(),
            'rowType' => 'verification',
            'recent' => collect(),
            'recentType' => null,
        ];
    }

    /**
     * @return array{stats: list<array{label: string, value: int|string, href: ?string}>, byStatus: Collection<string, int>, rows: Collection<int, mixed>, rowType: ?string, recent: Collection<int, mixed>, recentType: ?string}
     */
    private function committee(User $member): array
    {
        $assignedIds = Scholarship::query()
            ->whereHas('committeeMembers', fn ($query) => $query->where('user_id', $member->id))
            ->pluck('id');

        $underReview = Application::query()
            ->where('status', ApplicationStatus::UNDER_REVIEW->value)
            ->whereIn('scholarship_id', $assignedIds)
            ->count();

        $decisionsByOutcome = SelectionDecision::query()
            ->whereIn('application_id', Application::query()->select('id')->whereIn('scholarship_id', $assignedIds))
            ->selectRaw('decision, COUNT(*) as total')
            ->groupBy('decision')
            ->pluck('total', 'decision')
            ->mapWithKeys(fn ($count, $decision): array => [
                Decision::from((string) $decision)->label() => (int) $count,
            ]);

        $openAppeals = Appeal::query()
            ->whereIn('status', ['SUBMITTED', 'UNDER_REVIEW'])
            ->whereHas('application', fn ($query) => $query->whereIn('scholarship_id', $assignedIds))
            ->count();

        $queue = $this->selections->queueFor($member)
            ->where('status', ApplicationStatus::UNDER_REVIEW->value);

        return [
            'stats' => [
                ['label' => __('dashboard.stats.assigned_scholarships'), 'value' => $assignedIds->count(), 'href' => null],
                ['label' => __('dashboard.stats.ready_for_review'), 'value' => $underReview, 'href' => route('selection.index')],
                ['label' => __('dashboard.stats.decisions_recorded'), 'value' => $decisionsByOutcome->sum(), 'href' => route('selection.index')],
                ['label' => __('dashboard.stats.open_appeals'), 'value' => $openAppeals, 'href' => route('appeals.index')],
            ],
            'byStatus' => $decisionsByOutcome,
            'rows' => (clone $queue)->limit(5)->get(),
            'rowType' => 'selection',
            'recent' => collect(),
            'recentType' => null,
        ];
    }

    /**
     * @return array{stats: list<array{label: string, value: int|string, href: ?string}>, byStatus: Collection<string, int>, rows: Collection<int, mixed>, rowType: ?string, recent: Collection<int, mixed>, recentType: ?string}
     */
    private function applicant(User $user): array
    {
        $studentIds = $this->studentIdsFor($user);

        $byStatus = Application::query()
            ->whereIn('student_id', $studentIds)
            ->selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status')
            ->mapWithKeys(fn ($count, $status): array => [
                ApplicationStatus::from((string) $status)->label() => (int) $count,
            ]);

        $unread = Notification::query()
            ->where('user_id', $user->id)
            ->whereNull('read_at')
            ->count();

        return [
            'stats' => [
                ['label' => __('dashboard.stats.applications'), 'value' => Application::query()->whereIn('student_id', $studentIds)->count(), 'href' => route('applications.index')],
                ['label' => __('dashboard.stats.unread_notifications'), 'value' => $unread, 'href' => route('notifications.index')],
                ['label' => __('dashboard.stats.awards'), 'value' => Award::query()->whereHas('application', fn ($query) => $query->whereIn('student_id', $studentIds))->count(), 'href' => route('awards.index')],
            ],
            'byStatus' => $byStatus,
            'rows' => Application::query()
                ->with('scholarship')
                ->whereIn('student_id', $studentIds)
                ->orderByDesc('created_at')
                ->limit(5)
                ->get(),
            'rowType' => 'application',
            'recent' => Notification::query()
                ->where('user_id', $user->id)
                ->orderByDesc('created_at')
                ->orderByDesc('id')
                ->limit(5)
                ->get(),
            'recentType' => 'notification',
        ];
    }

    /**
     * @return list<int>
     */
    private function studentIdsFor(User $user): array
    {
        if ($user->hasRole(Role::STUDENT)) {
            return $user->student ? [$user->student->id] : [];
        }

        if ($user->hasRole(Role::GUARDIAN)) {
            return Student::query()->where('guardian_id', $user->guardian?->id)->pluck('id')->all();
        }

        return [];
    }
}
