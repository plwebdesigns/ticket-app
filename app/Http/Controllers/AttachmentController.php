<?php

namespace App\Http\Controllers;

use App\Models\Attachment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function show(Attachment $attachment): StreamedResponse
    {
        return Storage::download($attachment->path, $attachment->filename);
    }

    public function destroy(Attachment $attachment): RedirectResponse
    {
        Storage::delete($attachment->path);

        $attachment->delete();

        Inertia::flash('toast', ['type' => 'success', 'message' => __('Attachment deleted.')]);

        return back();
    }
}
