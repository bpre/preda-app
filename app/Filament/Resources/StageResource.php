<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Fieldset;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Actions\EditAction;
use Filament\Actions\DeleteAction;
use App\Filament\Resources\StageResource\Pages\ListStages;
use App\Models\Stage;
use App\Models\Matter;
use App\Support\StageManager;
use Filament\Tables\Table;
use App\Models\TemplateStage;
use Filament\Resources\Resource;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Grouping\Group;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\TextColumn;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\StageResource\Pages;
use App\Filament\Resources\TemplateStageResource;
use Filament\Resources\RelationManagers\RelationManager;
use Malzariey\FilamentDaterangepickerFilter\Filters\DateRangeFilter;
use App\Filament\Resources\CHFMatterResource\RelationManagers\StagesRelationManager;

class StageResource extends Resource
{

    protected static ?int $navigationSort = 5;
    protected static ?string $model = Stage::class;
    protected static ?string $navigationLabel = 'Etapy';
    protected static ?string $modelLabel = 'Etap';
    protected static ?string $pluralModelLabel = 'Etapy';
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

    // protected static ?array $kategorie =
    // [
    //     'Pozyskanie klienta' => 'Pozyskanie klienta',
    //     'Etap przedsądowy' => 'Etap przedsądowy',
    //     'I instancja' => 'I instancja',
    //     'II instancja' => 'II instancja',
    //     'Rozliczenie' => 'Rozliczenie'
    // ];

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Fieldset::make()->schema([

                    TextInput::make('label')
                    ->label('Nazwa')
                    ->required()
                    ->maxLength(255),

                    Select::make('parent')->label('Kategoria')
                        ->options(TemplateStageResource::kategorie())
                        ->native(false)
                        ->required()

                ])->hiddenOn('edit')->label('Nowy etap'),

                Fieldset::make()->schema([
                    DatePicker::make('date')->label('Data'),
                    Toggle::make('is_current')->label('Aktualny etap?')->inline(false)
                ])->extraAttributes([
                    'class' => 'danger'
                ]),

                RichEditor::make('description')->label('Notatka'),

                FileUpload::make('files')
                ->disk('local')
                ->storeFileNamesIn('files_names')
                ->reorderable()
                ->acceptedFileTypes(['application/pdf'])
                ->removeUploadedFileButtonPosition('right')
                ->directory(fn (Get $get): string => 'stages-notes/'.str_replace('-', '', $get('date').'/'))
                ->multiple()
                ->openable()
                ->label('Załączniki')
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->whereNotNull('date'))
            ->recordTitleAttribute('label')
            ->columns([
                TextColumn::make('label')->label('Status'),
                TextColumn::make('stage_id'),
                TextColumn::make('matter.label')->label('Sprawa')->toggleable(),
                // TextColumn::make('parent')->label('Kategoria'),
                ToggleColumn::make('is_current')
                ->updateStateUsing(function (Stage $record, bool $state): bool {
                    if ($state) {
                        return StageManager::setCurrentStage($record->matter, $record, $record->date ?? now()) !== null;
                    }

                    $record->update(['is_current' => false]);

                    if ($record->matter?->current_template_stage_id === $record->stage_id) {
                        $record->matter->forceFill(['current_template_stage_id' => null])->save();
                    }

                    return false;
                })
                ->label('Aktualny etap?'),
                TextColumn::make('date')->label('Data')->placeholder('-'),
            ])
            // ->reorderable('sort')
            ->defaultGroup('parent')
            ->groupingSettingsHidden()
            ->groups([
                Group::make('parent')
                    ->label('')
                    ->collapsible()
                    ->orderQueryUsing(fn (Builder $query, string $direction) => $query->orderBy('sort', $direction)),

            ])
            ->filters([

                SelectFilter::make('label')->options(TemplateStage::orderBy('sort')->get()->pluck('label', 'label'))->native(false)->searchable(),
                DateRangeFilter::make('date')->label('Okres')->withIndicator()

            ])
            // ->headerActions([
            //     CreateAction::make(),
            // ])
            ->recordActions([
                EditAction::make()->iconButton()->slideOver()->modalWidth('7xl')->using(function (Stage $record, array $data): Stage {
                    $wasCurrent = $record->is_current;
                    $isCurrent = (bool) ($data['is_current'] ?? false);

                    if($isCurrent && $record->templateStage?->is_active) {
                        Stage::where('matter_id', $record->matter_id)->whereNot('id', $record->id)
                        ->update(['is_current' => false]);

                        $data['date'] ??= $record->date ?? now()->toDateString();
                    } elseif ($isCurrent) {
                        $data['is_current'] = false;
                        $isCurrent = false;
                    }

                    $record->update($data);

                    if ($isCurrent) {
                        $record->matter?->forceFill(['current_template_stage_id' => $record->stage_id])->save();
                    } elseif ($wasCurrent && ($record->matter?->current_template_stage_id === $record->stage_id)) {
                        $record->matter->forceFill(['current_template_stage_id' => null])->save();
                    }

                    return $record;
                }),
                DeleteAction::make()->iconButton(),
            ])
            ->defaultSort('sort');
            // ->paginated(false);
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
            'index' => ListStages::route('/'),
            // 'create' => Pages\CreateStage::route('/create'),
            // 'edit' => Pages\EditStage::route('/{record}/edit'),
        ];
    }
}
