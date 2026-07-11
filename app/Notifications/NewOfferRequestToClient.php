<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOfferRequestToClient extends Notification
{
    use Queueable;

    public $offer;

    public function __construct($offer)
    {
        $this->offer = $offer;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }
    public function toMail(object $notifiable): MailMessage
    {

        $mail = (new MailMessage);

        $mail->subject('Potwierdzenie otrzymania zgłoszenia')
            ->line('Dzień dobry,')
            ->line('potwierdzamy otrzymanie prośby o przedstawienie oferty.')
            ->line('Spersonalizowaną ofertę prześlemy najszybciej jak to możliwe (maksymalnie w ciągu 1 dnia roboczego od otrzymania zgłoszenia).');

        return $mail;

    }
}
