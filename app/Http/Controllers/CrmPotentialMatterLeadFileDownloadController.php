<?php

namespace App\Http\Controllers;

use App\Models\CHFPotentialMatter;
use App\Models\Website\Lead;
use App\Support\Website\LeadFileNames;
use App\Support\Website\LeadFileStorage;
use Illuminate\Http\Request;

class CrmPotentialMatterLeadFileDownloadController extends Controller
{
    public function __invoke(Request $request, CHFPotentialMatter $matter, int $fileIndex, LeadFileStorage $storage)
    {
        abort_unless($request->user()?->can('view', $matter), 403);

        $lead = $matter->sourceWebsiteLead;

        abort_unless($lead instanceof Lead, 404);

        $path = collect($lead->files ?? [])
            ->filter(fn ($file): bool => is_string($file) && filled($file))
            ->values()
            ->get($fileIndex);

        abort_unless(is_string($path) && filled($path), 404);

        $resolvedPath = $storage->resolvePath($path);

        abort_unless($resolvedPath, 404);

        return response()->download($resolvedPath, LeadFileNames::downloadName($lead, $path, $fileIndex));
    }
}
