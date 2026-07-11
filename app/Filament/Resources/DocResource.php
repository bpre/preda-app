<?php
/*
namespace App\Filament\Resources;

use App\BP\documentParser;
use App\Models\Doc;
use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Credit;
use App\Models\Matter;
use App\Models\Contact;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use App\Models\Doctemplate;
use Filament\Resources\Resource;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Builder;
use FilamentTiptapEditor\TiptapEditor;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;
use App\Forms\Components\MentionsRichEditor;
use FilamentTiptapEditor\Enums\TiptapOutput;
use App\Filament\Resources\DocResource\Pages;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\DocResource\RelationManagers;

class DocResource extends Resource
{
    protected static ?string $slug = 'dokumenty';
    protected static ?string $model = Doc::class;
    protected static ?string $navigationLabel = 'Dokumenty';
    protected static ?string $modelLabel = 'Dokument';
    protected static ?string $pluralModelLabel = 'Dokumenty';
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static bool $hasTitleCaseModelLabel = false;
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

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('label')->label('Nazwa'),

                Select::make('template_id')
                    ->label('Szablon')
                    ->options(Doctemplate::get()->pluck('label', 'id'))
                    ->searchable()
                    ->required()
                    ->live()
                    ->suffixAction(
                        Action::make('Wczytaj szablon')
                            ->disabled(fn (Get $get) => $get('template_id') === null)
                            ->modalWidth('md')
                            ->icon('heroicon-m-arrow-up-tray')
                            ->requiresConfirmation()
                            ->modalHeading('Na pewno wczytać szablon?')
                            ->modalDescription('Wczytanie szablonu spowoduje, że aktualna treść dokumentu zostanie zastąpiona.')
                            ->action(function (Set $set, Get $get) {
                                $template = Doctemplate::where('id', $get('template_id'))->first();

                                $n = new documentParser;
                                $set('body',  $n->parseData($template->body, $get('credit_id')));
                        })
                    ),

                // Action::make('Wczytaj szablon')
                //     ->modalWidth('md')
                //     ->form([

                //     ])
                //     ->requiresConfirmation()
                //     ->modalDescription('Wczytanie szablonu spowoduje, że aktualna treść dokumentu zostanie zastąpiona.')
                //     ->action(function (Set $set) {
                //         $set('body', 'abx...');
                // }),

                Hidden::make('author_id')
                    ->default(auth()->user()->id),

                Select::make('matter_id')
                    ->label('Sprawa')
                    ->options(Matter::where('is_archived', 0)->orderBy('label')->get()->pluck('label', 'id'))
                    ->live()
                    ->searchable()
                    ->afterStateUpdated(function (Set $set) {
                        $set('credit_id', null);
                    }),

                Select::make('credit_id')
                    ->label('Umowa kredytowa')
                    ->options(function (Get $get) {

                        if($get('matter_id')) {
                            // $matter = Matter::find($get('matter_id'));
                            // return $matter->credits->pluck('number', 'id')->toArray();
                            $n = Credit::where('matter_id', $get('matter_id'))->get()->pluck('date', 'id')->toArray();
                            return $n;
                        }

                    })
                    // ->disabled(fn (Get $get) => $get('matter_id') === null)
                    ->native(false),

                    // TiptapEditor::make('body')
                    //     ->profile('default')
                    //     ->output(TiptapOutput::Html) // optional, change the format for saved data, default is html
                    //     ->maxContentWidth('5xl')
                    //     ->required(),

                    MentionsRichEditor::make('body')
                        ->label('')
                        ->columnSpan(2)

                    /*
                Builder::make('body')
                    ->label('Treść dokumentu')
                    ->columnSpan(2)
                    ->collapsible()
                    ->blockNumbers(false)
                    ->blocks([

                        Builder\Block::make('sekcje')
                            ->label(function (?array $state): string {
                                if ($state === null) {
                                    return 'Nowa sekcja';
                                }

                                if($state['section_name']) {
                                    return substr(strip_tags(str_replace('&nbsp;', '', $state['section_name'])), 0, 50);
                                } else {
                                    return 'Nowa sekcja';
                                }

                            })
                            ->schema([

                                TextInput::make('section_name')->label('Nazwa sekcji')->live(),
                                Builder::make('sekcja')
                                    ->label('')
                                    ->collapsible()
                                    ->blocks([
                                        Builder\Block::make('paragraf')
                                            ->schema([
                                                // RichEditor::make('content')
                                                MentionsRichEditor::make('content')
                                                    ->label('')
                                                    ->mentionsItems(
                                                        Contact::all()
                                                            ->map(
                                                                fn (Contact $user) => [
                                                                    'key' => $user->sort_name,
                                                                    'link' => $user->id
                                                                ])
                                                            ->toArray()
                                                    )
                                            ])
                                    ])


                            ])
                    ])

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')->label('Nazwa')
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make()->iconButton(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListDocs::route('/'),
            'create' => Pages\CreateDoc::route('/create'),
            'edit' => Pages\EditDoc::route('/{record}/edit'),
        ];
    }
}
*/
