<?php

namespace App\Http\Controllers;

use App\Http\Requests\VerificationDecisionRequest;
use App\Models\Application;
use App\Services\VerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VerificationController extends Controller
{
    public function __construct(private readonly VerificationService $verifications) {}

    public function index(Request $request): View
    {
        $officer = $request->user();
        $stage = $this->verifications->stageFor($officer);

        return view('verifications.index', [
            'stage' => $stage,
            'applications' => $this->verifications->queueFor($officer)->paginate(10)->withQueryString(),
        ]);
    }

    public function show(Request $request, Application $application): View
    {
        $officer = $request->user();
        $stage = $this->verifications->stageFor($officer);

        abort_unless($this->verifications->inJurisdiction($officer, $application), 403);

        $application->load([
            'scholarship.requiredDocuments',
            'student.school',
            'documents',
            'verifications',
        ]);

        return view('verifications.show', [
            'stage' => $stage,
            'application' => $application,
            'canDecide' => $this->verifications->canReview($officer, $application),
            'verification' => $application->verificationFor($stage),
        ]);
    }

    public function start(Request $request, Application $application): RedirectResponse
    {
        $this->verifications->start($request->user(), $application);

        return redirect()
            ->route('verifications.show', $application)
            ->with('status', 'Application taken up for school verification.');
    }

    public function approve(VerificationDecisionRequest $request, Application $application): RedirectResponse
    {
        $this->verifications->approve(
            $request->user(),
            $application,
            $request->validated('remarks'),
        );

        return redirect()
            ->route('verifications.show', $application)
            ->with('status', 'Verification recorded. The application moves to the next stage.');
    }

    public function returnForCorrection(VerificationDecisionRequest $request, Application $application): RedirectResponse
    {
        $this->verifications->returnForCorrection(
            $request->user(),
            $application,
            $request->validated('remarks'),
        );

        return redirect()
            ->route('verifications.show', $application)
            ->with('status', 'Application returned to the applicant for correction.');
    }
}
