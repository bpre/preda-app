<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class LeadDocumentsUploadedToAdmin extends Notification
{
    use Queueable;

    public function __construct(public $lead) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $filesCount = is_array($this->lead->files) ? count($this->lead->files) : 0;

        return (new MailMessage)
            ->subject('Dokumenty załączone do zgłoszenia')
            ->line('Klient załączył dokumenty po wysłaniu zgłoszenia do analizy.')
            ->line('Imię i nazwisko: ' . $this->lead->name)
            ->line('Telefon: ' . $this->lead->phone)
            ->line('E-mail: ' . $this->lead->email)
            ->line('Bank: ' . ($this->lead->bank ?: 'brak informacji'))
            ->line('Rok zawarcia umowy: ' . ($this->lead->contract_year_range ?: 'brak informacji'))
            ->line('Waluta kredytu: ' . ($this->lead->credit_currency ?: 'brak informacji'))
            ->line('Status kredytu: ' . ($this->lead->credit_status ?: 'brak informacji'))
            ->line('Czy klient ma umowę: ' . ($this->lead->has_contract ? 'tak' : 'nie'))
            ->line('Dokumenty załączone po wysłaniu zgłoszenia: tak')
            ->line('Liczba załączonych plików: ' . $filesCount);
    }
}
