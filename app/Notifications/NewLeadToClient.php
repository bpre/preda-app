<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadToClient extends Notification
{
    use Queueable;

    public $lead;

    public $no_docs;

    public function __construct($lead, $no_docs = null)
    {
        $this->lead = $lead;
        $this->no_docs = $no_docs;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {

        $mail = (new MailMessage)
            ->subject('Potwierdzenie otrzymania zgłoszenia')
            ->line('Dzień dobry,')
            ->line('dziękujemy za przesłanie zgłoszenia przez formularz na naszej stronie internetowej.')
            ->line('Skontaktujemy się maksymalnie w ciągu 1 dnia roboczego.');

        $mail->tag('website-lead-confirmation');

        if (filled($this->lead->getKey())) {
            $mail->metadata('website_lead_id', (string) $this->lead->getKey());
        }

        if (filled($this->lead->email)) {
            $mail->metadata('recipient_email', (string) $this->lead->email);
        }

        if (filled($this->lead->potential_matter_id)) {
            $mail->metadata('matter_id', (string) $this->lead->potential_matter_id);
        }

        return $mail;

    }
}
