<?php

namespace App\Filament\Website\Resources\Leads\Actions;

use App\Models\Website\Lead;
use App\Notifications\LeadGeneratedMessage;
use App\Support\Website\LeadResponseMailGenerator;
use Filament\Actions\Action;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Support\Facades\Notification;

class GenerateLeadResponseAction
{
    public static function make(): Action
    {
        return Action::make('generateLeadResponse')
            ->label('Wygeneruj mail')
            ->icon('heroicon-m-envelope')
            ->color('info')
            ->modalHeading('Mail do klienta i follow-up')
            ->modalDescription('Treść jest generowana na podstawie pól zgłoszenia. Możesz ją dopracować przed wysyłką.')
            ->modalWidth('5xl')
            ->fillForm(fn (Lead $record): array => LeadResponseMailGenerator::generate($record))
            ->schema([
                Section::make('Pierwszy mail')
                    ->headerActions([
                        self::sendMailAction(
                            name: 'sendInitialLeadResponse',
                            subjectField: 'initial_subject',
                            bodyField: 'initial_body',
                        ),
                    ])
                    ->schema([
                        TextInput::make('initial_subject')
                            ->label('Temat')
                            ->required()
                            ->copyable(copyMessage: 'Skopiowano temat', copyMessageDuration: 1500),
                        RichEditor::make('initial_body')
                            ->label('Treść')
                            ->required()
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'link'],
                                ['undo', 'redo'],
                            ])
                            ->helperText('Przed wysyłką możesz poprawić treść wiadomości.'),
                    ])
                    ->columns(1),
                Section::make('Follow-up')
                    ->headerActions([
                        self::sendMailAction(
                            name: 'sendFollowUpLeadResponse',
                            subjectField: 'follow_up_subject',
                            bodyField: 'follow_up_body',
                        ),
                    ])
                    ->schema([
                        TextInput::make('follow_up_subject')
                            ->label('Temat')
                            ->required()
                            ->copyable(copyMessage: 'Skopiowano temat', copyMessageDuration: 1500),
                        RichEditor::make('follow_up_body')
                            ->label('Treść')
                            ->required()
                            ->toolbarButtons([
                                ['bold', 'italic', 'underline', 'link'],
                                ['undo', 'redo'],
                            ])
                            ->helperText('Proponowana wiadomość do wysłania po 1-2 dniach bez odpowiedzi. Możesz ją poprawić przed wysyłką.'),
                    ])
                    ->columns(1),
            ])
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Zamknij');
    }

    private static function sendMailAction(string $name, string $subjectField, string $bodyField): Action
    {
        return Action::make($name)
            ->label('Wyślij maila')
            ->icon('heroicon-m-paper-airplane')
            ->color('success')
            ->action(function (Lead $record, Get $schemaGet) use ($subjectField, $bodyField): void {
                self::sendMail(
                    lead: $record,
                    subject: $schemaGet($subjectField),
                    body: $schemaGet($bodyField),
                );
            });
    }

    private static function sendMail(Lead $lead, mixed $subject, mixed $body): void
    {
        $subject = trim((string) $subject);
        $body = trim((string) $body);

        if (blank($lead->email)) {
            FilamentNotification::make()
                ->danger()
                ->title('Nie wysłano maila')
                ->body('Ten lead nie ma zapisanego adresu e-mail.')
                ->send();

            return;
        }

        if (blank($subject) || blank($body)) {
            FilamentNotification::make()
                ->danger()
                ->title('Nie wysłano maila')
                ->body('Temat i treść wiadomości nie mogą być puste.')
                ->send();

            return;
        }

        Notification::route('mail', $lead->email)
            ->notify(new LeadGeneratedMessage($subject, $body));

        FilamentNotification::make()
            ->success()
            ->title('Mail został wysłany')
            ->body('Wiadomość wysłano na adres '.$lead->email.'.')
            ->send();
    }
}
