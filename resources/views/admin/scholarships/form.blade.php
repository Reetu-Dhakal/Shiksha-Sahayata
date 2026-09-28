@extends('layouts.app')

@section('title', $scholarship->exists ? __('common.edit') : __('nav.create_scholarship'))

@section('content')
@php
    $editing = $scholarship->exists;
    $criteriaRows = old('criteria', $scholarship->criteria->map(fn ($c) => [
        'name' => $c->name,
        'description' => $c->description,
        'weight' => $c->weight,
        'maximum_score' => $c->maximum_score,
    ])->values()->all());
    $documentRows = old('documents', $scholarship->requiredDocuments->map(fn ($d) => [
        'document_type' => $d->document_type->value,
        'description' => $d->description,
        'is_required' => $d->is_required ? '1' : '0',
    ])->values()->all());
    $ruleRows = old('rules', $scholarship->eligibilityRules->map(fn ($r) => [
        'field' => $r->field->value,
        'operator' => $r->operator->value,
        'value' => $r->value,
        'description' => $r->description,
    ])->values()->all());
    $committeeIds = old('committee', $scholarship->committeeMembers->pluck('user_id')->all());
@endphp

<div class="mb-6 flex flex-wrap items-start justify-between gap-3">
    <div>
        <h1 class="text-lg font-semibold text-slate-900">
            {{ $editing ? 'Edit scholarship' : __('nav.create_scholarship') }}
        </h1>
        @if ($editing)
            <p class="mt-1 text-sm text-slate-600">
                Current status: <x-status-badge :type="$scholarship->status->badgeType()" :label="$scholarship->status->label()" />
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
                    <button type="submit" class="rounded bg-green-700 px-4 py-2 text-sm font-medium text-white hover:bg-green-800">Publish</button>
                </form>
            @endif
            @if ($scholarship->status === \App\Enums\ScholarshipStatus::PUBLISHED)
                <form method="POST" action="{{ route('admin.scholarships.status', $scholarship) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="CLOSED">
                    <button type="submit" class="rounded bg-amber-600 px-4 py-2 text-sm font-medium text-white hover:bg-amber-700">Close applications</button>
                </form>
            @endif
            @if ($scholarship->status === \App\Enums\ScholarshipStatus::PUBLISHED || $scholarship->status === \App\Enums\ScholarshipStatus::CLOSED)
                <form method="POST" action="{{ route('admin.scholarships.status', $scholarship) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="COMPLETED">
                    <button type="submit" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">Mark completed</button>
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
        <h2 class="text-sm font-semibold text-slate-900">Scholarship details</h2>
        <div class="mt-4 grid gap-4">
            <div>
                <label for="title" class="mb-1 block text-sm font-medium text-slate-700">Title *</label>
                <input id="title" name="title" type="text" value="{{ old('title', $scholarship->title) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('title') border-red-400 @enderror">
                @error('title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="provider" class="mb-1 block text-sm font-medium text-slate-700">Provider *</label>
                    <input id="provider" name="provider" type="text" value="{{ old('provider', $scholarship->provider) }}" required
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('provider') border-red-400 @enderror">
                    @error('provider')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="available_slots" class="mb-1 block text-sm font-medium text-slate-700">Available slots</label>
                    <input id="available_slots" name="available_slots" type="number" min="1" max="10000" value="{{ old('available_slots', $scholarship->available_slots) }}"
                           class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('available_slots') border-red-400 @enderror">
                    @error('available_slots')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>
            <div>
                <label for="description" class="mb-1 block text-sm font-medium text-slate-700">Description *</label>
                <textarea id="description" name="description" rows="5" required
                          class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('description') border-red-400 @enderror">{{ old('description', $scholarship->description) }}</textarea>
                @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Application window &amp; eligibility baseline</h2>
        <div class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div>
                <label for="application_start" class="mb-1 block text-sm font-medium text-slate-700">Application start *</label>
                <input id="application_start" name="application_start" type="date" value="{{ old('application_start', $scholarship->application_start?->format('Y-m-d')) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('application_start') border-red-400 @enderror">
                @error('application_start')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="application_deadline" class="mb-1 block text-sm font-medium text-slate-700">Application deadline *</label>
                <input id="application_deadline" name="application_deadline" type="date" value="{{ old('application_deadline', $scholarship->application_deadline?->format('Y-m-d')) }}" required
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('application_deadline') border-red-400 @enderror">
                @error('application_deadline')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="education_level" class="mb-1 block text-sm font-medium text-slate-700">Education level</label>
                <select id="education_level" name="education_level"
                        class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600">
                    <option value="">Any level</option>
                    @foreach (\App\Enums\EducationLevel::cases() as $level)
                        <option value="{{ $level->value }}" @selected(old('education_level', $scholarship->education_level?->value) === $level->value)>{{ $level->label() }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="target_grade_min" class="mb-1 block text-sm font-medium text-slate-700">Minimum grade</label>
                <input id="target_grade_min" name="target_grade_min" type="number" min="1" max="12" value="{{ old('target_grade_min', $scholarship->target_grade_min) }}"
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('target_grade_min') border-red-400 @enderror">
                @error('target_grade_min')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="target_grade_max" class="mb-1 block text-sm font-medium text-slate-700">Maximum grade</label>
                <input id="target_grade_max" name="target_grade_max" type="number" min="1" max="12" value="{{ old('target_grade_max', $scholarship->target_grade_max) }}"
                       class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-blue-600 focus:outline-none focus:ring-1 focus:ring-blue-600 @error('target_grade_max') border-red-400 @enderror">
                @error('target_grade_max')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
        <p class="mt-3 text-xs text-slate-500">Baseline rules: the student's grade and education level are checked automatically. Add any extra rules below.</p>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4"
             x-data="{
                 rows: @js($ruleRows),
                 add() { this.rows.push({ field: 'STUDENT_CATEGORY', operator: 'IN', value: '', description: '' }); },
                 remove(i) { this.rows.splice(i, 1); },
             }">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900">Additional eligibility rules</h2>
            <button type="button" @click="add()" class="rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Add rule</button>
        </div>

        <div class="mt-3 space-y-3">
            <template x-for="(rule, index) in rows" :key="index">
                <div class="grid gap-3 rounded border border-slate-200 p-3 sm:grid-cols-12">
                    <div class="sm:col-span-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Field</label>
                        <select :name="`rules[${index}][field]`" x-model="rule.field"
                                class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                            @foreach (\App\Enums\EligibilityField::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Operator</label>
                        <select :name="`rules[${index}][operator]`" x-model="rule.operator"
                                class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                            @foreach (\App\Enums\EligibilityOperator::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-4">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Value</label>
                        <input type="text" :name="`rules[${index}][value]`" x-model="rule.value" placeholder="e.g. DALIT, LOW_INCOME"
                               class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                    </div>
                    <div class="flex items-end justify-between gap-2 sm:col-span-2">
                        <div class="w-full">
                            <label class="mb-1 block text-xs font-medium text-slate-600">Label (optional)</label>
                            <input type="text" :name="`rules[${index}][description]`" x-model="rule.description" placeholder="Shown to students"
                                   class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                        </div>
                        <button type="button" @click="remove(index)" class="mb-1 text-xs text-red-600 hover:underline">Remove</button>
                    </div>
                </div>
            </template>

            <p x-show="rows.length === 0" x-cloak class="text-sm text-slate-500" style="display: none">
                No additional rules. The grade range and education level above are still checked.
            </p>
        </div>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4"
             x-data="{
                 rows: @js($criteriaRows),
                 add() { this.rows.push({ name: '', description: '', weight: '', maximum_score: 100 }); },
                 remove(i) { this.rows.splice(i, 1); },
                 total() { return this.rows.reduce((sum, row) => sum + (Number(row.weight) || 0), 0); },
             }">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div>
                <h2 class="text-sm font-semibold text-slate-900">Selection criteria &amp; weights</h2>
                <p class="mt-1 text-xs text-slate-500">Weighted score for a criterion = (score ÷ maximum score) × weight. Weights must total 100% to publish.</p>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm text-slate-600">Total: <strong x-text="total().toFixed(2)">0.00</strong>%</span>
                <button type="button" @click="add()" class="rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Add criterion</button>
            </div>
        </div>

        <div class="mt-3 space-y-3">
            <template x-for="(row, index) in rows" :key="index">
                <div class="grid gap-3 rounded border border-slate-200 p-3 sm:grid-cols-12">
                    <div class="sm:col-span-4">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Criterion name</label>
                        <input type="text" :name="`criteria[${index}][name]`" x-model="row.name" placeholder="e.g. Economic condition"
                               class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                    </div>
                    <div class="sm:col-span-3">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Description (optional)</label>
                        <input type="text" :name="`criteria[${index}][description]`" x-model="row.description"
                               class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Weight (%)</label>
                        <input type="number" step="0.01" min="0.1" max="100" :name="`criteria[${index}][weight]`" x-model="row.weight"
                               class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                    </div>
                    <div class="flex items-end justify-between gap-2 sm:col-span-3">
                        <div class="w-full">
                            <label class="mb-1 block text-xs font-medium text-slate-600">Maximum score</label>
                            <input type="number" min="1" max="1000" :name="`criteria[${index}][maximum_score]`" x-model="row.maximum_score"
                                   class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                        </div>
                        <button type="button" @click="remove(index)" class="mb-1 text-xs text-red-600 hover:underline">Remove</button>
                    </div>
                </div>
            </template>

            <p x-show="rows.length === 0" x-cloak class="text-sm text-slate-500" style="display: none">
                No criteria defined yet. At least one criterion totalling 100% is required to publish.
            </p>
        </div>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4"
             x-data="{
                 rows: @js($documentRows),
                 add() { this.rows.push({ document_type: 'BIRTH_REGISTRATION', description: '', is_required: '1' }); },
                 remove(i) { this.rows.splice(i, 1); },
             }">
        <div class="flex items-center justify-between">
            <h2 class="text-sm font-semibold text-slate-900">Required documents</h2>
            <button type="button" @click="add()" class="rounded border border-slate-300 px-3 py-1.5 text-xs font-medium text-slate-700 hover:bg-slate-50">Add document</button>
        </div>

        <div class="mt-3 space-y-3">
            <template x-for="(doc, index) in rows" :key="index">
                <div class="grid gap-3 rounded border border-slate-200 p-3 sm:grid-cols-12">
                    <div class="sm:col-span-5">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Document type</label>
                        <select :name="`documents[${index}][document_type]`" x-model="doc.document_type"
                                class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                            @foreach (\App\Enums\DocumentType::options() as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sm:col-span-4">
                        <label class="mb-1 block text-xs font-medium text-slate-600">Description / instruction</label>
                        <input type="text" :name="`documents[${index}][description]`" x-model="doc.description"
                               class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                    </div>
                    <div class="flex items-end justify-between gap-2 sm:col-span-3">
                        <div class="w-full">
                            <label class="mb-1 block text-xs font-medium text-slate-600">Requirement</label>
                            <select :name="`documents[${index}][is_required]`" x-model="doc.is_required"
                                    class="w-full rounded border border-slate-300 px-2 py-1.5 text-sm">
                                <option value="1">Required</option>
                                <option value="0">Optional</option>
                            </select>
                        </div>
                        <button type="button" @click="remove(index)" class="mb-1 text-xs text-red-600 hover:underline">Remove</button>
                    </div>
                </div>
            </template>

            <p x-show="rows.length === 0" x-cloak class="text-sm text-slate-500" style="display: none">
                No documents defined. At least one required document is needed before publishing.
            </p>
        </div>
    </section>

    <section class="rounded border border-slate-200 bg-white p-4">
        <h2 class="text-sm font-semibold text-slate-900">Selection committee</h2>
        <p class="mt-1 text-xs text-slate-500">Only the members selected here can review and decide on this scholarship's applications.</p>

        @if ($committeeMembers->isEmpty())
            <p class="mt-3 text-sm text-slate-500">No committee accounts exist yet. Create a user with the Selection Committee role first.</p>
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
            {{ $editing ? __('common.save') : 'Create scholarship' }}
        </button>
        <a href="{{ route('admin.scholarships.index') }}" class="rounded border border-slate-300 px-5 py-2 text-sm text-slate-700 hover:bg-slate-50">
            {{ __('common.cancel') }}
        </a>
    </div>
</form>
@endsection
