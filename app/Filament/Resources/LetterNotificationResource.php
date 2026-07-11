<?php

namespace App\Filament\Resources;

use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Carbon;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;
use Filament\Support\Enums\Width;
use Throwable;
use Closure;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Actions\EditAction;
use App\Filament\Resources\LetterNotificationResource\Pages\ListLetterNotifications;
use App\Models\LetterNotification;
use App\Models\LetterNotificationTemplate;
use App\Services\LetterNotificationSender;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\HtmlString;
use Livewire\Component as Livewire;
use App\Filament\Resources\LetterNotificationResource\Pages;

class LetterNotificationResource extends Resource
{
    protected static ?string $model = LetterNotification::class;
    protected static ?string $slug = 'powiadomienia-o-pismach';
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-envelope';

    protected static ?string $navigationLabel = 'Powiadomienia o pismach';

    protected static ?string $modelLabel = 'Powiadomienie o piśmie';

    protected static ?string $pluralModelLabel = 'Powiadomienia o pismach';

    protected static bool $hasTitleCaseModelLabel = false;

    protected static ?string $navigationParentItem = 'Korespondencja';

    protected static ?int $navigationSort = 1;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Dane pisma')
                    ->collapsible()
                    ->collapsed()
                    ->schema([
                        Placeholder::make('letter_info')
                            ->label('')
                            ->content(function ($record) {
                                $letter = $record?->letter;
                                $matter = $letter?->matter;

                                if (! $letter) {
                                    return new HtmlString('<span class="text-gray-500">Brak powiązanego pisma.</span>');
                                }

                                $typeLabel = match ($letter->type) {
                                    'in' => 'Przychodzące',
                                    'out' => 'Wychodzące',
                                    default => $letter->type,
                                };

                                $date = $letter->date
                                    ? Carbon::parse($letter->date)->format('d.m.Y')
                                    : '—';

                                $attachmentsCount = is_array($letter->files) ? count($letter->files) : 0;

                                $rows = [
                                    'Pismo' => $letter->label ?: '—',
                                    'Typ' => $typeLabel,
                                    'Data pisma' => $date,
                                    'Sprawa' => $matter?->label ?: '—',
                                    'Liczba plików' => $attachmentsCount > 0 ? $attachmentsCount : 'brak',
                                ];

                                $html = collect($rows)
                                    ->map(fn ($value, $label) => '<div><strong>' . e($label) . ':</strong> ' . e((string) $value) . '</div>')
                                    ->implode('');

                                return new HtmlString('<div class="space-y-1 text-sm">' . $html . '</div>');
                            }),
                    ])
                    ->columnSpanFull(),

                Group::make([
                    Hidden::make('pending_template_id')
                        ->dehydrated(false),

                    Select::make('template_id')
                        ->label('Szablon wiadomości')
                        ->options(function ($record) {
                            $letterType = $record?->letter?->type;

                            if (! $letterType) {
                                return [];
                            }

                            return LetterNotificationTemplate::query()
                                ->where('is_active', true)
                                ->where('letter_type', $letterType)
                                ->orderBy('sort')
                                ->orderBy('name')
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->getOptionLabelUsing(fn ($value): ?string => filled($value)
                            ? LetterNotificationTemplate::query()->find($value)?->name
                            : null)
                        ->getSelectedRecordUsing(fn (Select $component, $state): ?LetterNotificationTemplate => filled($state)
                            ? LetterNotificationTemplate::query()->find($state)
                            : null)
                        ->searchable()
                        ->preload()
                        ->createOptionForm(fn (Schema $schema, $record): Schema => LetterNotificationTemplateResource::templateForm(
                            $schema,
                            $record?->letter?->type,
                        ))
                        ->createOptionUsing(function (array $data): string {
                            $template = LetterNotificationTemplate::query()->create($data);

                            return (string) $template->getKey();
                        })
                        ->editOptionForm(fn (Schema $schema, $record): Schema => LetterNotificationTemplateResource::templateForm(
                            $schema,
                            $record?->letter?->type,
                        ))
                        ->fillEditOptionActionFormUsing(fn (Select $component): ?array => $component->getSelectedRecord()?->only([
                            'name',
                            'letter_type',
                            'subject',
                            'message',
                            'sort',
                            'is_active',
                        ]))
                        ->updateOptionUsing(function (array $data, Schema $schema, callable $set, ?LetterNotification $record): void {
                            $template = $schema->getRecord();

                            if (! $template instanceof LetterNotificationTemplate) {
                                return;
                            }

                            $template->update($data);

                            if (! $record) {
                                return;
                            }

                            static::applyTemplateToForm($template->getKey(), $record, $set);
                            $set('pending_template_id', null);
                        })
                        ->live()
                        ->afterStateUpdated(function (?string $state, ?string $old, Get $get, callable $set, $record, Livewire $livewire) {
                            if (! $state || ! $record) {
                                $set('pending_template_id', null);

                                return;
                            }

                            if (! static::shouldConfirmTemplateReplacement(
                                subject: $get('subject'),
                                message: $get('message'),
                            )) {
                                static::applyTemplateToForm($state, $record, $set);
                                $set('pending_template_id', null);

                                return;
                            }

                            $set('pending_template_id', $state);
                            $set('template_id', $old);

                            $livewire->dispatch('open-template-replace-confirmation');
                        }),

                    Actions::make([
                        Action::make('confirmTemplateReplacement')
                            ->label('Potwierdź podmianę szablonu')
                            ->icon('heroicon-o-document-duplicate')
                            ->color('warning')
                            ->modalHeading('Zastąpić temat i treść wiadomości?')
                            ->modalDescription('Wybranie szablonu zastąpi aktualny temat i całą treść wiadomości.')
                            ->modalWidth(Width::Medium)
                            ->modalSubmitActionLabel('Zastąp')
                            ->modalCancelActionLabel('Anuluj')
                            ->action(function (Get $get, callable $set, $record) {
                                $pendingTemplateId = $get('pending_template_id');

                                if (! $pendingTemplateId || ! $record) {
                                    return;
                                }

                                static::applyTemplateToForm($pendingTemplateId, $record, $set);
                                $set('pending_template_id', null);
                            }),
                    ])
                        ->extraAttributes([
                            'class' => 'hidden',
                            'x-on:open-template-replace-confirmation.window' => '$nextTick(() => $el.querySelector("button")?.click())',
                        ]),

                    TextInput::make('subject')
                        ->label('Temat')
                        ->required()
                        ->maxLength(255),

                    Textarea::make('message')
                        ->label('Treść')
                        ->required()
                        ->rows(18),

                    Textarea::make('error_message')
                        ->label('Błąd')
                        ->disabled()
                        ->rows(4)
                        ->hidden(fn ($record) => ! $record || blank($record->error_message)),
                ])->columnSpan(1),

                Group::make([
                    TextInput::make('recipient_email')
                        ->label('E-mail odbiorcy')
                        ->disabled()
                        ->placeholder('Brak'),

                    Toggle::make('with_attachments')
                        ->label('Wyślij załączniki')
                        ->live()
                        ->afterStateUpdated(function ($state, callable $set, $record) {
                            if (! $record) {
                                return;
                            }

                            $files = is_array($record->letter?->files) ? array_values(array_filter($record->letter->files)) : [];

                            if (! $state) {
                                $set('selected_attachments', []);
                                return;
                            }

                            $set('selected_attachments', $files);
                        }),

                    CheckboxList::make('selected_attachments')
                        ->label('Wybierz załączniki do wysyłki')
                        ->options(function ($record) {
                            $letter = $record?->letter;

                            if (! $letter) {
                                return [];
                            }

                            $files = is_array($letter->files) ? array_values(array_filter($letter->files)) : [];
                            $fileNames = is_array($letter->files_names) ? $letter->files_names : [];

                            $options = [];

                            foreach ($files as $path) {
                                $path = (string) $path;
                                $name = $fileNames[$path] ?? basename($path);

                                $sizeLabel = '';

                                try {
                                    if (Storage::disk('local')->exists($path)) {
                                        $sizeBytes = (int) Storage::disk('local')->size($path);
                                        $sizeMb = round($sizeBytes / 1024 / 1024, 2);
                                        $sizeLabel = ' (' . $sizeMb . ' MB)';
                                    }
                                } catch (Throwable $e) {
                                }

                                $options[$path] = static::getAttachmentOptionLabel(
                                    path: $path,
                                    name: $name,
                                    sizeLabel: $sizeLabel,
                                );
                            }

                            return $options;
                        })
                        ->allowHtml()
                        ->columns(1)
                        ->afterStateHydrated(function ($state, callable $set, $record) {
                            if (! $record || ! in_array($record->status, [
                                LetterNotification::STATUS_PENDING,
                                LetterNotification::STATUS_MISSING_RECIPIENT,
                            ], true)) {
                                return;
                            }

                            $files = is_array($record->letter?->files) ? array_values(array_filter($record->letter->files)) : [];
                            $selectedAttachments = is_array($state) ? array_values(array_filter($state)) : [];

                            if (count($files) === 0 || count($selectedAttachments) > 0) {
                                return;
                            }

                            $set('with_attachments', true);
                            $set('selected_attachments', $files);
                        })
                        ->live()
                        ->visible(function (Get $get, $record) {
                            $files = is_array($record?->letter?->files) ? array_values(array_filter($record->letter->files)) : [];

                            return $get('with_attachments') && count($files) > 0;
                        })
                        ->afterStateUpdated(function ($state, callable $set) {
                            $set('with_attachments', ! empty($state));
                        })
                        ->rule(function () {
                            return function (string $attribute, $value, Closure $fail) {
                                if (! is_array($value) || count($value) === 0) {
                                    return;
                                }

                                $totalBytes = static::getSelectedAttachmentsTotalBytes($value);
                                $maxBytes = static::getMaxAttachmentsSizeBytes();

                                if ($totalBytes > $maxBytes) {
                                    $totalMb = static::formatBytesToMb($totalBytes);

                                    $fail("Łączny rozmiar wybranych załączników wynosi {$totalMb} MB i przekracza " . LetterNotification::MAX_ATTACHMENTS_SIZE_MB . " MB.");
                                }
                            };
                        }),

                    Placeholder::make('selected_attachments_size_info')
                        ->label('Łączny rozmiar wybranych załączników')
                        ->content(function (Get $get) {
                            $selected = $get('selected_attachments');

                            if (! is_array($selected) || count($selected) === 0) {
                                return new HtmlString('<span class="text-gray-500">Nie wybrano żadnych załączników.</span>');
                            }

                            $totalBytes = static::getSelectedAttachmentsTotalBytes($selected);
                            $totalMb = static::formatBytesToMb($totalBytes);
                            $maxBytes = static::getMaxAttachmentsSizeBytes();

                            if ($totalBytes > $maxBytes) {
                                return new HtmlString(
                                    '<span class="font-bold text-danger-600">Łączny rozmiar wybranych załączników: ' . e((string) $totalMb) . ' MB. Przekracza ' . e((string) LetterNotification::MAX_ATTACHMENTS_SIZE_MB) . ' MB.</span>'
                                );
                            }

                            return new HtmlString(
                                '<span class="text-success-600">Łączny rozmiar wybranych załączników: ' . e((string) $totalMb) . ' MB.</span>'
                            );
                        })
                        ->visible(fn (Get $get) => $get('with_attachments')),

                    Group::make([
                        Placeholder::make('status_label')
                            ->label(new HtmlString('<span style="font-size: 0.75rem; color: #6b7280; font-weight: 500;">Status</span>'))
                            ->content(fn ($record) => new HtmlString(
                                '<span style="font-size: 0.875rem; color: #111827;">'
                                . e($record ? (LetterNotification::STATUS_LABELS[$record->status] ?? $record->status) : '—')
                                . '</span>'
                            )),

                        Placeholder::make('sent_at_info')
                            ->label(new HtmlString('<span style="font-size: 0.75rem; color: #6b7280; font-weight: 500;">Data wysłania</span>'))
                            ->content(fn ($record) => new HtmlString(
                                '<span style="font-size: 0.875rem; color: #111827;">'
                                . e(filled($record?->sent_at) ? $record->sent_at->format('d.m.Y H:i') : '—')
                                . '</span>'
                            )),

                        Placeholder::make('prepared_by_info')
                            ->label(new HtmlString('<span style="font-size: 0.75rem; color: #6b7280; font-weight: 500;">Przygotowane przez</span>'))
                            ->content(fn ($record) => new HtmlString(
                                '<span style="font-size: 0.875rem; color: #111827;">'
                                . e($record?->preparedBy?->name ?: '—')
                                . '</span>'
                            )),
                    ])
                        ->extraAttributes([
                            'style' => 'background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 0.75rem; padding: 0.875rem 1rem;',
                        ]),
                ])->columnSpan(1),
            ])
            ->columns(2);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('letter_summary')
                    ->label('Pismo / sprawa / klient')
                    ->state(fn (LetterNotification $record) => $record->letter?->label)
                    ->searchable(query: function ($query, string $search) {
                        return $query->where(function ($query) use ($search) {
                            $query->whereHas('letter', fn ($query) => $query->where('label', 'like', "%{$search}%"))
                                ->orWhereHas('letter.matter', fn ($query) => $query->where('label', 'like', "%{$search}%"))
                                ->orWhereHas('contact', fn ($query) => $query->where('sort_name', 'like', "%{$search}%"))
                                ->orWhere('recipient_email', 'like', "%{$search}%");
                        });
                    })
                    ->html()
                    ->wrap()
                    ->formatStateUsing(function ($state, LetterNotification $record) {
                        $letterLabel = $record->letter?->label ?: '—';
                        $matterLabel = $record->letter?->matter?->label ?: '—';
                        $letterType = $record->letter?->type;

                        if ($record->status === LetterNotification::STATUS_MISSING_RECIPIENT) {
                            $clientLabel = 'Brak przypisanego odbiorcy';
                        } else {
                            $clientName = $record->contact?->sort_name ?: 'Brak przypisanego odbiorcy';
                            $email = $record->recipient_email;
                            $clientLabel = filled($email) ? $clientName . ' (' . $email . ')' : $clientName;
                        }

                        $typeIcon = match ($letterType) {
                            'in' => '<span class="inline-flex items-center justify-center w-5 font-bold text-danger-600 shrink-0" title="Pismo przychodzące">↓</span>',
                            'out' => '<span class="inline-flex items-center justify-center w-5 font-bold text-success-600 shrink-0" title="Pismo wychodzące">↑</span>',
                            default => '',
                        };

                        return new HtmlString(
                            '<div class="leading-5">'
                                . '<div class="inline-flex items-center gap-2 fi-ta-text-item">'
                                    . $typeIcon
                                    . '<div class="font-bold">' . e($letterLabel) . '</div>'
                                . '</div>'
                                . '<div>' . e($matterLabel) . '</div>'
                                . '<div class="text-xs text-gray-600">' . e($clientLabel) . '</div>'
                            . '</div>'
                        );
                    }),

                TextColumn::make('template.name')
                    ->label('Szablon')
                    ->searchable()
                    ->sortable()
                    ->placeholder('Nie wybrano'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => LetterNotification::STATUS_LABELS[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        LetterNotification::STATUS_FAILED,
                        LetterNotification::STATUS_MISSING_RECIPIENT => 'danger',
                        LetterNotification::STATUS_SENT => 'success',
                        LetterNotification::STATUS_CANCELLED,
                        LetterNotification::STATUS_IGNORED => 'gray',
                        LetterNotification::STATUS_QUEUED,
                        LetterNotification::STATUS_SENDING => 'warning',
                        default => 'info',
                    }),

                IconColumn::make('with_attachments')
                    ->label('Wyślij załączniki')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),

                TextColumn::make('attachments_summary')
                    ->label('Pliki')
                    ->state(fn (LetterNotification $record): int => is_array($record->letter?->files) ? count($record->letter->files) : 0)
                    ->html()
                    ->sortable(false)
                    ->formatStateUsing(function (int $state, LetterNotification $record) {
                        $attachmentsCount = $state;
                        $selectedCount = is_array($record->selected_attachments) ? count(array_filter($record->selected_attachments)) : 0;

                        return new HtmlString(
                            '<div class="leading-5">'
                                . '<div>'
                                    . '<span class="inline-flex items-center justify-center px-2 py-1 text-xs font-medium rounded-md fi-badge fi-color-info gap-x-1 ring-1 ring-inset">'
                                        . e((string) $attachmentsCount) .
                                    '</span>'
                                . '</div>'
                                . '<div class="mt-1 text-xs text-gray-600">'
                                    . 'Wybrane: ' . e((string) $selectedCount) .
                                '</div>'
                            . '</div>'
                        );
                    }),

                TextColumn::make('created_at')
                    ->label('Utworzono')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),

                TextColumn::make('sent_at')
                    ->label('Wysłano')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options(LetterNotification::STATUS_LABELS),

                TernaryFilter::make('with_attachments')
                    ->label('Wyślij załączniki'),

                TernaryFilter::make('has_email')
                    ->label('Ma e-mail')
                    ->queries(
                        true: fn ($query) => $query->whereNotNull('recipient_email')->where('recipient_email', '!=', ''),
                        false: fn ($query) => $query->whereNull('recipient_email')->orWhere('recipient_email', ''),
                        blank: fn ($query) => $query,
                    ),
            ])
            ->recordActions([
                ActionGroup::make([
                    ViewAction::make()
                        ->slideOver()
                        ->modalHeading('Podgląd powiadomienia')
                        ->modalWidth(Width::SevenExtraLarge),

                    EditAction::make()
                        ->label('Przygotuj wiadomość')
                        ->icon('heroicon-o-pencil-square')
                        ->slideOver()
                        ->modalWidth(Width::SevenExtraLarge)
                        ->modalHeading('Przygotuj wiadomość')
                        ->modalSubmitActionLabel('Zapisz szkic')
                        ->extraModalFooterActions(fn (EditAction $action): array => [
                            $action->makeModalSubmitAction('saveAndQueue', arguments: ['queue' => true])
                                ->label('Zapisz i przekaż do wysyłki')
                                ->color('warning')
                                ->icon('heroicon-o-clock'),
                        ])
                        ->visible(fn (LetterNotification $record) => in_array($record->status, [
                            LetterNotification::STATUS_PENDING,
                            LetterNotification::STATUS_DRAFT,
                            LetterNotification::STATUS_FAILED,
                        ], true) && filled($record->contact_id))
                        ->using(function (EditAction $action, LetterNotification $record, array $data, array $arguments, Livewire $livewire): LetterNotification {
                            $data = static::normalizeNotificationFormData($record, $data);

                            $record->update(array_merge(
                                $data,
                                [
                                    'prepared_by' => auth()->id(),
                                    'status' => LetterNotification::STATUS_DRAFT,
                                ],
                            ));

                            if ($arguments['queue'] ?? false) {
                                if (! static::queueNotification($record, $errorMessage)) {
                                    Notification::make()
                                        ->title('Nie można przekazać do wysyłki')
                                        ->body($errorMessage)
                                        ->danger()
                                        ->send();

                                    $action->halt();

                                    return $record;
                                }
                            }

                            static::refreshPresetViews($livewire);

                            return $record;
                        })
                        ->successNotificationTitle(fn (array $arguments): string => ($arguments['queue'] ?? false)
                            ? 'Zapisano szkic i przekazano do wysyłki'
                            : 'Zapisano szkic wiadomości'
                        ),

                    Action::make('queue')
                        ->label('Przekaż do wysyłki')
                        ->icon('heroicon-o-clock')
                        ->color('warning')
                        ->requiresConfirmation()
                        ->visible(fn (LetterNotification $record) => in_array($record->status, [
                            LetterNotification::STATUS_DRAFT,
                            LetterNotification::STATUS_FAILED,
                        ], true))
                        ->action(function (LetterNotification $record, Livewire $livewire) {
                            if (! static::queueNotification($record, $errorMessage)) {
                                Notification::make()
                                    ->title('Nie można przekazać do wysyłki')
                                    ->body($errorMessage)
                                    ->danger()
                                    ->send();

                                return;
                            }

                            Notification::make()
                                ->title('Wiadomość przekazano do wysyłki')
                                ->success()
                                ->send();

                            static::refreshPresetViews($livewire);
                        }),

                    Action::make('unqueue')
                        ->label('Cofnij przekazanie do wysyłki')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(fn (LetterNotification $record) => $record->status === LetterNotification::STATUS_QUEUED)
                        ->action(function (LetterNotification $record, Livewire $livewire) {
                            $record->update([
                                'status' => LetterNotification::STATUS_DRAFT,
                            ]);

                            Notification::make()
                                ->title('Cofnięto przekazanie do wysyłki')
                                ->success()
                                ->send();

                            static::refreshPresetViews($livewire);
                        }),

                    Action::make('send_now')
                        ->label('Wyślij teraz')
                        ->icon('heroicon-o-paper-airplane')
                        ->color('success')
                        ->requiresConfirmation()
                        ->modalHeading('Wysłać wiadomość teraz?')
                        ->modalDescription('Ta akcja wyśle wiadomość od razu, bez oczekiwania na zaplanowaną wysyłkę dzienną. Operacja może potrwać chwilę, zwłaszcza przy większych załącznikach.')
                        ->visible(fn (LetterNotification $record) => $record->status === LetterNotification::STATUS_QUEUED)
                        ->action(function (LetterNotification $record, Livewire $livewire) {
                            if (! static::startImmediateSending($record, $errorMessage)) {
                                Notification::make()
                                    ->title('Nie udało się wysłać wiadomości')
                                    ->body($errorMessage ?: 'Wystąpił błąd podczas wysyłki wiadomości.')
                                    ->danger()
                                    ->send();

                                static::refreshPresetViews($livewire);

                                return;
                            }

                            Notification::make()
                                ->title('Wiadomość została wysłana')
                                ->success()
                                ->send();

                            static::refreshPresetViews($livewire);
                        }),

                    Action::make('rebuild_notifications')
                        ->label('Utwórz powiadomienia')
                        ->icon('heroicon-o-arrow-path')
                        ->requiresConfirmation()
                        ->visible(fn ($record) => $record->status === LetterNotification::STATUS_MISSING_RECIPIENT)
                        ->action(function (LetterNotification $record, Livewire $livewire) {
                            $letter = $record->letter;

                            if (! $letter) {
                                Notification::make()
                                    ->title('Nie znaleziono pisma')
                                    ->danger()
                                    ->send();

                                return;
                            }

                            $letter->loadMissing(['matter', 'notifications']);
                            $letter->syncNotificationsForRecipients();

                            Notification::make()
                                ->title('Powiadomienia zostały odświeżone')
                                ->success()
                                ->send();

                            static::refreshPresetViews($livewire);
                        }),

                    Action::make('ignore')
                        ->label('Ignoruj')
                        ->icon('heroicon-o-x-circle')
                        ->color('gray')
                        ->requiresConfirmation()
                        ->visible(fn (LetterNotification $record) => in_array($record->status, [
                            LetterNotification::STATUS_PENDING,
                            LetterNotification::STATUS_DRAFT,
                            LetterNotification::STATUS_MISSING_RECIPIENT,
                            LetterNotification::STATUS_FAILED,
                        ], true))
                        ->action(function (LetterNotification $record, Livewire $livewire) {
                            $record->update([
                                'status' => LetterNotification::STATUS_IGNORED,
                                'ignored_at' => now(),
                                'ignored_by' => auth()->id(),
                            ]);

                            Notification::make()
                                ->title('Powiadomienie oznaczono jako zignorowane')
                                ->success()
                                ->send();

                            static::refreshPresetViews($livewire);
                        }),

                    Action::make('restore_ignored')
                        ->label('Cofnij ignorowanie')
                        ->icon('heroicon-o-arrow-uturn-left')
                        ->color('info')
                        ->requiresConfirmation()
                        ->visible(fn (LetterNotification $record) => $record->status === LetterNotification::STATUS_IGNORED)
                        ->action(function (LetterNotification $record, Livewire $livewire) {
                            $restoredStatus = static::getRestoredStatusForIgnoredNotification($record);

                            $record->update([
                                'status' => $restoredStatus,
                                'ignored_at' => null,
                                'ignored_by' => null,
                            ]);

                            Notification::make()
                                ->title('Przywrócono powiadomienie do obsługi')
                                ->success()
                                ->send();

                            static::refreshPresetViews($livewire);
                        }),
                ])
                    ->icon('heroicon-m-ellipsis-horizontal')
                    ->label('Akcje')
                    ->button(),
            ])
            ->poll(fn (Livewire $livewire): ?string => ($livewire->activeTab ?? null) === 'queued' ? '5s' : null)
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [];
    }


    protected static function getRestoredStatusForIgnoredNotification(LetterNotification $record): string
    {
        if (! filled($record->contact_id)) {
            return LetterNotification::STATUS_MISSING_RECIPIENT;
        }

        if (static::hasPreparedDraftData($record)) {
            return LetterNotification::STATUS_DRAFT;
        }

        return LetterNotification::STATUS_PENDING;
    }

    protected static function refreshPresetViews(Livewire $livewire): void
    {
        if (method_exists($livewire, 'refreshLetterNotificationPresetViews')) {
            $livewire->refreshLetterNotificationPresetViews();

            return;
        }

        $livewire->dispatch('refresh-letter-notification-preset-views');
    }

    protected static function hasPreparedDraftData(LetterNotification $record): bool
    {
        $selectedAttachments = is_array($record->selected_attachments)
            ? array_values(array_filter($record->selected_attachments))
            : [];

        return filled($record->template_id)
            || filled($record->subject)
            || filled($record->message)
            || $record->with_attachments
            || count($selectedAttachments) > 0;
    }


    protected static function shouldConfirmTemplateReplacement(?string $subject, ?string $message): bool
    {
        return static::hasMeaningfulFormContent($subject) || static::hasMeaningfulFormContent($message);
    }

    protected static function hasMeaningfulFormContent(?string $value): bool
    {
        if ($value === null) {
            return false;
        }

        $normalized = str_replace(['&nbsp;', '\xc2\xa0'], ' ', $value);
        $normalized = strip_tags($normalized);
        $normalized = preg_replace('/\s+/u', ' ', $normalized ?? '');

        return filled(trim((string) $normalized));
    }

    protected static function getAttachmentOptionLabel(string $path, string $name, string $sizeLabel = ''): HtmlString
    {
        $previewUrl = e(static::getAttachmentPreviewUrl($path));
        $label = e($name . $sizeLabel);
        $icon = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="-mt-1 size-5"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>';

        return new HtmlString(
            '<span class="inline-flex items-center gap-4">'
            . '<span>' . $label . '</span>'
            . '<a href="' . $previewUrl . '" target="_blank" rel="noopener noreferrer" title="Podejrzyj załącznik" class="inline-flex items-center text-gray-500 hover:text-gray-700" onclick="event.stopPropagation();" onmousedown="event.stopPropagation();">'
            . $icon
            . '</a>'
            . '</span>'
        );
    }

    protected static function getAttachmentPreviewUrl(string $path): string
    {
        $encodedPath = collect(explode('/', trim($path, '/')))
            ->filter(fn (string $segment): bool => $segment !== '')
            ->map(fn (string $segment): string => rawurlencode($segment))
            ->implode('/');

        return '/z/' . $encodedPath;
    }

    protected static function applyTemplateToForm(string|int $templateId, LetterNotification $record, callable $set): void
    {
        $template = LetterNotificationTemplate::find($templateId);

        if (! $template) {
            return;
        }

        $rendered = $template->renderForNotification($record);

        $set('template_id', (string) $template->getKey());
        $set('subject', $rendered['subject']);
        $set('message', $rendered['message']);
    }

    protected static function normalizeNotificationFormData(LetterNotification $record, array $data): array
    {
        $allowedAttachments = is_array($record->letter?->files)
            ? array_values(array_filter($record->letter->files))
            : [];

        $selectedAttachments = is_array($data['selected_attachments'] ?? null)
            ? array_values(array_filter($data['selected_attachments']))
            : [];

        $selectedAttachments = array_values(array_intersect($selectedAttachments, $allowedAttachments));

        $data['selected_attachments'] = $selectedAttachments;
        $data['with_attachments'] = count($selectedAttachments) > 0;
        $data['recipient_email'] = $record->contact?->email;

        return $data;
    }

    protected static function queueNotification(LetterNotification $record, ?string &$errorMessage = null): bool
    {
        $errorMessage = static::getQueueValidationError($record);

        if ($errorMessage !== null) {
            return false;
        }

        $attributes = [
            'status' => LetterNotification::STATUS_QUEUED,
            'error_message' => null,
        ];

        if (! filled($record->prepared_by) && auth()->check()) {
            $attributes['prepared_by'] = auth()->id();
        }

        $record->update($attributes);

        return true;
    }

    protected static function startImmediateSending(LetterNotification $record, ?string &$errorMessage = null): bool
    {
        $record->refresh();

        if ($record->status !== LetterNotification::STATUS_QUEUED) {
            $errorMessage = 'Powiadomienie nie jest już dostępne do natychmiastowej wysyłki.';

            return false;
        }

        $errorMessage = static::getQueueValidationError($record);

        if ($errorMessage !== null) {
            return false;
        }

        $attributes = [];

        if (! filled($record->prepared_by) && auth()->check()) {
            $attributes['prepared_by'] = auth()->id();
        }

        if ($attributes !== []) {
            $record->update($attributes);
            $record->refresh();
        }

        $sender = app(LetterNotificationSender::class);

        if ($sender->send($record, auth()->id())) {
            return true;
        }

        $record->refresh();
        $errorMessage = $record->error_message;

        if (! filled($errorMessage)) {
            $errorMessage = $record->status === LetterNotification::STATUS_SENT
                ? 'Wiadomość została już wysłana.'
                : 'Nie udało się wysłać wiadomości.';
        }

        return false;
    }

    protected static function getQueueValidationError(LetterNotification $record): ?string
    {
        if (! filled($record->recipient_email)) {
            return 'Brak adresu e-mail odbiorcy.';
        }

        if (! filled($record->subject) || ! filled($record->message)) {
            return 'Uzupełnij temat i treść wiadomości.';
        }

        $selectedAttachments = is_array($record->selected_attachments)
            ? array_values(array_filter($record->selected_attachments))
            : [];

        if ($record->with_attachments && count($selectedAttachments) > 0) {
            $totalBytes = static::getSelectedAttachmentsTotalBytes($selectedAttachments);
            $maxBytes = static::getMaxAttachmentsSizeBytes();

            if ($totalBytes > $maxBytes) {
                $totalMb = static::formatBytesToMb($totalBytes);

                return "Łączny rozmiar wybranych załączników wynosi {$totalMb} MB i przekracza " . LetterNotification::MAX_ATTACHMENTS_SIZE_MB . ' MB.';
            }
        }

        return null;
    }

    protected static function getSelectedAttachmentsTotalBytes(array $attachments): int
    {
        $totalBytes = 0;

        foreach ($attachments as $path) {
            $path = (string) $path;

            try {
                if (Storage::disk('local')->exists($path)) {
                    $totalBytes += (int) Storage::disk('local')->size($path);
                }
            } catch (Throwable $e) {
                // Pomijamy pojedynczy błąd odczytu pliku.
            }
        }

        return $totalBytes;
    }

    protected static function getMaxAttachmentsSizeBytes(): int
    {
        return LetterNotification::MAX_ATTACHMENTS_SIZE_MB * 1024 * 1024;
    }

    protected static function formatBytesToMb(int $bytes): float
    {
        return round($bytes / 1024 / 1024, 2);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListLetterNotifications::route('/'),
        ];
    }
}
