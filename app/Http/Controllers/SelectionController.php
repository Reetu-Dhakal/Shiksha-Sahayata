<?php

namespace App\Http\Controllers;

use App\Enums\Decision;
use App\Http\Requests\SelectionDecisionRequest;
use App\Models\Application;
use App\Services\SelectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SelectionController extends Controller
{
    public function __construct(private readonly SelectionService $selection) {}

    public function index(Request $request): View
    {
        return view('selection.index', [
            'applications' => $this->selection->queueFor($request->user())->paginate(10)->withQueryString(),
        ]);
    }

    public function show(Request $request, Application $application): View
    {
        $committee = $request->user();
        $this->selection->assertCanView($committee, $application);

        $application->load([
            'scholarship.criteria',
            'student.school',
            'scores.criterion',
            'decision.decidedBy',
            'verifications.officer',
            'documents',
        ]);

        return view('selection.show', [
            'application' => $application,
            'canScore' => $this->selection->canScore($committee, $application),
            'weightedTotal' => $application->weightedTotal(),
        ]);
    }

    public function scores(Request $request, Application $application): RedirectResponse
    {
        $data = $request->validate([
            'scores' => ['required', 'array'],
            'scores.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $this->selection->saveScores($request->user(), $application, $data['scores']);

        return redirect()
            ->route('selection.show', $application)
            ->with('status', 'Scores saved.');
    }

    public function decide(SelectionDecisionRequest $request, Application $application): RedirectResponse
    {
        $decision = Decision::from($request->validated('decision'));

        $this->selection->decide(
            $request->user(),
            $application,
            $decision,
            $request->validated('reason'),
        );

        return redirect()
            ->route('selection.show', $application)
            ->with('status', 'Decision recorded: '.$decision->label().'.');
    }
}
