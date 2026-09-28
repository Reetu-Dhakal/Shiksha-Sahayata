<?php

namespace App\Http\Controllers\Admin;

use App\Enums\ApplicationStatus;
use App\Enums\Decision;
use App\Enums\DisbursementStatus;
use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Award;
use App\Models\Scholarship;
use App\Models\SelectionDecision;
use App\Models\Student;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReportController extends Controller
{
    public function index(): View
    {
        return view('admin.reports.index', [
            'totals' => [
                ['label' => 'Students', 'value' => Student::query()->count()],
                ['label' => 'Applications', 'value' => Application::query()->count()],
                ['label' => 'Published scholarships', 'value' => Scholarship::query()->where('status', 'PUBLISHED')->count()],
                ['label' => 'Awards issued', 'value' => Award::query()->count()],
                ['label' => 'Users', 'value' => User::query()->count()],
            ],
            'byStatus' => $this->countBy(Application::query(), 'status')
                ->mapWithKeys(fn (int $count, string $status): array => [
                    ApplicationStatus::from($status)->label() => $count,
                ]),
            'byScholarship' => $this->countBy(
                Application::query()->join('scholarships', 'scholarships.id', '=', 'applications.scholarship_id'),
                'scholarships.title',
            ),
            'byDistrict' => $this->countBy(
                Application::query()->join('students', 'students.id', '=', 'applications.student_id'),
                'students.district',
            ),
            'verificationOutcomes' => $this->countBy(Verification::query(), 'status'),
            'decisions' => $this->countBy(SelectionDecision::query(), 'decision')
                ->mapWithKeys(fn (int $count, string $decision): array => [
                    Decision::from($decision)->label() => $count,
                ]),
            'disbursement' => $this->countBy(Award::query(), 'disbursement_status')
                ->mapWithKeys(fn (int $count, string $status): array => [
                    DisbursementStatus::from($status)->label() => $count,
                ]),
        ]);
    }

    public function applicationsCsv(): StreamedResponse
    {
        $filename = sprintf('shiksha-sahayata-applications-%s.csv', now()->format('Ymd-His'));

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');

            fputcsv($handle, [
                'application_id',
                'scholarship',
                'student_id',
                'student_name',
                'school',
                'district',
                'municipality',
                'status',
                'submitted_at',
                'assisted',
                'award_number',
            ]);

            Application::query()
                ->with(['scholarship', 'student.school', 'award'])
                ->orderBy('id')
                ->chunk(200, function ($applications) use ($handle): void {
                    foreach ($applications as $application) {
                        fputcsv($handle, [
                            $application->id,
                            $application->scholarship->title,
                            $application->student->scholar_student_id,
                            $application->student->name,
                            $application->student->school?->name,
                            $application->student->district,
                            $application->student->municipality,
                            $application->status->value,
                            $application->submitted_at?->format('Y-m-d H:i'),
                            $application->is_assisted ? 'yes' : 'no',
                            $application->award?->award_number,
                        ]);
                    }
                });

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv',
        ]);
    }

    /**
     * @param  \Illuminate\Database\Eloquent\Builder<*>  $query
     * @return Collection<string, int>
     */
    private function countBy($query, string $column): Collection
    {
        return $query
            ->selectRaw($column.' as key_value, COUNT(*) as total')
            ->groupBy($column)
            ->orderByDesc('total')
            ->pluck('total', 'key_value')
            ->map(fn ($count): int => (int) $count);
    }
}
