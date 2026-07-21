<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadGeneratedMessage extends Notification
{
    use Queueable;

    public function __construct(
        public string $subject,
        public string $body,
        public array $attachments = [],
        public ?string $replyToEmail = null,
        public ?string $replyToName = null,
        public array $mailTags = [],
        public array $mailMetadata = [],
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->subject)
            ->view([
                'html' => 'emails.lead-generated-message',
                'text' => 'emails.lead-generated-message-text',
            ], [
                'subject' => $this->subject,
                'body' => $this->body,
            ]);

        if (filled($this->replyToEmail) && filter_var($this->replyToEmail, FILTER_VALIDATE_EMAIL)) {
            $message->replyTo($this->replyToEmail, filled($this->replyToName) ? $this->replyToName : null);
        }

        foreach ($this->mailTags as $tag) {
            if (is_scalar($tag) && filled((string) $tag)) {
                $message->tag((string) $tag);
            }
        }

        foreach ($this->mailMetadata as $key => $value) {
            if (is_string($key) && is_scalar($value) && filled((string) $value)) {
                $message->metadata($key, (string) $value);
            }
        }

        foreach ($this->attachments as $attachment) {
            $options = [
                'mime' => $attachment['mime'] ?? 'application/pdf',
            ];

            if (filled($attachment['as'] ?? null)) {
                $options['as'] = $attachment['as'];
            }

            $message->attach($attachment['path'], $options);
        }

        return $message;
    }
}
