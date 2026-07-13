<?php

namespace App\Http\Controllers;

use App\Models\MatterGeneratedDocument;
use Illuminate\Support\Facades\Storage;

class MatterGeneratedDocumentDownloadController extends Controller
{
    public function __invoke(MatterGeneratedDocument $document)
    {
        $disk = Storage::disk($document->disk ?: 'local');

        abort_unless($disk->exists($document->path), 404);

        return $disk->download($document->path, $document->downloadFilename(), [
            'Content-Type' => $document->mime_type ?: 'application/pdf',
        ]);
    }

    public function preview(MatterGeneratedDocument $document)
    {
        $disk = Storage::disk($document->disk ?: 'local');

        abort_unless($disk->exists($document->path), 404);

        return $disk->response($document->path, $document->downloadFilename(), [
            'Content-Type' => $document->mime_type ?: 'application/pdf',
        ]);
    }
}
