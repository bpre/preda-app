<?php

namespace App\Filament\Resources\CHFMatterResource\RelationManagers;

use Filament\Schemas\Schema;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\MatterGeneratedDocument;
use Illuminate\Database\Eloquent\Builder;
use App\Filament\Resources\CreditResource;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Resources\RelationManagers\RelationManager;

class CreditsRelationManager extends RelationManager
{
    protected static string $relationship = 'credits';

    protected static ?string $title = 'Umowy kredytowe';

    protected static ?string $modelLabel = 'Umowa kredytowa';
    protected static ?string $pluralModelLabel = 'Umowy kredytowe';

    public function form(Schema $schema): Schema
    {
        return CreditResource::form($schema);
    }

    public function table(Table $table): Table
    {
        $table = CreditResource::table($table, $this)
            ->headerActions([
                CreateAction::make()->slideOver()->modalWidth('7xl')->createAnother(false)->modalHeading('Nowa umowa kredytowa'),
            ]);

        if (! $this->shouldShowGeneratedDocumentsTable()) {
            return $table;
        }

        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->with([
                'generatedDocuments' => fn ($documents) => $documents
                    ->orderByDesc('generated_at')
                    ->orderByDesc('created_at'),
            ]))
            ->content(fn () => view('filament.resources.chf-matter-resource.relation-managers.credits-with-generated-documents-table'));
    }

    public function renameGeneratedDocument(string $documentId, string $filename): void
    {
        $filename = trim((string) preg_replace('/\.pdf$/iu', '', $filename));

        if ($filename === '') {
            Notification::make()
                ->danger()
                ->title('Nazwa pliku nie może być pusta')
                ->send();

            return;
        }

        $this->generatedDocument($documentId)->update([
            'filename' => $filename,
        ]);

        Notification::make()
            ->success()
            ->title('Zapisano nazwę pliku')
            ->send();
    }

    public function deleteGeneratedDocument(string $documentId): void
    {
        $this->generatedDocument($documentId)->delete();

        Notification::make()
            ->success()
            ->title('Usunięto wygenerowany dokument')
            ->send();
    }

    public function confirmDeleteGeneratedDocumentAction(): Action
    {
        return Action::make('confirmDeleteGeneratedDocument')
            ->label('Usuń')
            ->color('danger')
            ->link()
            ->size('sm')
            ->requiresConfirmation()
            ->modalHeading('Usunąć wygenerowany dokument?')
            ->modalDescription('Plik zostanie trwale usunięty z listy wygenerowanych dokumentów.')
            ->modalSubmitActionLabel('Usuń')
            ->modalCancelActionLabel('Anuluj')
            ->action(function (array $arguments): void {
                $this->deleteGeneratedDocument($arguments['documentId'] ?? '');
            });
    }

    private function generatedDocument(string $documentId): MatterGeneratedDocument
    {
        return $this->ownerRecord
            ->generatedDocuments()
            ->whereKey($documentId)
            ->firstOrFail();
    }

    private function shouldShowGeneratedDocumentsTable(): bool
    {
        return ! (bool) $this->ownerRecord->is_matter;
    }
}
