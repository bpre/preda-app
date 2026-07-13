<?php

namespace App\Filament\Search;

use App\Filament\Crm\Resources\CHFPotentialMatterResource;
use App\Models\CHFPotentialMatter;
use App\Models\User;
use Filament\GlobalSearch\GlobalSearchResult;
use Filament\GlobalSearch\GlobalSearchResults;
use Filament\GlobalSearch\Providers\Contracts\GlobalSearchProvider;
use Filament\GlobalSearch\Providers\DefaultGlobalSearchProvider;
use Illuminate\Support\Collection;

class KancelariaGlobalSearchProvider implements GlobalSearchProvider
{
    public function getResults(string $query): ?GlobalSearchResults
    {
        $results = app(DefaultGlobalSearchProvider::class)->getResults($query)
            ?? GlobalSearchResults::make();

        $potentialMatters = $this->getPotentialMatterResults($query);

        if ($potentialMatters->isNotEmpty()) {
            $results->category('Potencjalne sprawy', $potentialMatters);
        }

        return $results;
    }

    private function canSearchPotentialMatters(): bool
    {
        $user = auth()->user();

        return $user instanceof User
            && $user->canAccessPredaPanel('crm')
            && $user->can('view_any_c::h::f::potential::matter')
            && $user->can('update_c::h::f::potential::matter');
    }

    /**
     * @return Collection<int, GlobalSearchResult>
     */
    private function getPotentialMatterResults(string $query): Collection
    {
        if (! $this->canSearchPotentialMatters()) {
            return collect();
        }

        $terms = $this->searchTerms($query);

        if ($terms === []) {
            return collect();
        }

        $query = CHFPotentialMatter::query()
            ->with(['currentStage', 'lawyer'])
            ->where('is_archived', 0);

        foreach ($terms as $term) {
            $query->where(function ($query) use ($term): void {
                $query
                    ->where('label', 'like', "%{$term}%")
                    ->orWhereHas('lawsuits', fn ($query) => $query
                        ->where('signature', 'like', "%{$term}%"));
            });
        }

        return $query
            ->limit(10)
            ->get()
            ->map(fn (CHFPotentialMatter $matter): GlobalSearchResult => new GlobalSearchResult(
                title: $matter->label,
                url: CHFPotentialMatterResource::getUrl('edit', ['record' => $matter], panel: 'crm'),
                details: array_filter([
                    'Panel' => 'CRM',
                    'Etap' => $matter->currentStage?->label,
                    'Referat' => $matter->lawyer?->name,
                ], filled(...)),
            ));
    }

    /**
     * @return array<int, string>
     */
    private function searchTerms(string $query): array
    {
        return array_values(array_filter(
            preg_split('/(\s|\x{3164}|\x{1160})+/u', trim($query)) ?: [],
            filled(...),
        ));
    }
}
