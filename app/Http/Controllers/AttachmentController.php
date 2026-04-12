<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function show(Request $request, Attachment $attachment): StreamedResponse
    {
        if ($request->boolean('inline') && $this->isPdfAttachment($attachment)) {
            return Storage::response($attachment->path, $attachment->filename, [], 'inline');
        }

        return Storage::download($attachment->path, $attachment->filename);
    }

    private function isPdfAttachment(Attachment $attachment): bool
    {
        if ($attachment->mime_type === 'application/pdf') {
            return true;
        }

        return str_ends_with(strtolower($attachment->filename), '.pdf');
    }

    public function destroy(Attachment $attachment): RedirectResponse
    {
        Storage::delete($attachment->path);

        $attachment->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Attachment deleted.')]);

        return back();
    }
}
