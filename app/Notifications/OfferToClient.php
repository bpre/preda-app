<?php

namespace App\Notifications;

use Illuminate\Support\Str;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OfferToClient extends Notification
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

        $path = storage_path('app/private/offers/'.$this->offer->id.'/offer.pdf');


        switch($this->offer->sex) {
            case 'male':
                $persona = 'Pana';
                $uzna = 'uzna Pan';
                $beda = 'będzie Pan zainteresowany';
                break;
            case 'female':
                $persona = 'Pani';
                $uzna = 'uzna Pani';
                $beda = 'będzie Pani zainteresowana';
                break;
            default:
                $persona = 'Państwa';
                $uzna = 'uznają Państwo';
                $beda = 'będą Państwo zainteresowani';
        }


        $mail = (new MailMessage);

        $mail->subject('Oferta')
                ->line('Dzień dobry,')
                ->line('dziękuję za zainteresowanie usługami naszej kancelarii.')
                ->line('W załączeniu przesyłam przygotowaną specjalnie dla '.$persona.' ofertę.')
                ->line('Jeśli po zapoznaniu się z ofertą '.$uzna.', że odpowiada ona '.$persona.' oczekiwaniom i '.$beda.' nawiązaniem współpracy z naszą kancelarią - uprzejmie proszę o kontakt telefoniczny lub mailowy.')
                ->salutation('Z wyrazami szacunku

adw. Bartosz Pręda')
                ->attach($path, [
                    'as'   => 'PREDA_Kancelaria_Oferta_dla_'
                    . str_replace(' ', '_', $this->offer->name),
                    'mime' => 'application/pdf',
                ]);

        return $mail;

   }

}
