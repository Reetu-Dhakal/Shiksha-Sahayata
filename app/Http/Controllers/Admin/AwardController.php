<?php

namespace App\Http\Controllers\Admin;

use App\Enums\DisbursementStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\DisbursementRequest;
use App\Models\Application;
use App\Models\Award;
use App\Services\AwardService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AwardController extends Controller
{
    public function __construct(private readonly AwardService $awards) {}

    public function index(): View
    {
        return view('admin.awards.index', [
            'awards' => Award::query()
                ->with(['application.scholarship', 'application.student'])
                ->orderByDesc('issued_at')
                ->paginate(10),
            'selectedApplications' => Application::query()
                ->with(['scholarship', 'student', 'decision'])
                ->where('status', 'SELECTED')
                ->whereDoesntHave('award')
                ->orderBy('submitted_at')
                ->get(),
        ]);
    }

    public function issue(Request $request, Application $application): RedirectResponse
    {
        $this->awards->issue($application, $request->user());

        return back()->with('status', 'Award issued. The applicant can now download the award letter.');
    }

    public function updateDisbursement(DisbursementRequest $request, Award $award): RedirectResponse
    {
        $this->awards->advanceDisbursement(
            $award,
            DisbursementStatus::from($request->validated('disbursement_status')),
            $request->validated('remarks'),
        );

        return back()->with('status', 'Disbursement status updated.');
    }

    public function revoke(Request $request, Award $award): RedirectResponse
    {
        $data = $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        $this->awards->revoke($award, $request->user(), $data['reason']);

        return back()->with('status', 'Award revoked.');
    }
}
