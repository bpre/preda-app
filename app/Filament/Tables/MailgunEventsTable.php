<?php

namespace App\Filament\Tables;

use App\Models\MailgunEvent;
use App\Services\Crm\PotentialMatterWorkflowService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MailgunEventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('occurred_at')
                    ->label('Czas')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->placeholder('—'),

                TextColumn::make('event')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => MailgunEvent::labelFor($state))
                    ->color(fn (?string $state): string => MailgunEvent::colorFor($state)),

                TextColumn::make('recipient_email')
                    ->label('Odbiorca')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('subject')
                    ->label('Temat')
                    ->searchable()
                    ->wrap()
                    ->placeholder('—'),

                TextColumn::make('crmClientMessage.action')
                    ->label('Akcja CRM')
                    ->formatStateUsing(fn (?string $state): string => filled($state)
                        ? app(PotentialMatterWorkflowService::class)->actionLabel($state)
                        : '—')
                    ->badge()
                    ->color('info')
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('url')
                    ->label('Kliknięty link')
                    ->limit(44)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->url(fn (MailgunEvent $record): ?string => $record->url, shouldOpenInNewTab: true)
                    ->placeholder('—')
                    ->toggleable(),

                TextColumn::make('tags')
                    ->label('Tagi')
                    ->state(fn (MailgunEvent $record): ?string => is_array($record->tags) ? implode(', ', $record->tags) : null)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('mailgun_message_id')
                    ->label('ID wiadomości')
                    ->limit(32)
                    ->tooltip(fn (?string $state): ?string => $state)
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('event')
                    ->label('Status')
                    ->options(MailgunEvent::EVENT_LABELS),
            ])
            ->defaultSort('occurred_at', 'desc');
    }
}
