<?php

namespace App\Filament\Crm\Widgets;

use App\Filament\Crm\Resources\CHFPotentialMatterResource;
use App\Models\CHFPotentialMatter;
use App\Models\Matter;
use App\Services\Crm\PotentialMatterWorkflowService;
use App\Support\Crm\ClientAcquisitionAccess;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Enums\TextSize;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;
use Illuminate\Database\Eloquent\Builder;

class PotentialMattersRequiringActionWidget extends TableWidget
{
    protected static bool $isLazy = false;

    protected int|string|array $columnSpan = 'full';

    protected static ?int $sort = 10;

    public static function canView(): bool
    {
        $user = auth()->user();

        return ClientAcquisitionAccess::canUse($user)
            && $user?->can('view_any_c::h::f::potential::matter') === true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading('Sprawy wymagające działania')
            ->description('Potencjalne sprawy, w których termin kolejnego działania przypada dzisiaj albo już minął.')
            ->query(fn (): Builder => $this->query())
            ->columns([
                TextColumn::make('label')
                    ->label('Sprawa')
                    ->weight(FontWeight::Bold)
                    ->size(TextSize::Medium)
                    ->searchable()
                    ->description(fn (Matter $record): ?string => $record->lawyer?->name
                        ? 'Referat: '.$record->lawyer->name
                        : null)
                    ->extraAttributes(['class' => 'crm-compact-description-column']),
                TextColumn::make('next_action_key')
                    ->label('Działanie')
                    ->formatStateUsing(fn (?string $state): string => app(PotentialMatterWorkflowService::class)->actionLabel($state))
                    ->badge()
                    ->color('danger')
                    ->description(fn (Matter $record): ?string => $record->next_action_reason)
                    ->extraAttributes(['class' => 'crm-compact-description-column'])
                    ->placeholder('-')
                    ->wrap(),
                TextColumn::make('next_action_due_at')
                    ->label('Termin')
                    ->date()
                    ->sortable()
                    ->color('danger')
                    ->description(fn (Matter $record): ?string => $record->next_action_due_at?->lt(now()->startOfDay())
                        ? 'Po terminie'
                        : 'Dzisiaj')
                    ->extraAttributes(['class' => 'crm-compact-description-column']),
            ])
            ->filters([
                Filter::make('scopeMine')
                    ->toggle()
                    ->default(fn (): bool => auth()->user()?->is_lawyer === true)
                    ->label('Tylko mój referat')
                    ->query(fn (Builder $query): Builder => $query->where('lawyer_id', auth()->id()))
                    ->hidden(fn (): bool => auth()->user()?->is_lawyer !== true),
            ])
            ->recordUrl(fn (Matter $record): ?string => $this->canOpenPotentialMatter()
                ? CHFPotentialMatterResource::getUrl('edit', ['record' => $record], panel: 'crm')
                : null)
            ->defaultSort('next_action_due_at')
            ->paginated([5, 10, 25])
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('Brak spraw wymagających działania')
            ->emptyStateDescription('Na ten moment nie ma przeterminowanych ani dzisiejszych działań do wykonania.');
    }

    private function query(): Builder
    {
        $query = CHFPotentialMatter::query()
            ->with(['currentStage', 'lawyer'])
            ->whereNull('end')
            ->where('is_archived', false)
            ->whereNotNull('next_action_key')
            ->whereNotNull('next_action_due_at')
            ->whereDate('next_action_due_at', '<=', now()->toDateString());

        $user = auth()->user();

        if ($user?->is_lawyer) {
            $query->where('lawyer_id', $user->getKey());
        }

        return $query;
    }

    private function canOpenPotentialMatter(): bool
    {
        return auth()->user()?->can('update_c::h::f::potential::matter') === true;
    }
}
