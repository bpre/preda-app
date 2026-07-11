<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Support\Enums\TextSize;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\ContactResource\Pages\ListContacts;
use App\Filament\Resources\ContactResource\Pages\EditContact;
use App\Models\Credit;
use App\Models\Letter;
use App\Models\Matter;
use App\Models\Contact;
use App\Models\Lawsuit;
use Filament\Tables\Table;
use App\Models\ContactDeal;
use App\Models\Departament;
use App\Models\ContactCredit;
use App\Models\ContactLetter;
use Filament\Resources\Resource;
use Filament\Forms\Components\Select;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Columns\TextColumn;
use Filament\Notifications\Notification;
use Filament\Tables\Filters\SelectFilter;
use App\Filament\Resources\ContactResource\Pages;
use Filament\Resources\RelationManagers\RelationGroup;
use App\Filament\Resources\ContactResource\RelationManagers\DepartamentsRelationManager;
use App\Filament\Resources\ContactResource\RelationManagers\CourtLawsuitsRelationManager;
use App\Filament\Resources\ContactResource\RelationManagers\JudgeLawsuitsRelationManager;
use App\Filament\Resources\ContactResource\RelationManagers\SenderLettersRelationManager;
use App\Filament\Resources\ContactResource\RelationManagers\ContactCreditsRelationManager;
use App\Filament\Resources\ContactResource\RelationManagers\ContactLawfirmRelationManager;
use App\Filament\Resources\ContactResource\RelationManagers\LawfirmContactsRelationManager;
use App\Filament\Resources\ContactResource\RelationManagers\RecipientLettersRelationManager;
use App\Filament\Resources\ContactResource\RelationManagers\FormerBankCreditsRelationManager;
use App\Filament\Resources\ContactResource\RelationManagers\CurrentBankCreditsRelationManager;
use App\Filament\Resources\ContactResource\RelationManagers\OpponentLawyerMattersRelationManager;
use App\Filament\Resources\ContactResource\RelationManagers\OpponentLawfirmMattersRelationManager;

class ContactResource extends Resource
{
    protected static ?int $navigationSort = 1;
    protected static ?string $slug = 'kontakty';
    protected static ?string $model = Contact::class;
    protected static ?string $recordTitleAttribute = 'label';
    protected static ?string $navigationLabel = 'Kontakty';
    protected static ?string $modelLabel = 'Kontakty';
    protected static ?string $pluralModelLabel = 'Kontakty';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static bool $shouldRegisterNavigation = false;

    protected function shouldPersistTableColumnSearchInSession(): bool
    {
        return true;
    }
    protected function shouldPersistTableFiltersInSession(): bool
    {
        return true;
    }
    protected function shouldPersistTableSearchInSession(): bool
    {
        return true;
    }
    protected function shouldPersistTableSortInSession(): bool
    {
        return true;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components(contactForm());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([

                TextColumn::make('sort_name')
                    ->icon(fn (Contact $record): string => match ($record->type) {
                        'organizacja' => 'heroicon-o-building-office-2',
                        'osoba' => 'heroicon-o-user',
                        default => 'heroicon-o-user',
                    })
                    ->iconColor(fn (Contact $record): string => match ($record->category) {
                        'Bank' => 'gray',
                        'Pełnomocnik' => 'success',
                        'Kredytobiorca' => 'secondary',
                        'Kancelaria' => 'yellow',
                        'Sąd' => 'info',
                        default => 'indigo'
                    })
                    ->size(TextSize::Medium)
                    ->weight(FontWeight::Bold)
                    ->searchable()
                    ->sortable()
                    ->label('Nazwa'),
                TextColumn::make('category')->label('Kategoria')->toggleable(),
                TextColumn::make('email')->label('E-mail')->toggleable()->placeholder('-'),
                TextColumn::make('phone')->label('Telefon')->toggleable()->placeholder('-'),

            ])
            ->filters([
                SelectFilter::make('category')->label('Kategoria')->options(Contact::S_CATEGORIES)->native(false)
            ])
            ->recordActions([
                Action::make('Scal kontakt')
                ->hidden(fn () => auth()->user()->role != 'admin')
                ->schema(function (Contact $record) {

                    return [
                        Select::make('correct_contact_id')
                            ->label('Docelowy kontakt')
                            ->searchable()
                            ->options(Contact::whereNot('id', $record->id)->orderBy('sort_name')->get()->pluck('sort_name', 'id'))
                    ];
                    }
                )
                ->action(function (Contact $record, $data) {

                    $c = $data['correct_contact_id'];
                    // $record->id

                    Contact::where('lawfirm_id', $record->id)->update(['lawfirm_id' => $c]);
                    Matter::where('opponent_lawyer_id', $record->id)->update(['opponent_lawyer_id' => $c]);
                    Matter::where('opponent_lawfirm_id', $record->id)->update(['opponent_lawfirm_id' => $c]);
                    Matter::where('opponent_departament_id', $record->id)->update(['opponent_departament_id' => $c]);
                    Credit::where('former_bank', $record->id)->update(['former_bank' => $c]);
                    Credit::where('current_bank', $record->id)->update(['current_bank' => $c]);
                    Letter::where('sender_id', $record->id)->update(['sender_id' => $c]);
                    Lawsuit::where('court_id', $record->id)->update(['court_id' => $c]);
                    Lawsuit::where('judge_id', $record->id)->update(['judge_id' => $c]);
                    Departament::where('contact_id', $record->id)->update(['contact_id' => $c]);
                    ContactCredit::where('contact_id', $record->id)->update(['contact_id' => $c]);
                    ContactDeal::where('contact_id', $record->id)->update(['contact_id' => $c]);
                    ContactLetter::where('contact_id', $record->id)->update(['contact_id' => $c]);

                    Contact::where('id', $record->id)->delete();

                    Notification::make()->title('Scalono kontakt.')->success()->send();

                }),
                EditAction::make()->iconButton()->modalHeading('Edytuj kontakt'),
                DeleteAction::make()
                    ->hidden(function (DeleteAction $action, Contact $record) {
                        return ($record->hasAnyRelation());
                    })
                    ->iconButton(),
            ])
            ->defaultSort('sort_name');
    }

    // public static function rel () {
    //     dd($livewire);
    // }

    public static function getRelations(): array
    {

        return [
            RelationGroup::make('Powiązania', [
                OpponentLawyerMattersRelationManager::class,
                OpponentLawfirmMattersRelationManager::class,
                ContactLawfirmRelationManager::class,
                LawfirmContactsRelationManager::class,
                FormerBankCreditsRelationManager::class,
                CurrentBankCreditsRelationManager::class,
                ContactCreditsRelationManager::class,
                JudgeLawsuitsRelationManager::class,
                CourtLawsuitsRelationManager::class,
                RecipientLettersRelationManager::class,
                SenderLettersRelationManager::class
            ]),
            DepartamentsRelationManager::class,
        ];

    }

    public static function getPages(): array
    {
        return [
            'index' => ListContacts::route('/'),
            // 'create' => Pages\CreateContact::route('/create'),
            'edit' => EditContact::route('/{record}/edit'),
        ];
    }
}
