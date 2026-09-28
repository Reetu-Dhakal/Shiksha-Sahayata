@extends('layouts.public')

@section('title', __('nav.verify_award'))

@section('content')
<div class="mx-auto max-w-2xl px-4 py-10">
    <h1 class="text-xl font-semibold text-slate-900">{{ __('nav.verify_award') }}</h1>
    <p class="mt-2 text-sm text-slate-600">
        Enter the verification code printed on an award letter or shared by the scholarship office to confirm that an award is genuine.
    </p>

    <form method="GET" action="{{ route('verify.award.form') }}" class="mt-6 rounded border border-slate-200 bg-white p-5">
        <label for="code" class="block text-sm font-medium text-slate-700">Verification code</label>
        <input id="code" name="code" type="text" required autocomplete="off" placeholder="e.g. 4FJ2K9QW8SLM3N7P0RTY5XZ1"
               class="mt-2 w-full rounded border border-slate-300 px-3 py-2 font-mono text-sm uppercase focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
        <button type="submit" class="mt-4 rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900">
            Verify award
        </button>
    </form>
</div>
@endsection
