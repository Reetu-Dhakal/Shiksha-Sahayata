<?php

namespace App\Http\Controllers;

use App\Enums\Role;
use App\Models\Award;
use App\Models\Student;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class AwardController extends Controller
{
    public function index(Request $request): View
    {
        $studentIds = $this->studentIdsFor($request->user());

        $awards = Award::query()
            ->with(['application.scholarship', 'application.student'])
            ->whereHas('application', fn ($query) => $query->whereIn('student_id', $studentIds))
            ->orderByDesc('issued_at')
            ->get();

        return view('awards.index', [
            'awards' => $awards,
        ]);
    }

    public function letter(Request $request, Award $award): SymfonyResponse
    {
        abort_unless($this->owns($request, $award), 403);

        $verificationUrl = route('verify.award.show', $award->verification_code);
        $qrDataUri = (new PngWriter)->write(new QrCode($verificationUrl))->getDataUri();

        $pdf = Pdf::loadView('awards.letter', [
            'award' => $award,
            'application' => $award->application,
            'qrDataUri' => $qrDataUri,
            'verificationUrl' => $verificationUrl,
        ])->setPaper('a4');

        return $pdf->download(sprintf('award-letter-%s.pdf', $award->award_number));
    }

    private function owns(Request $request, Award $award): bool
    {
        $user = $request->user();

        if ($user->isAdmin()) {
            return true;
        }

        return in_array($award->application->student_id, $this->studentIdsFor($user), true);
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

        if ($user->isAdmin()) {
            return Student::query()->pluck('id')->all();
        }

        return [];
    }
}
