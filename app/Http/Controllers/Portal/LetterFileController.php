<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Models\Letter;
use App\Models\PortalUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class LetterFileController extends Controller
{
    public function preview(string $k, string $date, string $file)
    {
        $path = $this->path($k, $date, $file);
        $letter = $this->letterForCurrentPortalUser($path);
        $filename = $this->filename($letter, $path);

        abort_unless(pathinfo($path, PATHINFO_EXTENSION) === 'pdf', 404);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return view('filepreview/pdf', [
            'title' => $filename,
            'file' => $path,
        ]);
    }

    public function download(string $k, string $date, string $file)
    {
        $path = $this->path($k, $date, $file);
        $letter = $this->letterForCurrentPortalUser($path);

        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path, $this->filename($letter, $path));
    }

    private function letterForCurrentPortalUser(string $path): Letter
    {
        /** @var PortalUser|null $user */
        $user = Auth::guard('portal')->user();

        abort_unless($user?->contact_id, 403);

        return Letter::query()
            ->whereJsonContains('files', $path)
            ->whereHas('matter', fn ($query) => $query
                ->where('category', 'CHF')
                ->where('is_matter', true)
                ->whereHas('contactMatters', fn ($query) => $query->where('contact_id', $user->contact_id)))
            ->firstOrFail();
    }

    private function path(string $k, string $date, string $file): string
    {
        return "{$k}/{$date}/{$file}";
    }

    private function filename(Letter $letter, string $path): string
    {
        return $letter->files_names[$path] ?? basename($path);
    }
}
