@extends('layouts.app')

@section('title', $scholarship->exists ? __('common.edit') : __('nav.create_scholarship'))

@section('content')
@php
    $editing = $scholarship->exists;
    $criteriaRows = old('criteria', $scholarship->criteria->map(fn ($c) => [
        'name' => $c->getRawOriginal('name'),
        'name_np' => $c->getRawOriginal('name_np'),
        'description' => $c->getRawOriginal('description'),
        'description_np' => $c->getRawOriginal('description_np'),
        'weight' => $c->weight,
        'maximum_score' => $c->maximum_score,
    ])->values()->all());
    $documentRows = old('documents', $scholarship->requiredDocuments->map(fn ($d) => [
        'document_type' => $d->document_type->value,
        'description' => $d->getRawOriginal('description'),
        'description_np' => $d->getRawOriginal('description_np'),
        'is_required' => $d->is_required ? '1' : '0',
    ])->values()->all());
    $ruleRows = old('rules', $scholarship->eligibilityRules->map(fn ($r) => [
        'field' => $r->field->value,
        'operator' => $r->operator->value,
        'value' => $r->value,
        'description' => $r->getRawOriginal('description'),
        'description_np' => $r->getRawOriginal('description_np'),
    ])->values()->all());
    $committeeIds = old('committee', $scholarship->committeeMembers->pluck('user_id')->all());
@endphp

<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-lg font-semibold text-slate-900">
            {{ $editing ? __('admin.scholarships.form.edit') : __('nav.create_scholarship') }}
        </h1>
        @if ($editing)
            <p class="mt-1 text-sm text-slate-600">
                {{ __('admin.scholarships.form.current_status') }} <x-status-badge :type="$scholarship->status->badgeType()" :label="$scholarship->status->label()" />
            </p>
        @endif
    </div>
    @if ($editing)
        <div class="flex flex-wrap gap-2">
            @if ($scholarship->status === \App\Enums\ScholarshipStatus::DRAFT || $scholarship->status === \App\Enums\ScholarshipStatus::CLOSED)
                <form method="POST" action="{{ route('admin.scholarships.status', $scholarship) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="PUBLISHED">
                    <button type="submit" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">{{ __('admin.scholarships.publish') }}</button>
                </form>
            @endif
            @if ($scholarship->status === \App\Enums\ScholarshipStatus::PUBLISHED)
                <form method="POST" action="{{ route('admin.scholarships.status', $scholarship) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="CLOSED">
                    <button type="submit" class="rounded bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700">{{ __('admin.scholarships.form.close_applications') }}</button>
                </form>
            @endif
            @if ($scholarship->status === \App\Enums\ScholarshipStatus::PUBLISHED || $scholarship->status === \App\Enums\ScholarshipStatus::CLOSED)
                <form method="POST" action="{{ route('admin.scholarships.status', $scholarship) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="COMPLETED">
                    <button type="submit" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">{{ __('admin.scholarships.mark_completed') }}</button>
                </form>
            @endif
        </div>
    @endif
</div>

@if ($errors->has('status') || $errors->has('criteria') || $errors->has('documents'))
    <div class="mb-4 rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
        @foreach (['status', 'criteria', 'documents'] as $key)
            @if ($errors->has($key))
                <p>{{ $errors->first($key) }}</p>
            @endif
        @endforeach
    </div>
@endif

<form method="POST" action="{{ $editing ? route('admin.scholarships.update', $scholarship) : route('admin.scholarships.store') }}" class="space-y-6">
    @csrf
    @if ($editing)
        @method('PUT')
    @endif

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('admin.scholarships.form.details') }}</h2>
        <div class="mt-4 grid gap-4">
            <div>
                <label for="title" class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.scholarships.form.title') }} *</label>
                <input id="title" name="title" type="text" value="{{ old('title', $scholarship->getRawOriginal('title')) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('title') border-red-400 @enderror">
                @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="title_np" class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.scholarships.form.title_np') }}</label>
                <input id="title_np" name="title_np" type="text" value="{{ old('title_np', $scholarship->getRawOriginal('title_np')) }}"
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('title_np') border-red-400 @enderror">
                @error('title_np')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="provider" class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.scholarships.form.provider') }} *</label>
                    <input id="provider" name="provider" type="text" value="{{ old('provider', $scholarship->getRawOriginal('provider')) }}" required
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('provider') border-red-400 @enderror">
                    @error('provider')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="provider_np" class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.scholarships.form.provider_np') }}</label>
                    <input id="provider_np" name="provider_np" type="text" value="{{ old('provider_np', $scholarship->getRawOriginal('provider_np')) }}"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('provider_np') border-red-400 @enderror">
                    @error('provider_np')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="available_slots" class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.scholarships.form.available_slots') }}</label>
                    <input id="available_slots" name="available_slots" type="number" min="1" max="10000" value="{{ old('available_slots', $scholarship->available_slots) }}"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('available_slots') border-red-400 @enderror">
                    @error('available_slots')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="description" class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.scholarships.form.description') }} *</label>
                <textarea id="description" name="description" rows="5" required
                          class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('description') border-red-400 @enderror">{{ old('description', $scholarship->getRawOriginal('description')) }}</textarea>
                @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="description_np" class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.scholarships.form.description_np') }}</label>
                <textarea id="description_np" name="description_np" rows="5"
                          class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('description_np') border-red-400 @enderror">{{ old('description_np', $scholarship->getRawOriginal('description_np')) }}</textarea>
                @error('description_np')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('admin.scholarships.form.window') }}</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label for="application_start" class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.scholarships.form.application_start') }} *</label>
                <input id="application_start" name="application_start" type="date" value="{{ old('application_start', $scholarship->application_start?->format('Y-m-d')) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('application_start') border-red-400 @enderror">
                @error('application_start')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="application_deadline" class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.scholarships.form.application_deadline') }} *</label>
                <input id="application_deadline" name="application_deadline" type="date" value="{{ old('application_deadline', $scholarship->application_deadline?->format('Y-m-d')) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('application_deadline') border-red-400 @enderror">
                @error('application_deadline')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="education_level" class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.scholarships.form.education_level') }}</label>
                <select id="education_level" name="education_level"
                        class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                    <option value="">{{ __('common.any_level') }}</option>
                    @foreach (\App\Enums\EducationLevel::cases() as $level)
                        <option value="{{ $level->value }}" @selected(old('education_level', $scholarship->education_level?->value) === $level->value)>{{ $level->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="target_grade_min" class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.scholarships.form.minimum_grade') }}</label>
                <input id="target_grade_min" name="target_grade_min" type="number" min="1" max="12" value="{{ old('target_grade_min', $scholarship->target_grade_min) }}"
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('target_grade_min') border-red-400 @enderror">
                @error('target_grade_min')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="target_grade_max" class="mb-1 block text-sm font-medium text-slate-700">{{ __('admin.scholarships.form.maximum_grade') }}</label>
                <input id="target_grade_max" name="target_grade_max" type="number" min="1" max="12" value="{{ old('target_grade_max', $scholarship->target_grade_max) }}"
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('target_grade_max') border-red-400 @enderror">
                @error('target_grade_max')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <p class="mt-3 text-xs text-slate-500">{{ __('admin.scholarships.form.baseline_note') }}</p>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4"
             x-data="{
                 rows: @js($ruleRows),
                 add() { this.rows.push({ field: 'STUDENT_CATEGORY', operator: 'IN', value: '', description: '', description_np: '' }); },
                 remove(i) { this.rows.splice(i, 1); },
             }">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('admin.scholarships.form.rules_title') }}</h2>
            <button type="button" @click="add()" class="rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">{{ __('admin.scholarships.form.add_rule') }}</button>
        </div>

        <div class="mt-3 space-y-3">
            <template x-for="(rule, index) in rows" :key="index">
                <div class="grid gap-3 rounded border border-slate-200 p-3 sm:grid-cols-12">
                    <div class="sm:col-span-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('admin.scholarships.form.field') }}</label>
                        <select :name="`rules[${index}][field]`" x-model="rule.field"
                                class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                            @foreach (\App\Enums\EligibilityField::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('admin.scholarships.form.operator') }}</label>
                        <select :name="`rules[${index}][operator]`" x-model="rule.operator"
                                class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                            @foreach (\App\Enums\EligibilityOperator::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('admin.scholarships.form.value') }}</label>
                        <input type="text" :name="`rules[${index}][value]`" x-model="rule.value" placeholder="{{ __('admin.scholarships.form.value_placeholder') }}"
                               class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                    </div>
                    <div class="flex items-end justify-between gap-2 sm:col-span-3">
                        <div class="w-full space-y-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('admin.scholarships.form.label') }}</label>
                                <input type="text" :name="`rules[${index}][description]`" x-model="rule.description" placeholder="{{ __('admin.scholarships.form.label_placeholder') }}"
                                       class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('admin.scholarships.form.label_np') }}</label>
                                <input type="text" :name="`rules[${index}][description_np]`" x-model="rule.description_np"
                                       class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                            </div>
                        </div>
                        <button type="button" @click="remove(index)" class="mb-1 text-xs text-red-600 hover:underline">{{ __('admin.scholarships.form.remove') }}</button>
                    </div>
                </div>
            </template>

            <p x-show="rows.length === 0" x-cloak class="text-sm text-slate-500" style="display: none">
                {{ __('admin.scholarships.form.rules_empty') }}
            </p>
        </div>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4"
             x-data="{
                 rows: @js($criteriaRows),
                 add() { this.rows.push({ name: '', name_np: '', description: '', description_np: '', weight: '', maximum_score: 100 }); },
                 remove(i) { this.rows.splice(i, 1); },
                 total() { return this.rows.reduce((sum, row) => sum + (Number(row.weight) || 0), 0); },
             }">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">{{ __('admin.scholarships.form.criteria_title') }}</h2>
                <p class="mt-1 text-xs text-slate-500">{{ __('admin.scholarships.form.criteria_note') }}</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-600">{{ __('admin.scholarships.form.total') }} <strong x-text="total().toFixed(2)">0.00</strong>%</span>
                <button type="button" @click="add()" class="rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">{{ __('admin.scholarships.form.add_criterion') }}</button>
            </div>
        </div>

        <div class="mt-3 space-y-3">
            <template x-for="(row, index) in rows" :key="index">
                <div class="grid gap-3 rounded border border-slate-200 p-3 sm:grid-cols-12">
                    <div class="sm:col-span-4">
                        <div class="space-y-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('admin.scholarships.form.criterion_name') }}</label>
                                <input type="text" :name="`criteria[${index}][name]`" x-model="row.name" placeholder="{{ __('admin.scholarships.form.criterion_name_placeholder') }}"
                                       class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('admin.scholarships.form.criterion_name_np') }}</label>
                                <input type="text" :name="`criteria[${index}][name_np]`" x-model="row.name_np"
                                       class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-3">
                        <div class="space-y-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('admin.scholarships.form.criterion_description') }}</label>
                                <input type="text" :name="`criteria[${index}][description]`" x-model="row.description"
                                       class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('admin.scholarships.form.criterion_description_np') }}</label>
                                <input type="text" :name="`criteria[${index}][description_np]`" x-model="row.description_np"
                                       class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('admin.scholarships.form.weight') }}</label>
                        <input type="number" step="0.01" min="0.1" max="100" :name="`criteria[${index}][weight]`" x-model="row.weight"
                               class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                    </div>
                    <div class="flex items-end justify-between gap-2 sm:col-span-3">
                        <div class="w-full">
                            <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('admin.scholarships.form.maximum_score') }}</label>
                            <input type="number" min="1" max="1000" :name="`criteria[${index}][maximum_score]`" x-model="row.maximum_score"
                                   class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                        </div>
                        <button type="button" @click="remove(index)" class="mb-1 text-xs text-red-600 hover:underline">{{ __('admin.scholarships.form.remove') }}</button>
                    </div>
                </div>
            </template>

            <p x-show="rows.length === 0" x-cloak class="text-sm text-slate-500" style="display: none">
                {{ __('admin.scholarships.form.criteria_empty') }}
            </p>
        </div>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4"
             x-data="{
                 rows: @js($documentRows),
                 add() { this.rows.push({ document_type: 'BIRTH_REGISTRATION', description: '', description_np: '', is_required: '1' }); },
                 remove(i) { this.rows.splice(i, 1); },
             }">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900">{{ __('admin.scholarships.form.documents_title') }}</h2>
            <button type="button" @click="add()" class="rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">{{ __('admin.scholarships.form.add_document') }}</button>
        </div>

        <div class="mt-3 space-y-3">
            <template x-for="(doc, index) in rows" :key="index">
                <div class="grid gap-3 rounded border border-slate-200 p-3 sm:grid-cols-12">
                    <div class="sm:col-span-5">
                        <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('admin.scholarships.form.document_type') }}</label>
                        <select :name="`documents[${index}][document_type]`" x-model="doc.document_type"
                                class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                            @foreach (\App\Enums\DocumentType::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-4">
                        <div class="space-y-2">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('admin.scholarships.form.document_description') }}</label>
                                <input type="text" :name="`documents[${index}][description]`" x-model="doc.description"
                                       class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('admin.scholarships.form.document_description_np') }}</label>
                                <input type="text" :name="`documents[${index}][description_np]`" x-model="doc.description_np"
                                       class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                            </div>
                        </div>
                    </div>
                    <div class="flex items-end justify-between gap-2 sm:col-span-3">
                        <div class="w-full">
                            <label class="mb-1 block text-xs font-medium text-slate-600">{{ __('admin.scholarships.form.requirement') }}</label>
                            <select :name="`documents[${index}][is_required]`" x-model="doc.is_required"
                                    class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                                <option value="1">{{ __('common.required') }}</option>
                                <option value="0">{{ __('common.optional') }}</option>
                            </select>
                        </div>
                        <button type="button" @click="remove(index)" class="mb-1 text-xs text-red-600 hover:underline">{{ __('admin.scholarships.form.remove') }}</button>
                    </div>
                </div>
            </template>

            <p x-show="rows.length === 0" x-cloak class="text-sm text-slate-500" style="display: none">
                {{ __('admin.scholarships.form.documents_empty') }}
            </p>
        </div>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">{{ __('admin.scholarships.form.committee_title') }}</h2>
        <p class="mt-1 text-xs text-slate-500">{{ __('admin.scholarships.form.committee_note') }}</p>

        @if ($committeeMembers->isEmpty())
            <p class="mt-3 text-sm text-slate-500">{{ __('admin.scholarships.form.committee_empty') }}</p>
        @else
            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                @foreach ($committeeMembers as $member)
                    <label class="flex items-center gap-2 rounded border border-slate-200 px-3 py-2 text-sm">
                        <input type="checkbox" name="committee[]" value="{{ $member->id }}" class="rounded border-slate-300"
                               @checked(in_array($member->id, (array) $committeeIds, false))>
                        <span>{{ $member->name }}</span>
                    </label>
                @endforeach
            </div>
        @endif
    </section>

    <div class="flex gap-3">
        <button type="submit" class="rounded bg-blue-800 px-5 py-2 text-sm font-medium text-white hover:bg-blue-900">
            {{ $editing ? __('common.save') : __('admin.scholarships.form.create') }}
        </button>
        <a href="{{ route('admin.scholarships.index') }}" class="rounded border border-slate-300 px-5 py-2 text-sm text-slate-700 hover:bg-slate-50">
            {{ __('common.cancel') }}
        </a>
    </div>
</form>
@endsection
