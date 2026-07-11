<?php

namespace App\Filament\Resources\CHFMatterResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Actions\CreateAction;
use Filament\Actions\BulkAction;
use Filament\Forms;
use Filament\Tables;
use App\Models\Matter;
use App\Models\Payment;
use App\Models\CHFMatter;
use Filament\Tables\Table;
use Livewire\Attributes\On;
use Filament\Tables\Actions\Action;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\PaymentResource;
use Illuminate\Database\Eloquent\Collection;
use App\Filament\Support\PresetTab;
use App\Filament\AdvancedTables\Concerns\InteractsWithAdvancedTablePresetTabs;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class PaymentsRelationManager extends RelationManager
{
    use InteractsWithAdvancedTablePresetTabs;

    protected static string $relationship = 'payments';

    protected static ?string $title = 'Płatności';

    protected static ?string $modelLabel = 'Płatność';
    protected static ?string $pluralModelLabel = 'Płatności';


    public $update;

    #[On('refresh-payments')]
    public function refreshPaymentsList()
    {
        $this->update = !$this->update;
    }

    public $canView = false;

    // public function getTabs(): array
    // {
    //     return [
    //         'Zapłacone' => PresetTab::make()
    //             ->modifyQueryUsing(fn ($query) => $query->where('is_paid', 1))
    //             ->icon('heroicon-o-check-circle')
    //             ->color('success')
    //             ->favorite()->default(),
    //         'Po terminie' => PresetTab::make()
    //             ->modifyQueryUsing(fn ($query) => $query->where('is_paid', 0)->where('deadline', '<', now()))
    //             ->icon('heroicon-o-calendar-days')
    //             ->color('danger')
    //             ->favorite(),
    //         'Przyszłe' => PresetTab::make()
    //             ->modifyQueryUsing(fn ($query) => $query->where('is_paid', 0)->where('deadline', '>=', now()))
    //             ->icon('heroicon-o-star')
    //             ->color('warning')
    //             ->favorite(),
    //         'Wszystkie' => PresetTab::make()
    //             ->icon('heroicon-o-list-bullet')
    //             ->favorite()->default(),
    //     ];
    // }

    public function form(Schema $schema): Schema
    {
        return PaymentResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return PaymentResource::table($table)
            ->headerActions([
                CreateAction::make()->createAnother(false)->modalHeading('Nowa korespondencja'),
            ])
            ->toolbarActions([
                BulkAction::make('delete')
                    ->label('Usuń zaznaczone')
                    ->color('danger')
                    ->action(fn (Collection $records) => $records->each->delete())
                    ->deselectRecordsAfterCompletion()
                    ->requiresConfirmation()
                    ->modalHeading('Usuń płatności')
                    ->modalSubheading('Na pewno chcesz usunąć płatności?')
                    ->modalButton('Tak, usuń zaznaczone')
            ]);
    }
}
