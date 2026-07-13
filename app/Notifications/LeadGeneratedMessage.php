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
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $message = (new MailMessage)
            ->subject($this->subject)
            ->markdown('emails.lead-generated-message', [
                'body' => $this->body,
            ]);

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
