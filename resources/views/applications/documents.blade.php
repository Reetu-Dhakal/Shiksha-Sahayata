@extends('layouts.app')

@section('title', 'Documents')

@section('content')
<div class="mb-6">
    <nav class="text-xs text-slate-500">
        <a href="{{ route('applications.index') }}" class="hover:text-blue-800">My applications</a>
        <span class="mx-1">/</span>
        <a href="{{ route('applications.show', $application) }}" class="hover:text-blue-800">{{ $application->scholarship->title }}</a>
        <span class="mx-1">/</span>
        <span>Documents</span>
    </nav>
    <h1 class="mt-2 text-lg font-semibold text-slate-900">Supporting documents</h1>
    <p class="mt-1 text-sm text-slate-600">
        Upload every required document before submitting. Accepted formats: PDF, JPG or PNG, maximum 5 MB each.
    </p>
    <div class="mt-2">
        <x-status-badge :type="$application->status->badgeType()" :label="$application->status->label()" />
    </div>
</div>

@if (! $application->isEditable())
    <div class="mb-4 rounded border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-slate-600">
        This application is {{ $application->status->label() }}, so its documents are locked.
    </div>
@endif

<div class="space-y-4">
    @forelse ($requiredDocuments as $document)
        @php $uploaded = $application->documents->firstWhere('document_type', $document->document_type->value); @endphp
        <section class="rounded border border-slate-200 bg-white p-4">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div>
                    <h2 class="text-sm font-semibold text-slate-900">{{ $document->document_type->label() }}</h2>
                    <p class="mt-1 text-xs text-slate-500">{{ $document->description ?: 'No special instructions.' }}</p>
                </div>
                <x-status-badge :type="$document->is_required ? 'warning' : 'neutral'" :label="$document->is_required ? 'Required' : 'Optional'" />
            </div>

            @if ($uploaded)
                <div class="mt-3 flex flex-wrap items-center justify-between gap-3 rounded border border-green-200 bg-green-50 px-3 py-2">
                    <div class="text-sm text-green-900">
                        <p class="font-medium">{{ $uploaded->original_filename }}</p>
                        <p class="text-xs text-green-700">
                            Uploaded {{ $uploaded->created_at->format('j M Y, H:i') }}
                            @if ($uploaded->size_bytes) · {{ number_format($uploaded->size_bytes / 1024, 1) }} KB @endif
                        </p>
                    </div>
                    <div class="flex gap-3 text-xs">
                        <a href="{{ route('applications.documents.download', [$application, $uploaded->id]) }}" class="font-medium text-blue-800 hover:underline">Download</a>
                        @if ($application->isEditable())
                            <form method="POST" action="{{ route('applications.documents.destroy', [$application, $uploaded->id]) }}"
                                  onsubmit="return confirm('Remove this document?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="font-medium text-red-700 hover:underline">Remove</button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            @if ($application->isEditable())
                <form method="POST" action="{{ route('applications.documents.store', $application) }}"
                      enctype="multipart/form-data" class="mt-3 flex flex-wrap items-end gap-3">
                    @csrf
                    <input type="hidden" name="document_type" value="{{ $document->document_type->value }}">

                    <div class="min-w-64 flex-1">
                        <label class="mb-1 block text-xs font-medium text-slate-600">
                            {{ $uploaded ? 'Replace file' : 'Choose file' }}
                        </label>
                        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" required
                               class="block w-full text-sm text-slate-600 file:mr-3 file:rounded file:border-0 file:bg-blue-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-blue-800 hover:file:bg-blue-100">
                        @error('file')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                        @error('document_type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>

                    <button type="submit" class="rounded bg-blue-800 px-4 py-2 text-sm font-medium text-white hover:bg-blue-900">
                        {{ $uploaded ? 'Replace' : 'Upload' }}
                    </button>
                </form>
            @endif
        </section>
    @empty
        <x-empty-state title="No documents are required for this scholarship" message="You can submit without uploading anything." />
    @endforelse
</div>

<div class="mt-6 flex gap-3">
    <a href="{{ route('applications.show', $application) }}" class="rounded border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">
        Back to application
    </a>
</div>
@endsection
