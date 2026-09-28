<?php

namespace App\Http\Controllers;

use App\Enums\AppealStatus;
use App\Http\Requests\AppealRequest;
use App\Http\Requests\AppealReviewRequest;
use App\Models\Appeal;
use App\Models\Application;
use App\Services\AppealService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppealController extends Controller
{
    public function __construct(private readonly AppealService $appeals) {}

    public function store(AppealRequest $request, Application $application): RedirectResponse
    {
        $this->appeals->submit($request->user(), $application, $request->validated('reason'));

        return redirect()
            ->route('applications.show', $application)
            ->with('status', 'Your appeal has been submitted and is waiting for review.');
    }

    public function index(Request $request): View
    {
        return view('appeals.index', [
            'appeals' => $this->appeals->queueFor($request->user())->paginate(10)->withQueryString(),
        ]);
    }

    public function show(Request $request, Appeal $appeal): View
    {
        $reviewer = $request->user();
        $this->appeals->assertCanReview($reviewer, $appeal);

        $appeal->load([
            'application.scholarship.criteria',
            'application.student.school',
            'application.decision',
            'application.scores.criterion',
            'appellant',
            'reviewer',
        ]);

        return view('appeals.show', [
            'appeal' => $appeal,
            'canReview' => $appeal->status === AppealStatus::SUBMITTED || $appeal->status === AppealStatus::UNDER_REVIEW,
        ]);
    }

    public function reopen(Request $request, Appeal $appeal): RedirectResponse
    {
        $this->appeals->reopen($request->user(), $appeal);

        return redirect()
            ->route('appeals.show', $appeal)
            ->with('status', 'Appeal reopened. The application is back under selection review.');
    }

    public function decide(AppealReviewRequest $request, Appeal $appeal): RedirectResponse
    {
        $this->appeals->decide(
            $request->user(),
            $appeal,
            $request->validated('outcome') === 'APPROVED',
            $request->validated('remarks'),
        );

        return redirect()
            ->route('appeals.show', $appeal)
            ->with('status', 'Appeal decision recorded.');
    }
}
