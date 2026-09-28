@extends('layouts.app')

@section('title', __('nav.awards'))

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">Award Management</h1>
    <p class="mt-1 text-sm text-slate-600">
        Issue awards for selected applications, track disbursement, and revoke awards when required.
    </p>
</div>

<section class="mb-8">
    <h2 class="mb-3 text-sm font-semibold text-slate-900">Selected applications awaiting an award</h2>

    @if ($selectedApplications->isEmpty())
        <x-empty-state title="Nothing to issue" message="Applications appear here once the selection committee records a SELECTED decision." />
    @else
        <div class="overflow-x-auto rounded border border-slate-200 bg-white">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="px-4 py-3">Student</th>
                        <th class="px-4 py-3">Scholarship</th>
                        <th class="px-4 py-3">Decision</th>
                        <th class="px-4 py-3">{{ __('common.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach ($selectedApplications as $application)
                        <tr>
                            <td class="px-4 py-3">
                                <p class="font-medium text-slate-800">{{ $application->student->name }}</p>
                                <p class="text-xs text-slate-500">{{ $application->student->scholar_student_id }}</p>
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $application->scholarship->title }}</td>
                            <td class="px-4 py-3">
                                <x-status-badge type="success" :label="$application->decision?->decision?->label() ?? __('status.application.SELECTED')" />
                            </td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.awards.issue', $application) }}">
                                    @csrf
                                    <button type="submit" class="rounded bg-blue-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-blue-900">
                                        Issue award
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

<section>
    <h2 class="mb-3 text-sm font-semibold text-slate-900">Issued awards</h2>

    @if ($awards->isEmpty())
        <x-empty-state title="No awards issued" message="Issued awards and their disbursement progress will be listed here." />
    @else
        <div class="space-y-4">
            @foreach ($awards as $award)
                @php($application = $award->application)
                <article class="rounded border border-slate-200 bg-white p-4">
                    <div class="flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <p class="text-xs text-slate-500">{{ $application->scholarship->title }}</p>
                            <h3 class="mt-1 text-sm font-semibold text-slate-900">
                                {{ $award->award_number }} · {{ $application->student->name }}
                            </h3>
                            <p class="mt-1 text-xs text-slate-600">
                                Issued {{ $award->issued_at?->format('j M Y') ?: '—' }}
                                · Code <span class="font-mono">{{ $award->verification_code }}</span>
                            </p>
                        </div>
                        <div class="flex flex-col items-end gap-2">
                            <x-status-badge :type="$award->status->badgeType()" :label="$award->status->label()" />
                            <x-status-badge :type="$award->disbursement_status->badgeType()" :label="$award->disbursement_status->label()" />
                        </div>
                    </div>

                    @if ($award->isActive())
                        @php($next = collect(\App\Enums\DisbursementStatus::flow())
                            ->slice(collect(\App\Enums\DisbursementStatus::flow())->search($award->disbursement_status) + 1)
                            ->first())

                        @if ($next)
                            <form method="POST" action="{{ route('admin.awards.disbursement', $award) }}"
                                  class="mt-3 flex flex-wrap items-end gap-2 border-t border-slate-100 pt-3">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="disbursement_status" value="{{ $next->value }}">
                                <div class="flex-1 min-w-56">
                                    <label for="remarks-{{ $award->id }}" class="sr-only">Remarks</label>
                                    <input id="remarks-{{ $award->id }}" name="remarks" type="text" maxlength="1000"
                                           placeholder="Remarks (optional)"
                                           class="w-full rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                                </div>
                                <button type="submit" class="rounded border border-blue-800 px-3 py-1.5 text-xs font-medium text-blue-800 hover:bg-blue-50">
                                    Mark as {{ $next->label() }}
                                </button>
                            </form>
                        @else
                            <p class="mt-3 border-t border-slate-100 pt-3 text-xs font-medium text-green-700">
                                Disbursement confirmed and recorded on the application.
                            </p>
                        @endif

                        <form method="POST" action="{{ route('admin.awards.revoke', $award) }}"
                              class="mt-2 flex flex-wrap items-end gap-2">
                            @csrf
                            @method('PATCH')
                            <div class="flex-1 min-w-56">
                                <label for="reason-{{ $award->id }}" class="sr-only">Revocation reason</label>
                                <input id="reason-{{ $award->id }}" name="reason" type="text" required minlength="10" maxlength="1000"
                                       placeholder="Reason for revocation (required)"
                                       class="w-full rounded border border-slate-300 px-3 py-1.5 text-xs focus:border-red-500 focus:outline-none focus:ring-1 focus:ring-red-500">
                            </div>
                            <button type="submit" class="rounded border border-red-600 px-3 py-1.5 text-xs font-medium text-red-600 hover:bg-red-50">
                                Revoke award
                            </button>
                        </form>
                    @elseif ($award->disbursement_remarks)
                        <p class="mt-3 rounded bg-slate-50 p-2 text-xs text-slate-600">
                            Revocation reason: {{ $award->disbursement_remarks }}
                        </p>
                    @endif

                    <div class="mt-3 border-t border-slate-100 pt-3">
                        <a href="{{ route('applications.show', $application) }}" class="text-xs font-medium text-blue-800 hover:underline">
                            Open application
                        </a>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $awards->links() }}
        </div>
    @endif
</section>
@endsection
