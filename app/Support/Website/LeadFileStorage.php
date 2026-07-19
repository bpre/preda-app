<?php

namespace App\Support\Website;

use Illuminate\Support\Facades\Storage;

class LeadFileStorage
{
    public function exists(string $path): bool
    {
        return $this->resolvePath($path) !== null;
    }

    public function size(string $path): ?int
    {
        $resolvedPath = $this->resolvePath($path);

        return $resolvedPath ? filesize($resolvedPath) : null;
    }

    public function resolvePath(string $path): ?string
    {
        if (! $this->isSafeRelativePath($path)) {
            return null;
        }

        if (Storage::disk('local')->exists($path)) {
            return Storage::disk('local')->path($path);
        }

        foreach ($this->legacyCandidates($path) as $candidate) {
            if (is_file($candidate) && is_readable($candidate)) {
                return $candidate;
            }
        }

        return null;
    }

    private function isSafeRelativePath(string $path): bool
    {
        return filled($path)
            && ! str_contains($path, '..')
            && ! str_starts_with($path, '/')
            && ! str_starts_with($path, '\\');
    }

    /**
     * @return array<int, string>
     */
    private function legacyCandidates(string $path): array
    {
        return [
            storage_path('app/private/'.$path),
            storage_path('app/'.$path),
            storage_path('app/public/'.$path),
            public_path('storage/'.$path),
        ];
    }
}
