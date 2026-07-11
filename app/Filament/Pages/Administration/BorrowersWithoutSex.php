<?php

namespace App\Filament\Pages\Administration;

use Filament\Actions\BulkAction;
use App\Models\Contact;
use BezhanSalleh\FilamentShield\Traits\HasPageShield;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Table;

class BorrowersWithoutSex extends Page implements HasForms, HasTable
{
    use HasPageShield;
    use InteractsWithForms;
    use InteractsWithTable;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-user-group';

    protected string $view = 'filament.pages.administration.borrowers-without-sex';

    protected static ?string $navigationLabel = 'Kredytobiorcy bez płci';

    protected static ?string $title = 'Kredytobiorcy bez płci';

    protected static string | \UnitEnum | null $navigationGroup = 'Administracja';

    protected static ?string $navigationParentItem = 'Powiadomienia (pisma)';

    protected static ?int $navigationSort = 6;

    protected static ?string $slug = 'borrower-without-sexes';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Contact::query()
                    ->where('category', 'Kredytobiorca')
                    ->where(function ($query) {
                        $query->whereNull('sex')->orWhere('sex', '');
                    })
            )
            ->columns([
                TextColumn::make('sort_name')
                    ->label('Nazwa')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('first_name')
                    ->label('Imię')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('last_name')
                    ->label('Nazwisko')
                    ->searchable()
                    ->sortable()
                    ->placeholder('-'),

                TextColumn::make('pesel')
                    ->label('PESEL')
                    ->searchable()
                    ->placeholder('-'),

                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->placeholder('-'),

                SelectColumn::make('sex')
                    ->label('Płeć')
                    ->options(Contact::SEX),
            ])
            ->recordActions([])
            ->toolbarActions([
                BulkAction::make('set_female')
                    ->label('Ustaw kobieta')
                    ->icon('heroicon-o-user')
                    ->requiresConfirmation()
                    ->action(fn ($records) => $records->each->update([
                        'sex' => 'K',
                    ])),

                BulkAction::make('set_male')
                    ->label('Ustaw mężczyzna')
                    ->icon('heroicon-o-user')
                    ->requiresConfirmation()
                    ->action(fn ($records) => $records->each->update([
                        'sex' => 'M',
                    ])),
            ])
            ->defaultSort('sort_name')
            ->emptyStateHeading('Brak rekordów')
            ->emptyStateDescription('Wszyscy kredytobiorcy mają uzupełnione pole płci.');
    }
}
