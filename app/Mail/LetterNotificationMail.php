<?php

namespace App\Mail;

use App\Models\LetterNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;

class LetterNotificationMail extends Mailable
{
    use Queueable, SerializesModels;

    protected const LOGO_URL = 'https://preda.info/images/logo.png';

    protected const COMPANY_URL = 'https://preda.info';

    public function __construct(
        public LetterNotification $notification
    ) {}

    public function build(): static
    {
        [$signatureName, $signatureTitle, $companyName] = $this->resolveSignatureData();
        $messageText = $this->normalizeMessage($this->notification->message);

        $mail = $this
            ->subject($this->notification->subject ?? 'Powiadomienie o piśmie')
            ->view('emails.letter-notification', [
                'notification' => $this->notification,
                'messageHtml' => $this->formatMessageForHtml($messageText),
                'messageText' => $messageText,
                'signatureName' => $signatureName,
                'signatureTitle' => $signatureTitle,
                'companyName' => $companyName,
                'logoUrl' => self::LOGO_URL,
                'companyUrl' => self::COMPANY_URL,
            ])
            ->text('emails.letter-notification-text', [
                'messageText' => $messageText,
                'signatureName' => $signatureName,
                'signatureTitle' => $signatureTitle,
                'companyName' => $companyName,
            ]);

        $letter = $this->notification->letter;

        $mail->tag('letter-notification');

        foreach ($this->mailgunMetadata($letter) as $key => $value) {
            $mail->metadata($key, $value);
        }

        $allowedAttachments = is_array($letter?->files)
            ? array_values(array_filter($letter->files))
            : [];

        $selectedAttachments = is_array($this->notification->selected_attachments)
            ? array_values(array_filter($this->notification->selected_attachments))
            : [];

        $selectedAttachments = array_values(array_intersect($selectedAttachments, $allowedAttachments));

        if (
            $this->notification->with_attachments
            && $letter
            && count($selectedAttachments) > 0
        ) {
            $fileNames = is_array($letter->files_names) ? $letter->files_names : [];

            foreach ($selectedAttachments as $path) {
                if (! $path) {
                    continue;
                }

                $path = (string) $path;
                $name = $fileNames[$path] ?? basename($path);

                $mail->attachFromStorageDisk('local', $path, $name);
            }
        }

        return $mail;
    }

    /**
     * @return array<string, string>
     */
    protected function mailgunMetadata(?object $letter): array
    {
        return collect([
            'letter_notification_id' => $this->notification->getKey(),
            'letter_id' => $letter?->getKey(),
            'matter_id' => $letter?->matter_id,
            'contact_id' => $this->notification->contact_id,
            'recipient_email' => $this->notification->recipient_email,
        ])
            ->filter(fn ($value): bool => is_scalar($value) && filled((string) $value))
            ->map(fn ($value): string => (string) $value)
            ->all();
    }

    protected function normalizeMessage(?string $message): string
    {
        return Str::of((string) $message)
            ->replace(["\r\n", "\r"], "\n")
            ->trim()
            ->value();
    }

    protected function formatMessageForHtml(string $message): string
    {
        if ($message === '') {
            return '';
        }

        $paragraphs = preg_split('/\n{2,}/', $message) ?: [];

        $paragraphs = array_map(
            function (string $paragraph): string {
                $formattedParagraph = collect(explode("\n", $paragraph))
                    ->map(fn (string $line): string => e(rtrim($line)))
                    ->implode('<br>');

                return '<p style="margin: 0 0 16px;">'.$formattedParagraph.'</p>';
            },
            array_filter($paragraphs, fn (string $paragraph): bool => trim($paragraph) !== '')
        );

        return implode('', $paragraphs);
    }

    protected function resolveSignatureData(): array
    {
        $preparedBy = $this->notification->preparedBy;

        if ($preparedBy) {
            return [
                (string) $preparedBy->name,
                (string) $preparedBy->mail_signature_title,
                (string) (config('mail.from.name') ?: 'PRĘDA Kancelaria Adwokacka'),
            ];
        }

        return [
            'Zespół kancelarii',
            '',
            (string) (config('mail.from.name') ?: 'PRĘDA Kancelaria Adwokacka'),
        ];
    }
}
