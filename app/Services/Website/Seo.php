<?php

namespace App\Services\Website;

class Seo
{
    protected ?string $title = null;
    protected ?string $description = null;
    protected bool $noSuffix = false;

    // --- Fluent API ---
    public function title(?string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function description(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function noSuffix(bool $state = true): self
    {
        $this->noSuffix = $state;
        return $this;
    }

    // --- Render fragmentu SEO jako HTML ---
    public function render(): string
    {
        $baseTitle   = $this->title ?? config('website.seo.default_title');
        $description = $this->description ?? config('website.seo.default_description');

        $shouldAppend = config('website.seo.append_suffix') && !$this->noSuffix;
        $suffix = trim((string) config('website.seo.title_suffix'));
        $sep    = (string) config('website.seo.title_separator', ' | ');

        $finalTitle = $baseTitle;
        if ($shouldAppend && $suffix !== '') {
            $finalTitle = $baseTitle !== '' ? ($baseTitle . $sep . $suffix) : $suffix;
        }

        return view('partial.seo', [
            'title'       => $finalTitle,
            'description' => $description,
        ])->render();
    }

    // --- Reset stanu (dla porządku na koniec requestu) ---
    public function reset(): void
    {
        $this->title = null;
        $this->description = null;
        $this->noSuffix = false;
    }
}
