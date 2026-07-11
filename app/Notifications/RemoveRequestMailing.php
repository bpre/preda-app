<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RemoveRequestMailing extends Notification
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

        $mail->subject('Prośba o usunięcie danych - mailing')
                ->line('Potencjalny klient prosi o usunięcie danych.')
                ->line('Imię i nazwisko klienta: ' . $this->offer->name);

        return $mail;

   }

}
