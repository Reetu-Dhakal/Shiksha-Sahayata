@extends('layouts.app')

@section('title', 'Selection review')

@php
    $scoreMap = [];
    $maxMap = [];
    $weightMap = [];
    foreach ($application->scholarship->criteria as $criterion) {
        $saved = $application->scores->firstWhere('criterion_id', $criterion->id);
        $scoreMap[$criterion->id] = (float) ($saved?->score ?? 0);
        $maxMap[$criterion->id] = (float) $criterion->maximum_score;
        $weightMap[$criterion->id] = (float) $criterion->weight;
    }
@endphp

@section('content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <nav class="text-xs text-slate-500">
            <a href="{{ route('selection.index') }}" class="hover:text-blue-800">{{ __('nav.selection') }}</a>
            <span class="mx-1">/</span>
            <span>{{ $application->student->name }}</span>
        </nav>
        <h1 class="mt-2 text-lg font-semibold text-slate-900">{{ $application->scholarship->title }}</h1>
        <p class="mt-1 text-sm text-slate-600">
            {{ $application->student->name }} · {{ $application->student->scholar_student_id }}
            · {{ $application->student->school?->name ?? 'No school' }}
        </p>
        <div class="mt-2 flex flex-wrap gap-2">
            <x-status-badge :type="$application->status->badgeType()" :label="$application->status->label()" />
            @if ($application->decision)
                <x-status-badge :type="$application->decision->decision->badgeType()" :label="$application->decision->decision->label()" />
            @endif
        </div>
    </div>

    <div class="text-right">
        <p class="text-xs uppercase tracking-wide text-slate-500">Weighted score</p>
        <p class="text-xl font-semibold text-slate-900">{{ number_format($weightedTotal, 2) }}%</p>
        <p class="text-xs text-slate-500">of {{ rtrim(rtrim(number_format($application->scholarship->totalWeight(), 2), '0'), '.') }}% available</p>
    </div>
</div>

@if ($application->decision)
    <div class="mb-6 rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Recorded decision: {{ $application->decision->decision->label() }}</h2>
        <p class="mt-1 text-sm text-slate-700">{{ $application->decision->reason }}</p>
        <p class="mt-1 text-xs text-slate-500">
            {{ $application->decision->decidedBy?->name ?: 'Committee' }}
            @if ($application->decision->decided_at) · {{ $application->decision->decided_at->format('j M Y, H:i') }} @endif
        </p>
    </div>
@endif

<div class="grid gap-6 lg:grid-cols-3">
    <section class="rounded border border-slate-200 bg-white p-4 lg:col-span-2"
             x-data="{
                 scores: @js($scoreMap),
                 maxima: @js($maxMap),
                 weights: @js($weightMap),
                 total() {
                     return Object.keys(this.scores).reduce((sum, id) => {
                         const score = Number(this.scores[id]) || 0;
                         const max = this.maxima[id] || 1;
                         return sum + (score / max) * this.weights[id];
                     }, 0);
                 },
                 contribution(id) {
                     const score = Number(this.scores[id]) || 0;
                     const max = this.maxima[id] || 1;
                     return (score / max) * this.weights[id];
                 },
             }">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h2 class="text-sm font-semibold text-slate-900">Criteria scoring</h2>
            <span class="text-sm text-slate-600">Weighted total: <strong x-text="total().toFixed(2)">0.00</strong>%</span>
        </div>

        @if ($application->scholarship->criteria->isEmpty())
            <p class="mt-3 text-sm text-slate-500">This scholarship has no scoring criteria configured.</p>
        @elseif ($canScore)
            <form method="POST" action="{{ route('selection.scores', $application) }}" class="mt-4">
                @csrf
                @method('PATCH')

                <div class="space-y-3">
                    @foreach ($application->scholarship->criteria as $criterion)
                        <div class="grid gap-3 rounded border border-slate-200 p-3 sm:grid-cols-12">
                            <div class="sm:col-span-5">
                                <p class="text-sm font-medium text-slate-900">{{ $criterion->name }}</p>
                                <p class="text-xs text-slate-500">{{ $criterion->description }}</p>
                            </div>
                            <div class="sm:col-span-3">
                                <label class="mb-1 block text-xs font-medium text-slate-600">Score (max {{ $criterion->maximum_score }})</label>
                                <input type="number" step="0.5" min="0" max="{{ $criterion->maximum_score }}"
                                       name="scores[{{ $criterion->id }}]" x-model="scores[{{ $criterion->id }}]"
                                       class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                                @error('scores.'.$criterion->id)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <div class="sm:col-span-2">
                                <p class="mb-1 text-xs font-medium text-slate-600">Weight</p>
                                <p class="text-sm text-slate-800">{{ rtrim(rtrim(number_format((float) $criterion->weight, 2), '0'), '.') }}%</p>
                            </div>
                            <div class="sm:col-span-2">
                                <p class="mb-1 text-xs font-medium text-slate-600">Contribution</p>
                                <p class="text-sm font-medium text-slate-900" x-text="contribution({{ $criterion->id }}).toFixed(2)+'%'">0.00%</p>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-4 flex items-center justify-between gap-3">
                    <p class="text-xs text-slate-500">Weighted contribution = (score ÷ maximum score) × weight.</p>
                    <button type="submit" class="rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900">
                        Save scores
                    </button>
                </div>
            </form>

            <form method="POST" action="{{ route('selection.decision', $application) }}" class="mt-6 border-t border-slate-100 pt-4">
                @csrf
                @method('PATCH')

                <h3 class="text-sm font-semibold text-slate-900">Record decision</h3>
                <p class="mt-1 text-xs text-slate-500">Decisions are final for this round. The reason is visible to the applicant.</p>

                <div class="mt-3 grid gap-3 sm:grid-cols-3">
                    @foreach (['SELECTED' => 'Selected', 'WAITLISTED' => 'Waitlisted', 'REJECTED' => 'Rejected'] as $value => $label)
                        <label class="flex items-center gap-2 rounded border border-slate-200 px-3 py-2 text-sm">
                            <input type="radio" name="decision" value="{{ $value }}" required class="border-slate-300"
                                   @checked(old('decision') === $value || ($value === 'SELECTED' && old('decision') === null))>
                            <span>{{ $label }}</span>
                        </label>
                    @endforeach
                </div>
                @error('decision')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <label for="reason" class="mb-1 mt-3 block text-xs font-medium text-slate-600">Reason *</label>
                <textarea id="reason" name="reason" rows="3" required
                          placeholder="Explain why this decision was made (minimum 10 characters)."
                          class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">{{ old('reason') }}</textarea>
                @error('reason')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror

                <button type="submit" class="mt-3 rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900"
                        onclick="return confirm('Record this decision?');">
                    Record decision
                </button>
            </form>
        @else
            <p class="mt-3 text-sm text-slate-500">Scoring is closed because a decision has been recorded.</p>
        @endif

        <h2 class="mt-6 text-sm font-semibold text-slate-900">Applicant statement</h2>
        <p class="mt-2 whitespace-pre-line text-sm leading-relaxed text-slate-700">{{ $application->statement }}</p>

        <div class="mt-4 grid gap-3 border-t border-slate-100 pt-4 sm:grid-cols-4">
            <div>
                <p class="text-xs text-slate-500">Current grade</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $application->current_grade ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">GPA</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $application->grade_point_average ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">Category</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $application->student->student_category?->label() ?? '—' }}</p>
            </div>
            <div>
                <p class="text-xs text-slate-500">District</p>
                <p class="mt-1 text-sm font-medium text-slate-900">{{ $application->student->district ?: '—' }}</p>
            </div>
        </div>
    </section>

    <aside class="space-y-6">
        <section class="rounded border border-slate-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-slate-900">Verification record</h2>
            @if ($application->verifications->isEmpty())
                <p class="mt-2 text-sm text-slate-500">No verification records.</p>
            @else
                <ul class="mt-2 space-y-2">
                    @foreach ($application->verifications as $record)
                        <li class="rounded border border-slate-200 px-3 py-2 text-sm">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-medium text-slate-800">{{ $record->stage->label() }}</span>
                                <x-status-badge :type="$record->status->badgeType()" :label="$record->status->label()" />
                            </div>
                            @if ($record->remarks)<p class="mt-1 text-xs text-slate-600">{{ $record->remarks }}</p>@endif
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>

        <section class="rounded border border-slate-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-slate-900">Documents</h2>
            @if ($application->documents->isEmpty())
                <p class="mt-2 text-sm text-slate-500">No documents uploaded.</p>
            @else
                <ul class="mt-2 space-y-2 text-sm">
                    @foreach ($application->documents as $document)
                        <li class="flex items-center justify-between gap-2">
                            <span class="text-slate-700">{{ $document->document_type }}</span>
                            <a href="{{ route('applications.documents.download', [$application, $document->id]) }}"
                               class="text-xs font-medium text-blue-800 hover:underline">Download</a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </section>
    </aside>
</div>
@endsection
