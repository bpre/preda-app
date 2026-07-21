<?php

namespace App\Filament\Crm\Resources\CHFPotentialMatterResource\RelationManagers;

use App\Models\CrmClientMessage;
use App\Models\MailgunEvent;
use App\Services\Crm\PotentialMatterWorkflowService;
use App\Support\Crm\ClientAcquisitionAccess;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ClientMessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'crmClientMessages';

    protected static ?string $title = 'Wiadomości do klienta';

    protected static ?string $modelLabel = 'Wiadomość do klienta';

    protected static ?string $pluralModelLabel = 'Wiadomości do klienta';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return ClientAcquisitionAccess::canUse()
            && parent::canViewForRecord($ownerRecord, $pageClass);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('subject')
            ->columns([
                TextColumn::make('sent_at')
                    ->label('Wysłano')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                TextColumn::make('action')
                    ->label('Akcja')
                    ->formatStateUsing(fn (?string $state): string => app(PotentialMatterWorkflowService::class)->actionLabel($state))
                    ->badge()
                    ->color('info'),
                TextColumn::make('recipient_email')
                    ->label('Do')
                    ->searchable(),
                TextColumn::make('subject')
                    ->label('Temat')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('mailgun_status')
                    ->label('Status dostarczenia')
                    ->state(fn (CrmClientMessage $record): ?string => MailgunEvent::mostRelevantFrom(
                        $record->mailgunEvents()->get(['id', 'event', 'occurred_at', 'created_at'])
                    )?->event)
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => MailgunEvent::labelFor($state))
                    ->color(fn (?string $state): string => MailgunEvent::colorFor($state))
                    ->placeholder('—'),
                TextColumn::make('crm_workflow_offer_label')
                    ->label('Oferta')
                    ->placeholder('-')
                    ->toggleable(),
                IconColumn::make('default_offer_attached')
                    ->label('Załączona?')
                    ->boolean(),
            ])
            ->defaultSort('sent_at', 'desc')
            ->paginated(false);
    }

    public function isReadOnly(): bool
    {
        return true;
    }
}
