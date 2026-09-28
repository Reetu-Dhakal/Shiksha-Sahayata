@extends('layouts.app')

@section('title', __('nav.dashboard'))

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">Local Education Dashboard</h1>
    <p class="mt-1 text-sm text-slate-600">Applications assigned to your local education unit.</p>
</div>

<div class="grid gap-4 lg:grid-cols-3">
    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Pending verification</h2>
        <div class="mt-2">
            <x-empty-state title="Nothing pending" message="Applications awaiting local verification will appear here." />
        </div>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Completed verification</h2>
        <div class="mt-2">
            <x-empty-state title="No records" message="Verification records you complete will be listed here." />
        </div>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Returned applications</h2>
        <div class="mt-2">
            <x-empty-state title="No records" message="Applications returned for correction will be listed here." />
        </div>
    </section>
</div>
@endsection
