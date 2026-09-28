@extends('layouts.app')

@section('title', __('nav.profile'))

@section('content')
<div class="mb-6">
    <h1 class="text-lg font-semibold text-slate-900">Guardian Profile</h1>
    <p class="mt-1 text-sm text-slate-600">Manage your guardian details and the student profiles linked to your account.</p>
</div>

@unless ($guardian)
    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Create guardian profile</h2>
        <form method="POST" action="{{ route('guardian.profile.store') }}" class="mt-4 grid gap-4 sm:grid-cols-2">
            @csrf
            <div>
                <label for="name" class="mb-1 block text-sm font-medium text-slate-700">{{ __('common.name') }} *</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('name') border-red-400 @enderror">
                @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="relationship" class="mb-1 block text-sm font-medium text-slate-700">Relationship *</label>
                <input id="relationship" name="relationship" type="text" placeholder="Father / Mother / Guardian" value="{{ old('relationship') }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('relationship') border-red-400 @enderror">
                @error('relationship')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="phone" class="mb-1 block text-sm font-medium text-slate-700">{{ __('common.phone') }} *</label>
                <input id="phone" name="phone" type="text" value="{{ old('phone') }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('phone') border-red-400 @enderror">
                @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="citizenship_number" class="mb-1 block text-sm font-medium text-slate-700">Citizenship number <span class="font-normal text-slate-400">({{ __('common.optional') }})</span></label>
                <input id="citizenship_number" name="citizenship_number" type="text" value="{{ old('citizenship_number') }}"
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('citizenship_number') border-red-400 @enderror">
                @error('citizenship_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="address" class="mb-1 block text-sm font-medium text-slate-700">{{ __('common.address') }} <span class="font-normal text-slate-400">({{ __('common.optional') }})</span></label>
                <input id="address" name="address" type="text" value="{{ old('address') }}"
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('address') border-red-400 @enderror">
                @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <button type="submit" class="rounded bg-blue-800 px-5 py-2 text-sm font-medium text-white hover:bg-blue-900">Create profile</button>
            </div>
        </form>
    </section>
@else
    <div class="grid gap-4 lg:grid-cols-2">
        <section class="rounded border border-slate-200 bg-white p-4" x-data="{ editing: false }">
            <div class="flex items-center justify-between">
                <h2 class="text-sm font-semibold text-slate-900">Guardian details</h2>
                <button type="button" @click="editing = !editing"
                        class="text-xs font-medium text-blue-800 hover:underline">
                    <span x-text="editing ? '{{ __('common.cancel') }}' : '{{ __('common.edit') }}'">{{ __('common.edit') }}</span>
                </button>
            </div>

            <dl class="mt-3 space-y-2 text-sm">
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('common.name') }}</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $guardian->name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Relationship</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $guardian->relationship }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('common.phone') }}</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $guardian->phone }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">Citizenship number</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $guardian->citizenship_number ?: '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt class="text-slate-500">{{ __('common.address') }}</dt>
                    <dd class="text-right font-medium text-slate-800">{{ $guardian->address ?: '—' }}</dd>
                </div>
            </dl>

            <form method="POST" action="{{ route('guardian.profile.update') }}" class="mt-4 border-t border-slate-100 pt-4" x-show="editing" x-cloak style="display: none">
                @csrf
                @method('PUT')
                <div class="grid gap-3 sm:grid-cols-2">
                    <div>
                        <label for="g_name" class="mb-1 block text-sm font-medium text-slate-700">{{ __('common.name') }} *</label>
                        <input id="g_name" name="name" type="text" value="{{ old('name', $guardian->name) }}" required
                               class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('name') border-red-400 @enderror">
                        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="g_relationship" class="mb-1 block text-sm font-medium text-slate-700">Relationship *</label>
                        <input id="g_relationship" name="relationship" type="text" value="{{ old('relationship', $guardian->relationship) }}" required
                               class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('relationship') border-red-400 @enderror">
                        @error('relationship')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="g_phone" class="mb-1 block text-sm font-medium text-slate-700">{{ __('common.phone') }} *</label>
                        <input id="g_phone" name="phone" type="text" value="{{ old('phone', $guardian->phone) }}" required
                               class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('phone') border-red-400 @enderror">
                        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="g_citizenship" class="mb-1 block text-sm font-medium text-slate-700">Citizenship number</label>
                        <input id="g_citizenship" name="citizenship_number" type="text" value="{{ old('citizenship_number', $guardian->citizenship_number) }}"
                               class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                    </div>
                    <div class="sm:col-span-2">
                        <label for="g_address" class="mb-1 block text-sm font-medium text-slate-700">{{ __('common.address') }}</label>
                        <input id="g_address" name="address" type="text" value="{{ old('address', $guardian->address) }}"
                               class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                    </div>
                </div>
                <div class="mt-3 flex gap-2">
                    <button type="submit" class="rounded bg-blue-800 px-4 py-2 text-xs font-medium text-white hover:bg-blue-900">{{ __('common.save') }}</button>
                </div>
            </form>
        </section>

        <section class="rounded border border-slate-200 bg-white p-4">
            <h2 class="text-sm font-semibold text-slate-900">Link a student profile</h2>
            <p class="mt-1 text-xs text-slate-500">Enter the Scholar Student ID of your child to link their profile to your account.</p>
            <form method="POST" action="{{ route('guardian.students.link') }}" class="mt-3 flex flex-wrap gap-2">
                @csrf
                <label for="scholar_student_id" class="sr-only">Scholar Student ID</label>
                <input id="scholar_student_id" name="scholar_student_id" type="text" placeholder="SS-2026-0001" value="{{ old('scholar_student_id') }}"
                       class="w-48 rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('scholar_student_id') border-red-400 @enderror">
                <button type="submit" class="rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900">Link</button>
            </form>
            @error('scholar_student_id')<p class="mt-2 text-xs text-red-600">{{ $message }}</p>@enderror
        </section>
    </div>

    <section class="mt-4 rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Linked students</h2>
        <div class="mt-3 overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead class="border-b border-slate-200 text-xs uppercase text-slate-500">
                    <tr>
                        <th class="py-2 pr-4">Scholar Student ID</th>
                        <th class="py-2 pr-4">{{ __('common.name') }}</th>
                        <th class="py-2 pr-4">Grade</th>
                        <th class="py-2 pr-4">School</th>
                        <th class="py-2">{{ __('common.status') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($students as $student)
                        <tr>
                            <td class="py-2 pr-4 font-medium text-slate-800">{{ $student->scholar_student_id }}</td>
                            <td class="py-2 pr-4 text-slate-700">{{ $student->name }}</td>
                            <td class="py-2 pr-4 text-slate-700">{{ $student->grade }}</td>
                            <td class="py-2 pr-4 text-slate-700">{{ $student->school?->name ?? '—' }}</td>
                            <td class="py-2">
                                @if ($student->isVerified())
                                    <x-status-badge type="success" label="Verified" />
                                @else
                                    <x-status-badge type="warning" label="Unverified" />
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-sm text-slate-500">
                                No student profiles are linked to your account yet.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endunless
@endsection
