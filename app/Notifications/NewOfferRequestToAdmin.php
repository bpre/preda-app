<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewOfferRequestToAdmin extends Notification
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

        $mail->subject('Nowa prośba o ofertę')
                ->line('Potencjalny klient prosi o ofertę.')
                ->line('Imię i nazwisko klienta: ' . $this->offer->name)
                ->line('Telefon: ' . $this->offer->phone)
                ->line('Wariant: ' . $this->offer->variant);

        return $mail;

   }

}
