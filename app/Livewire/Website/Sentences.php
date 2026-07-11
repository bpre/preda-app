<?php

namespace App\Livewire\Website;

use Livewire\Component;
use App\Models\Website\Bank;
use Livewire\WithPagination;
use App\Models\Website\Contact;
use App\Models\Website\Sentence;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use App\Enums\Website\ContactCategories;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema as SchemaFacade;
use Filament\Schemas\Concerns\InteractsWithSchemas;

class Sentences extends Component implements HasSchemas
{
    use InteractsWithSchemas;
    use WithPagination;

    private const CURRENCY_LISTING_CATEGORIES = [
        'kredyty-euro' => 'EUR',
        'kredyty-frankowe' => 'CHF',
    ];

    public $search = '';
    public $more = false;
    public $more_url = null;
    public $show_all = false;
    public $dark = false;
    public $is_paid_off = false;
    public ?int $relatedBankId = null;

    public ?string $category = null;

    public ?string $currency = null;
    public ?string $slug = null;
    public $bank_id = null;
    public $court_id = null;
    public bool $filtersVisible = false;
    public ?array $filterData = [
        'bank_id' => null,
        'currency' => null,
        'court_id' => null,
    ];

    public function mount()
    {
        if (request()->segment(1) === 'wyroki') {
            $this->category = request()->segment(2);
            $this->slug = request()->segment(3);
        }

        $this->filterData = array_replace($this->emptyFilterData(), $this->filterData ?? []);

        if (filled($this->bank_id)) {
            $this->filterData['bank_id'] = $this->bank_id;
        }

        if (filled($this->currency)) {
            $this->filterData['currency'] = $this->currency;
        }

        if (filled($this->court_id)) {
            $this->filterData['court_id'] = $this->court_id;
        }

        if ($currencyListingCurrency = $this->currencyListingCurrency()) {
            $this->currency = $currencyListingCurrency;
            $this->filterData['currency'] = $currencyListingCurrency;
        }

        $this->filtersForm->fill($this->filterData);
    }

    public function updated(string $property): void
    {
        if ($property === 'filterData.currency' && $this->shouldLeaveCurrencyListing()) {
            $this->redirectRoute('wyroki');

            return;
        }

        if ($property === 'search' || str_starts_with($property, 'filterData.')) {
            $this->resetPage();
        }
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('bank_id')
                    ->label('Bank')
                    ->placeholder('Wszystkie banki')
                    ->options(fn (): array => $this->bankFilterOptions())
                    ->searchable()
                    ->native(false)
                    ->live(),

                Select::make('currency')
                    ->label('Waluta')
                    ->placeholder('Wszystkie waluty')
                    ->options(fn (): array => $this->currencyFilterOptions())
                    ->native(false)
                    ->live(),

                Select::make('court_id')
                    ->label('Sąd')
                    ->placeholder('Wszystkie sądy')
                    ->options(fn (): array => $this->courtFilterOptions())
                    ->searchable()
                    ->native(false)
                    ->live(),
            ])
            ->columns([
                'default' => 1,
                'lg' => 3,
            ])
            ->statePath('filterData');
    }

    public function toggleFilters(): void
    {
        $this->filtersVisible = ! $this->filtersVisible;
    }

    public function clearFilters(): void
    {
        $this->search = '';
        $this->bank_id = null;
        $this->currency = null;
        $this->court_id = null;
        $this->filterData = $this->emptyFilterData();
        $this->filtersForm->fill($this->filterData);

        $this->resetPage();

        if ($this->isCurrencyListing()) {
            $this->redirectRoute('wyroki');
        }
    }

    private function sentencesQuery(): Builder
    {
        $search = trim((string) $this->search);

        if ($this->category == 'splacone') {
            $this->is_paid_off = true;
        }

        $is_paid_off = $this->is_paid_off;

        $bankIdFilter = $this->filterValue('bank_id');
        $currency = $this->filterValue('currency');
        $courtIdFilter = $this->filterValue('court_id');
        $category = $this->category;
        $slug = $this->slug;

        $court = null;
        $judge = null;
        $bank = null;

        if ($category && $slug) {

            if ($category == 'sad') {
                $court = $this->wherePublishedIfAvailable(Contact::query())
                    ->where('category', ContactCategories::SAD->value)
                    ->where('slug', $slug)
                    ->firstOrFail();
            }

            if ($category == 'sedzia') {
                $judge = $this->wherePublishedIfAvailable(Contact::query())
                    ->where('category', ContactCategories::SEDZIA->value)
                    ->where('slug', $slug)
                    ->firstOrFail();
            }

            if ($category == 'bank') {
                $bank = $this->wherePublishedIfAvailable(Bank::query())
                    ->where('slug', $slug)
                    ->firstOrFail();
            }

        }

        $query = $this->publishedVisibleSentencesQuery()
            ->with([
                'court' => fn ($query) => $this->wherePublishedIfAvailable($query),
                'judge' => fn ($query) => $this->wherePublishedIfAvailable($query),
                'bank',
                'bank_previously',
            ]);

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search) {
                $query->where('label', 'like', '%' . $search . '%')
                    ->orWhere('sign', 'like', '%' . $search . '%')
                    ->orWhereHas('judge', function (Builder $query) use ($search) {
                        $this->wherePublishedIfAvailable($query)
                            ->where('label', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('bank', function (Builder $query) use ($search) {
                        $query->where(function (Builder $query) use ($search) {
                            $query->where('label', 'like', '%' . $search . '%')
                                ->orWhere('bank', 'like', '%' . $search . '%');
                        });
                    })
                    ->orWhereHas('bank_previously', function (Builder $query) use ($search) {
                        $query->where(function (Builder $query) use ($search) {
                            $query->where('label', 'like', '%' . $search . '%')
                                ->orWhere('bank', 'like', '%' . $search . '%');
                        });
                    });
            });
        }

        if (filled($bankIdFilter)) {
            $bankId = (int) $bankIdFilter;

            $query->where(function (Builder $query) use ($bankId) {
                $query->whereHas('bank', function (Builder $query) use ($bankId) {
                    $query->whereKey($bankId);
                })->orWhereHas('bank_previously', function (Builder $query) use ($bankId) {
                    $query->whereKey($bankId);
                });
            });
        }

        if (filled($currency)) {
            $query->where('currency', $currency);
        }

        if (filled($courtIdFilter)) {
            $courtId = (int) $courtIdFilter;

            $query->whereHas('court', function (Builder $query) use ($courtId) {
                $this->wherePublishedIfAvailable($query)
                    ->whereKey($courtId);
            });
        }

        if ($is_paid_off) {
            $query->where('is_paid_off', true);
        }

        if ($category !== null) {
            if ($category === 'sad' && $court) {
                $query->whereHas('court', function (Builder $query) use ($court) {
                    $this->wherePublishedIfAvailable($query)
                        ->whereKey($court->id);
                });
            }
            if ($category === 'sedzia' && $judge) {
                $query->whereHas('judge', function (Builder $query) use ($judge) {
                    $this->wherePublishedIfAvailable($query)
                        ->whereKey($judge->id);
                });
            }
            if ($category === 'bank' && $bank) {
                $query->where(function (Builder $query) use ($bank) {
                    $query->whereHas('bank', function (Builder $query) use ($bank) {
                        $this->wherePublishedIfAvailable($query)
                            ->whereKey($bank->id);
                    })->orWhereHas('bank_previously', function (Builder $query) use ($bank) {
                        $this->wherePublishedIfAvailable($query)
                            ->whereKey($bank->id);
                    });
                });
            }
        }

        return $query->orderBy('sentence_date', 'desc');
    }

    private function bankFilterOptions(): array
    {
        $visibleSentencesQuery = $this->publishedVisibleSentencesQuery();

        return Bank::query()
            ->where(function (Builder $query) use ($visibleSentencesQuery) {
                $query->whereHas('sentences', fn (Builder $query) => $query->whereIn(
                    $query->qualifyColumn('id'),
                    (clone $visibleSentencesQuery)->select('id')
                ))->orWhereHas('sentences_prev', fn (Builder $query) => $query->whereIn(
                    $query->qualifyColumn('id'),
                    (clone $visibleSentencesQuery)->select('id')
                ));
            })
            ->orderBy('label')
            ->pluck('label', 'id')
            ->toArray();
    }

    private function currencyFilterOptions(): array
    {
        return $this->publishedVisibleSentencesQuery()
            ->whereNotNull('currency')
            ->where('currency', '!=', '')
            ->distinct()
            ->orderBy('currency')
            ->pluck('currency', 'currency')
            ->toArray();
    }

    private function courtFilterOptions(): array
    {
        $visibleSentencesQuery = $this->publishedVisibleSentencesQuery();
        $query = $this->wherePublishedIfAvailable(Contact::query());

        return $query
            ->where('category', ContactCategories::SAD->value)
            ->whereHas('court_sentences', fn (Builder $query) => $query->whereIn(
                $query->qualifyColumn('id'),
                (clone $visibleSentencesQuery)->select('id')
            ))
            ->orderBy('organization')
            ->pluck('organization', 'id')
            ->toArray();
    }

    private function publishedVisibleSentencesQuery(): Builder
    {
        $query = Sentence::query()
            ->where('is_published', true);

        $this->whereHasPublishedRelationIfAvailable($query, 'court');
        $this->whereHasPublishedRelationIfAvailable($query, 'judge');

        return $query;
    }

    private function relatedBankSentences(): Collection
    {
        $sentences = collect();
        $excludedIds = [];
        $bankId = (int) $this->relatedBankId;

        $append = function (Builder $query) use (&$sentences, &$excludedIds): void {
            $remaining = 4 - $sentences->count();

            if ($remaining <= 0) {
                return;
            }

            if ($excludedIds !== []) {
                $query->whereNotIn($query->getModel()->getQualifiedKeyName(), $excludedIds);
            }

            $nextSentences = $query->limit($remaining)->get();

            $sentences = $sentences->concat($nextSentences);
            $excludedIds = $sentences->pluck('id')->all();
        };

        $append($this->sentencesQuery()->where('bank_previously_id', $bankId));
        $append($this->sentencesQuery()->where('bank_id', $bankId));
        $append($this->sentencesQuery()
            ->reorder('created_at', 'desc')
            ->orderByDesc('id')
            ->where(function (Builder $query) use ($bankId) {
                $query->whereNull('bank_previously_id')
                    ->orWhere('bank_previously_id', '!=', $bankId);
            })
            ->where(function (Builder $query) use ($bankId) {
                $query->whereNull('bank_id')
                    ->orWhere('bank_id', '!=', $bankId);
            }));

        return $sentences->values();
    }

    private function filtersViewData(): array
    {
        $activeFiltersCount = $this->activeFiltersCount();

        return [
            'showSentenceFilters' => ! $this->more && $this->shouldShowSentenceFilters(),
            'activeFiltersCount' => $activeFiltersCount,
            'hasActiveFilters' => $activeFiltersCount > 0,
        ];
    }

    private function emptyFilterData(): array
    {
        return [
            'bank_id' => null,
            'currency' => null,
            'court_id' => null,
        ];
    }

    private function filterValue(string $key): mixed
    {
        return $this->filterData[$key] ?? null;
    }

    private function activeFiltersCount(): int
    {
        return collect([
            $this->search,
            $this->filterValue('bank_id'),
            $this->filterValue('currency'),
            $this->filterValue('court_id'),
        ])->filter(fn (mixed $value): bool => filled($value))->count();
    }

    private function isCurrencyListing(): bool
    {
        return $this->currencyListingCurrency() !== null;
    }

    private function currencyListingCurrency(): ?string
    {
        return self::CURRENCY_LISTING_CATEGORIES[$this->category] ?? null;
    }

    private function shouldLeaveCurrencyListing(): bool
    {
        return $this->isCurrencyListing()
            && $this->filterValue('currency') !== $this->currencyListingCurrency();
    }

    private function shouldShowSentenceFilters(): bool
    {
        return blank($this->category) || $this->isCurrencyListing();
    }

    private function wherePublishedIfAvailable(Builder|Relation $query): Builder|Relation
    {
        if ($this->modelHasPublishedColumn($this->modelFromQuery($query))) {
            $query->where('is_published', true);
        }

        return $query;
    }

    private function whereHasPublishedRelationIfAvailable(Builder $query, string $relation): Builder
    {
        $relatedModel = $query->getModel()->{$relation}()->getRelated();

        if (! $this->modelHasPublishedColumn($relatedModel)) {
            return $query;
        }

        return $query->whereHas($relation, fn (Builder $query) => $query->where('is_published', true));
    }

    private function modelFromQuery(Builder|Relation $query): Model
    {
        return $query instanceof Relation
            ? $query->getRelated()
            : $query->getModel();
    }

    private function modelHasPublishedColumn(Model $model): bool
    {
        static $cache = [];

        $connection = $model->getConnectionName() ?: config('database.default');
        $table = $model->getTable();
        $cacheKey = $connection . '.' . $table;

        return $cache[$cacheKey] ??= SchemaFacade::connection($connection)->hasColumn($table, 'is_published');
    }

    public function render()
    {
        $query = $this->sentencesQuery();

        if ($this->more) {
            // Gdy wyświetlamy więcej, pobieramy tylko 4 elementy bez paginacji
            $sentences = filled($this->relatedBankId)
                ? $this->relatedBankSentences()
                : $query->limit(4)->get();

            return view('livewire.website.sentences', array_merge([
                'sentences' => $sentences,
                'links' => null // Brak linków paginacji w trybie "more"
            ], $this->filtersViewData()));
        } elseif ($this->show_all) {

            $sentences = $query->get();

            return view('livewire.website.sentences', array_merge([
                'sentences' => $sentences
            ], $this->filtersViewData()));

        } else {
            // Standardowa paginacja

            $sentences = $query->paginate(10);

            return view('livewire.website.sentences', array_merge([
                'sentences' => $sentences, // Pobierz elementy z paginator
                'links' => $sentences // Linki paginacji
            ], $this->filtersViewData()));
        }
    }
}
