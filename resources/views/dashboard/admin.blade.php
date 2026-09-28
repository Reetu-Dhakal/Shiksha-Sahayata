@extends('layouts.app')

@section('title', __('nav.dashboard'))

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">Administration Dashboard</h1>
    <p class="mt-1 text-sm text-slate-600">System-wide status based on live database records.</p>
</div>

<div class="grid gap-4 lg:grid-cols-3">
    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Scholarships</h2>
        <div class="mt-2">
            <x-empty-state title="No scholarships yet" message="Create the first scholarship to get started.">
                @if (Route::has('admin.scholarships.create'))
                    <a href="{{ route('admin.scholarships.create') }}" class="rounded bg-blue-800 px-4 py-2 text-xs font-medium text-white hover:bg-blue-900">{{ __('nav.create_scholarship') }}</a>
                @endif
            </x-empty-state>
        </div>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Applications</h2>
        <div class="mt-2">
            <x-empty-state title="No applications yet" message="Applications, verifications and selections will appear here." />
        </div>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Users</h2>
        <div class="mt-2">
            <x-empty-state title="Manage the system" message="Create schools, local education units, officers and committee members.">
                @if (Route::has('admin.users.index'))
                    <a href="{{ route('admin.users.index') }}" class="rounded border border-slate-300 px-4 py-2 text-xs font-medium text-slate-700 hover:bg-slate-50">{{ __('nav.users') }}</a>
                @endif
            </x-empty-state>
        </div>
    </section>
</div>
@endsection
