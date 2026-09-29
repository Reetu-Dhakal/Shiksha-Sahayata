@extends('layouts.app')

@section('title', __('nav.profile'))

@section('content')
<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-lg font-semibold text-slate-900">{{ __('nav.profile') }}</h1>
        <p class="mt-1 text-sm text-slate-600">
            {{ __('profile.show.id_note_prefix') }}
            <span class="font-medium text-slate-800">{{ $student->scholar_student_id }}</span>
            {{ __('profile.show.id_note_suffix') }}
        </p>
    </div>
    <a href="{{ route('profile.edit') }}" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
        {{ __('common.edit') }}
    </a>
</div>

<div class="grid gap-4 lg:grid-cols-2">
    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('profile.show.student_details') }}</h2>
        <dl class="mt-3 space-y-2 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('common.name') }}</dt>
                <dd class="text-right font-medium text-slate-800">{{ $student->name }}</dd>
            </div>
            @if ($student->name_np)
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('profile.show.name_nepali') }}</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $student->name_np }}</dd>
                </div>
            @endif
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('profile.show.date_of_birth') }}</dt>
                <dd class="text-right font-medium text-slate-800">{{ $student->date_of_birth->format('Y-m-d') }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('profile.show.gender') }}</dt>
                <dd class="text-right font-medium text-slate-800">{{ $student->gender->label() }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('profile.show.education_level') }}</dt>
                <dd class="text-right font-medium text-slate-800">{{ $student->education_level?->label() }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('profile.show.grade') }}</dt>
                <dd class="text-right font-medium text-slate-800">{{ $student->grade }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('profile.show.birth_registration_no') }}</dt>
                <dd class="text-right font-medium text-slate-800">{{ $student->birth_registration_number ?: '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('profile.show.category') }}</dt>
                <dd class="text-right font-medium text-slate-800">{{ $student->student_category?->label() ?: '—' }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('common.status') }}</dt>
                <dd class="text-right">
                    @if ($student->isVerified())
                        <x-status-badge type="success" :label="__('profile.show.verified')" />
                    @else
                        <x-status-badge type="warning" :label="__('profile.show.not_verified')" />
                    @endif
                </dd>
            </div>
        </dl>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('profile.show.location_school') }}</h2>
        <dl class="mt-3 space-y-2 text-sm">
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('profile.show.province') }}</dt>
                <dd class="text-right font-medium text-slate-800">{{ $student->province }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('profile.show.district') }}</dt>
                <dd class="text-right font-medium text-slate-800">{{ $student->district }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('profile.show.municipality') }}</dt>
                <dd class="text-right font-medium text-slate-800">{{ $student->municipality }}</dd>
            </div>
            <div class="flex justify-between gap-4">
                <dt class="text-slate-500">{{ __('profile.show.school') }}</dt>
                <dd class="text-right font-medium text-slate-800">{{ $student->school?->name ?? '—' }}</dd>
            </div>
        </dl>

        <h2 class="mt-5 text-sm font-semibold text-slate-900">{{ __('profile.show.guardian') }}</h2>
        @if ($student->guardian)
            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('common.name') }}</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $student->guardian->name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('profile.show.relationship') }}</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $student->guardian->relationship }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('common.phone') }}</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $student->guardian->phone }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('common.address') }}</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $student->guardian->address ?: '—' }}</dd>
                </div>
            </dl>
        @else
            <p class="mt-2 text-sm text-slate-500">{{ __('common.no_records') }}</p>
        @endif
    </section>
</div>
@endsection
