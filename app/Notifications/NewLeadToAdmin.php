<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewLeadToAdmin extends Notification
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
            ->subject('Nowe zgłoszenie do analizy kredytu')
            ->line('Potencjalny klient wysłał zgłoszenie do bezpłatnej analizy.')
            ->line('Imię i nazwisko: '.$this->lead->name)
            ->line('Telefon: '.$this->lead->phone)
            ->line('E-mail: '.$this->lead->email)
            ->line('Kod pocztowy: '.($this->lead->postal_code ?: 'brak informacji'))
            ->line('Bank: '.($this->lead->bank ?: 'brak informacji'))
            ->line('Rok zawarcia umowy: '.($this->lead->contract_year_range ?: 'brak informacji'))
            ->line('Waluta kredytu: '.($this->lead->credit_currency ?: 'brak informacji'))
            ->line('Kwota kredytu: '.($this->lead->credit_amount_range ?: 'brak informacji'))
            ->line('Status kredytu: '.($this->lead->credit_status ?: 'brak informacji'))
            ->line('Czy klient ma umowę: '.($this->lead->has_contract ? 'tak' : 'nie'))
            ->line('Źródło leada: '.$this->lead->attribution_summary)
            ->line('Szczegóły źródła: '.($this->lead->attribution_description ?: 'brak dodatkowych danych'))
            ->line('Dokumenty załączone po wysłaniu zgłoszenia: '.($this->lead->documents_uploaded_at ? 'tak' : 'nie'))
            ->line('Dodatkowe informacje: '.($this->lead->additional_info ?: 'brak'));

        $mail->tag('website-lead-admin-notification');

        if (filled($this->lead->getKey())) {
            $mail->metadata('website_lead_id', (string) $this->lead->getKey());
        }

        if (filled($notifiable->email ?? null)) {
            $mail->metadata('recipient_email', (string) $notifiable->email);
        }

        if (filled($this->lead->potential_matter_id)) {
            $mail->metadata('matter_id', (string) $this->lead->potential_matter_id);
        }

        return $mail;

    }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
