@extends('layouts.app')

@section('title', __('nav.dashboard'))

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">Selection Committee Dashboard</h1>
    <p class="mt-1 text-sm text-slate-600">Verified applications ready for review on scholarships you are assigned to.</p>
</div>

<div class="grid gap-4 lg:grid-cols-2">
    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Applications ready for review</h2>
        <div class="mt-2">
            <x-empty-state title="Nothing to review" message="Applications that pass all required verifications will appear here." />
        </div>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Decisions recorded</h2>
        <div class="mt-2">
            <x-empty-state title="No decisions yet" message="Selected, waitlisted and rejected counts will appear here." />
        </div>
    </section>
</div>
@endsection
