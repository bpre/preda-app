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

        return (new MailMessage)
            ->subject('Nowe zgłoszenie do analizy kredytu')
            ->line('Potencjalny klient wysłał zgłoszenie do bezpłatnej analizy.')
            ->line('Imię i nazwisko: ' . $this->lead->name)
            ->line('Telefon: ' . $this->lead->phone)
            ->line('E-mail: ' . $this->lead->email)
            ->line('Kod pocztowy: ' . ($this->lead->postal_code ?: 'brak informacji'))
            ->line('Bank: ' . ($this->lead->bank ?: 'brak informacji'))
            ->line('Rok zawarcia umowy: ' . ($this->lead->contract_year_range ?: 'brak informacji'))
            ->line('Waluta kredytu: ' . ($this->lead->credit_currency ?: 'brak informacji'))
            ->line('Kwota kredytu: ' . ($this->lead->credit_amount_range ?: 'brak informacji'))
            ->line('Status kredytu: ' . ($this->lead->credit_status ?: 'brak informacji'))
            ->line('Czy klient ma umowę: ' . ($this->lead->has_contract ? 'tak' : 'nie'))
            ->line('Źródło leada: ' . $this->lead->attribution_summary)
            ->line('Szczegóły źródła: ' . ($this->lead->attribution_description ?: 'brak dodatkowych danych'))
            ->line('Dokumenty załączone po wysłaniu zgłoszenia: ' . ($this->lead->documents_uploaded_at ? 'tak' : 'nie'))
            ->line('Dodatkowe informacje: ' . ($this->lead->additional_info ?: 'brak'));

   }

    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
