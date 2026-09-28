@extends('layouts.app')

@section('title', __('nav.profile'))

@section('content')
@php($editing = $student !== null)

<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">{{ $editing ? 'Edit student profile' : 'Complete your student profile' }}</h1>
    <p class="mt-1 text-sm text-slate-600">
        Only information required for scholarship processing is collected. A National ID is not required.
    </p>
</div>

<form method="POST" action="{{ $editing ? route('profile.update') : route('profile.store') }}" class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Student details</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">{{ __('common.name') }} *</label>
                <input id="name" name="name" type="text" value="{{ old('name', $student?->name) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="name_np" class="mb-1 block text-sm font-medium text-slate-700">नाम (नेपाली) <span class="font-normal text-slate-400">({{ __('common.optional') }})</span></label>
                <input id="name_np" name="name_np" type="text" value="{{ old('name_np', $student?->name_np) }}"
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('name_np') border-red-400 @enderror">
                @error('name_np')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="date_of_birth" class="mb-1 block text-sm font-medium text-slate-700">Date of birth *</label>
                <input id="date_of_birth" name="date_of_birth" type="date" value="{{ old('date_of_birth', $student?->date_of_birth?->format('Y-m-d')) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('date_of_birth') border-red-400 @enderror">
                @error('date_of_birth')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="gender" class="mb-1 block text-sm font-medium text-slate-700">Gender *</label>
                <select id="gender" name="gender" required
                        class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('gender') border-red-400 @enderror">
                    <option value="">Select</option>
                    @foreach (\App\Enums\Gender::cases() as $gender)
                        <option value="{{ $gender->value }}" @selected(old('gender', $student?->gender?->value) === $gender->value)>
                            {{ $gender->label() }}
                        </option>
                    @endforeach
                </select>
                @error('gender')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="birth_registration_number" class="mb-1 block text-sm font-medium text-slate-700">Birth registration number <span class="font-normal text-slate-400">({{ __('common.optional') }})</span></label>
                <input id="birth_registration_number" name="birth_registration_number" type="text" value="{{ old('birth_registration_number', $student?->birth_registration_number) }}"
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('birth_registration_number') border-red-400 @enderror">
                @error('birth_registration_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="student_category" class="mb-1 block text-sm font-medium text-slate-700">Category <span class="font-normal text-slate-400">({{ __('common.optional') }})</span></label>
                <select id="student_category" name="student_category"
                        class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('student_category') border-red-400 @enderror">
                    <option value="">Select</option>
                    @foreach (\App\Enums\StudentCategory::options() as $value => $label)
                        <option value="{{ $value }}" @selected(old('student_category', $student?->student_category?->value) === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('student_category')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Education &amp; location</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label for="education_level" class="mb-1 block text-sm font-medium text-slate-700">Education level *</label>
                <select id="education_level" name="education_level" required
                        class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('education_level') border-red-400 @enderror">
                    <option value="">Select</option>
                    @foreach (\App\Enums\EducationLevel::cases() as $level)
                        <option value="{{ $level->value }}" @selected(old('education_level', $student?->education_level?->value) === $level->value)>{{ $level->label() }}</option>
                    @endforeach
                </select>
                @error('education_level')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="grade" class="mb-1 block text-sm font-medium text-slate-700">Grade / class (year for higher education) *</label>
                <input id="grade" name="grade" type="number" min="1" max="12" value="{{ old('grade', $student?->grade) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('grade') border-red-400 @enderror">
                @error('grade')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="school_id" class="mb-1 block text-sm font-medium text-slate-700">School *</label>
                <select id="school_id" name="school_id" required
                        class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('school_id') border-red-400 @enderror">
                    <option value="">Select</option>
                    @foreach ($schools as $school)
                        <option value="{{ $school->id }}" @selected((int) old('school_id', $student?->school_id) === $school->id)>
                            {{ $school->name }} ({{ $school->district }})
                        </option>
                    @endforeach
                </select>
                @error('school_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="province" class="mb-1 block text-sm font-medium text-slate-700">Province *</label>
                <input id="province" name="province" type="text" value="{{ old('province', $student?->province) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('province') border-red-400 @enderror">
                @error('province')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="district" class="mb-1 block text-sm font-medium text-slate-700">District *</label>
                <input id="district" name="district" type="text" value="{{ old('district', $student?->district) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('district') border-red-400 @enderror">
                @error('district')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="municipality" class="mb-1 block text-sm font-medium text-slate-700">Municipality / ward *</label>
                <input id="municipality" name="municipality" type="text" value="{{ old('municipality', $student?->municipality) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('municipality') border-red-400 @enderror">
                @error('municipality')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Guardian details</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2">
            <div>
                <label for="guardian_name" class="mb-1 block text-sm font-medium text-slate-700">Guardian name *</label>
                <input id="guardian_name" name="guardian_name" type="text" value="{{ old('guardian_name', $student?->guardian?->name) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('guardian_name') border-red-400 @enderror">
                @error('guardian_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="guardian_relationship" class="mb-1 block text-sm font-medium text-slate-700">Relationship *</label>
                <input id="guardian_relationship" name="guardian_relationship" type="text" placeholder="Father / Mother / Guardian"
                       value="{{ old('guardian_relationship', $student?->guardian?->relationship) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('guardian_relationship') border-red-400 @enderror">
                @error('guardian_relationship')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="guardian_phone" class="mb-1 block text-sm font-medium text-slate-700">Guardian phone *</label>
                <input id="guardian_phone" name="guardian_phone" type="text" value="{{ old('guardian_phone', $student?->guardian?->phone) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('guardian_phone') border-red-400 @enderror">
                @error('guardian_phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="guardian_citizenship_number" class="mb-1 block text-sm font-medium text-slate-700">Guardian citizenship number <span class="font-normal text-slate-400">({{ __('common.optional') }})</span></label>
                <input id="guardian_citizenship_number" name="guardian_citizenship_number" type="text"
                       value="{{ old('guardian_citizenship_number', $student?->guardian?->citizenship_number) }}"
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('guardian_citizenship_number') border-red-400 @enderror">
                @error('guardian_citizenship_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="guardian_address" class="mb-1 block text-sm font-medium text-slate-700">Guardian address <span class="font-normal text-slate-400">({{ __('common.optional') }})</span></label>
                <input id="guardian_address" name="guardian_address" type="text" value="{{ old('guardian_address', $student?->guardian?->address) }}"
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('guardian_address') border-red-400 @enderror">
                @error('guardian_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <div class="flex gap-3">
        <button type="submit" class="rounded bg-blue-800 px-5 py-2 text-sm font-medium text-white hover:bg-blue-900">
            {{ $editing ? __('common.save') : 'Create profile' }}
        </button>
        <a href="{{ $editing ? route('profile.show') : route('dashboard') }}" class="rounded border border-slate-300 px-5 py-2 text-sm text-slate-700 hover:bg-slate-50">
            {{ __('common.cancel') }}
        </a>
    </div>
</form>
@endsection
