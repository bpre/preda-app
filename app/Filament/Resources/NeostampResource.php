<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Actions\ViewAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use App\Filament\Resources\NeostampResource\Pages\ListNeostamps;
use App\Filament\Resources\NeostampResource\Pages\CreateNeostamp;
use Filament\Forms;
use Filament\Tables;
use App\Models\Neostamp;
use Filament\Tables\Table;
use Filament\Resources\Resource;
use Filament\Tables\Filters\Filter;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\View;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use App\Infolists\Components\NeostampPreview;
use Filament\Infolists\Components\ImageEntry;
use App\Filament\Resources\NeostampResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\NeostampResource\RelationManagers;

class NeostampResource extends Resource
{
    protected static ?int $navigationSort = 11;
    protected static ?string $slug = 'neoznaczki';
    protected static ?string $model = Neostamp::class;
    protected static ?string $navigationLabel = 'Neoznaczki';
    protected static ?string $modelLabel = 'Neoznaczek';
    protected static ?string $pluralModelLabel = 'Neoznaczki';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static string | \UnitEnum | null $navigationGroup = 'Administracja';

    public static function types()
    {
        return [
            'Polecony (S)' => 'Polecony (S)',
            'Polecony (M)' => 'Polecony (M)',
            'Polecony (L)' => 'Polecony (L)',
            'ZPO (S)' => 'ZPO (S)'
        ];
    }

    public static function typesAdditional()
    {
        return [
            'Złożono w sądzie' => 'Złożono w sądzie',
            // 'Doręczono osobiście' => 'Doręczono osobiście',
            // 'Kurier' => 'Kurier',
            'Portal' => 'Portal Informacyjny',
            'Inny' => 'Inny sposob doręczenia'
        ];
    }
    public static function typesExtended()
    {
        $results = NeostampResource::types();
        $results['Złożono w sądzie'] = 'Złożono w sądzie';
        // $results['Doręczono osobiście'] = 'Doręczono osobiście';
        // $results['Kurier'] = 'Kurier';
        $results['Portal'] = 'Portal Informacyjny';
        $results['Inny'] = 'Inny sposób doręczenia';

        return $results;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                //
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('label')->label('Numer przesyłki'),
                TextEntry::make('type')->label('Typ przesyłki'),
                TextEntry::make('expiration_date')->label('Data ważności')->placeholder('-'),
                ViewEntry::make('status')->view('infolists.entries.neostamp-preview')->label('')
                // ImageEntry::make('../neoznaczki/2024-04-14/00459007730895387472.png')
                //     ->extraImgAttributes([
                //         'src' => ''
                //     ])
                //     ->label('Znaczek')
                //     ->disk('local'),


            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Numer przesyłki')->sortable(),
                TextColumn::make('type')->label('Typ'),
                TextColumn::make('expiration_date')->label('Data ważności')->placeholder('-')->date(),
                TextColumn::make('created_at')->label('Data dodania')->date(),
                IconColumn::make('id')->label('Dostępny')
                    ->icon(fn ($record): string => match ($record->contact_letter()->exists()) {
                        true => 'heroicon-o-x-circle',
                        default => 'heroicon-o-check-circle'
                    })
                    ->color(fn ($record): string => match ($record->contact_letter()->exists()) {
                        true => 'danger',
                        default => 'success'
                    }),
                TextColumn::make('contact_letter.contact.label')->label('Odbiorca')->placeholder('-'),
                TextColumn::make('contact_letter.letter.matter.label')->label('Sprawa')->placeholder('-')

            ])
            ->filters([
                SelectFilter::make('type')
                    ->label('Typ')
                    ->native(false)
                    ->options(NeostampResource::types()),
                Filter::make('is_available')
                    ->schema([
                        Toggle::make('hide')->label('Ukryj zużyte znaczki')
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['hide'],
                                fn (Builder $query, $hide): Builder => $query->doesntHave('contact_letter')
                            );
                    })

            ])
            ->recordActions([
                ViewAction::make()->iconButton()->modalHeading('Podgląd'),
                DeleteAction::make()
                ->hidden(function (DeleteAction $action, Neostamp $record) {
                    return ($record->hasAnyRelation());
                })
                ->iconButton(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(fn (Neostamp $record) => !$record->hasAnyRelation())
            ->defaultSort('expiration_date', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNeostamps::route('/'),
            'create' => CreateNeostamp::route('/create'),
            // 'edit' => Pages\EditNeostamp::route('/{record}/edit'),
        ];
    }
}
