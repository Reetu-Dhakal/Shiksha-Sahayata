<?php

namespace App\Http\Controllers;

use App\Http\Requests\DocumentUploadRequest;
use App\Models\Application;
use App\Services\ApplicationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ApplicationDocumentController extends Controller
{
    private const DISK = 'local';

    public function __construct(private readonly ApplicationService $applications) {}

    public function index(Request $request, Application $application): View
    {
        $this->applications->assertCanActFor($request->user(), $application->student, $application->scholarship);

        $application->load(['documents', 'scholarship.requiredDocuments']);

        return view('applications.documents', [
            'application' => $application,
            'requiredDocuments' => $application->scholarship->requiredDocuments,
        ]);
    }

    public function store(DocumentUploadRequest $request, Application $application): RedirectResponse
    {
        $this->applications->assertCanActFor($request->user(), $application->student, $application->scholarship);

        if (! $application->isEditable()) {
            return back()->withErrors([
                'file' => __('application.errors.documents_locked_change'),
            ]);
        }

        $documentType = $request->validated('document_type');
        $required = $application->scholarship->requiredDocuments
            ->firstWhere('document_type', $documentType);

        $file = $request->file('file');
        $extension = strtolower($file->getClientOriginalExtension());
        $directory = sprintf('applications/%d/%s', $application->id, $documentType);
        $path = $file->storeAs($directory, uniqid('doc_').'.'.$extension, self::DISK);

        $existing = $application->documents()->where('document_type', $documentType)->first();

        if ($existing !== null) {
            Storage::disk(self::DISK)->delete($existing->path);
            $existing->update([
                'required_document_id' => $required?->id,
                'uploaded_by_user_id' => $request->user()->id,
                'original_filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
                'status' => 'UPLOADED',
            ]);
        } else {
            $application->documents()->create([
                'required_document_id' => $required?->id,
                'uploaded_by_user_id' => $request->user()->id,
                'document_type' => $documentType,
                'original_filename' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
                'status' => 'UPLOADED',
            ]);
        }

        return back()->with('status', __('application.flash.document_uploaded'));
    }

    public function download(Request $request, Application $application, int $document): StreamedResponse
    {
        $this->applications->assertCanActFor($request->user(), $application->student, $application->scholarship);

        $file = $application->documents()->findOrFail($document);

        abort_unless(Storage::disk(self::DISK)->exists($file->path), 404);

        return Storage::disk(self::DISK)->download($file->path, $file->original_filename);
    }

    public function destroy(Request $request, Application $application, int $document): RedirectResponse
    {
        $this->applications->assertCanActFor($request->user(), $application->student, $application->scholarship);

        if (! $application->isEditable()) {
            return back()->withErrors([
                'file' => __('application.errors.documents_locked_remove'),
            ]);
        }

        $file = $application->documents()->findOrFail($document);
        Storage::disk(self::DISK)->delete($file->path);
        $file->delete();

        return back()->with('status', __('application.flash.document_removed'));
    }
}
