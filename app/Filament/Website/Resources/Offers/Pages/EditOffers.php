<?php

namespace App\Filament\Website\Resources\Offers\Pages;

use Filament\Actions\Action;
use Barryvdh\DomPDF\Facade\Pdf;
use Filament\Actions\DeleteAction;
use App\Notifications\OfferToClient;
use Illuminate\Support\Facades\Storage;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Illuminate\Support\Facades\Notification as Notif;
use App\Filament\Website\Resources\Offers\OffersResource;

class EditOffers extends EditRecord
{
    protected static string $resource = OffersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    public string $saveIntent = 'normal'; // 'normal' | 'confirm'

    protected function getSaveFormAction(): Action
    {
        return parent::getSaveFormAction()
            ->action(function () {
                $this->saveIntent = 'normal';
                $this->save();
            });
    }

    protected function getFormActions(): array
    {
        return [

            Action::make('fillDefaults')
                ->label('Domyślna oferta')
                ->icon('heroicon-m-sparkles')
                ->hidden(fn () => $this->isOfferSent()) // opcjonalnie: po wysłaniu blokujesz też wypełnianie
                ->action(function () {

                    $state = $this->form->getRawState();

                    $start_wstepna = 999;
                    $start_premia = $state['amount'] > 150000 ? 30000 : 25000;

                    $max_wstepna = 12000;
                    $max_druga = 6000;
                    $max_rozprawa = 500;
                    $max_rozprawy_limit = 1999;

                    $defaults = [

                        'start_wstepna' => $start_wstepna,
                        'start_premia' => $start_premia,
                        'start_procent_limit' => 35,
                        'start_rozprawa' => 0,
                        'start_razem_max' => $start_wstepna + $start_premia,

                        'max_wstepna' => $max_wstepna,
                        'max_druga_instancja' => $max_druga,
                        'max_rozprawa' => $max_rozprawa,
                        'max_rozprawy_limit' => $max_rozprawy_limit,
                        'max_razem_max' => $max_wstepna + $max_druga + $max_rozprawy_limit
                    ];

                    foreach ($defaults as $path => $value) {
                        data_set($state, $path, $value); // obsługuje dot-notation
                    }

                    $this->form->fill($state);

                }),

            Action::make('confirmOffer')
                ->label('Zatwierdź ofertę')
                ->icon('heroicon-m-check-circle')
                ->hidden(fn () => $this->isOfferSent()) // po wysłaniu nie można zatwierdzać
                ->action(function() {

                    $this->saveIntent = 'confirm';
                    $this->save();
                    $this->saveIntent = 'normal';
                }),

            Action::make('sendOffer')
                ->label(fn () => $this->isOfferSent() ? 'Wyślij ponownie ofertę' : 'Wyślij ofertę')
                ->icon('heroicon-m-paper-airplane')
                ->color(fn () => $this->isOfferSent() ? 'danger' : 'primary')
                // ->disabled(fn () => ! $this->isOfferConfirmed() || $this->isOfferSent())
                ->action(fn () => $this->sendOffer()),

            Action::make('downloadOffer')
                ->label('Pobierz ofertę')
                ->icon('heroicon-m-arrow-down-tray')
                ->visible(fn () => $this->isOfferSent())
                ->url(fn () => route('offers.download-pdf', $this->getRecord()))
                ->openUrlInNewTab(),

                $this->getSaveFormAction(),
                $this->getCancelFormAction(),
        ];
    }


    protected function generateOfferPdfPath(): string
    {
        $offer = $this->getRecord()->fresh(); // pewniej, gdy formularz coś zmienił

        $pdf = Pdf::loadView('pdfs.offer', [
            'offer' => $offer,
        ]);

        $relativePath = "offers/{$offer->id}/offer.pdf";

        Storage::disk('local')->put($relativePath, $pdf->output());

        return storage_path('app/' . $relativePath);
    }

    public function sendOffer(): void
    {
        if (! $this->isOfferConfirmed()) {
            Notification::make()
                ->danger()
                ->title('Najpierw zatwierdź ofertę')
                ->send();

            return;
        }

        // if ($this->isOfferSent()) {
        //     Notification::make()
        //         ->warning()
        //         ->title('Oferta już została wysłana')
        //         ->send();

        //     return;
        // }

        // 1) zapisz ewentualne zmiany z formularza normalnym zapisem (bez sent_at)
        $this->saveIntent = 'normal';
        $this->save();

        $record = $this->getRecord();

        $this->generateOfferPdfPath();

        // 2) wyślij maila (dopasuj adres i Mailable)
        Notif::route('mail', $record->email)->notify(new OfferToClient($record));

        // 3) dopiero po udanym mailu oznacz jako wysłane

        $record->offer_sent_at = now();
        $record->save();

        $record->refresh(); // żeby UI od razu zobaczyło offer_sent_at i zablokowało pola/przyciski

        Notification::make()
            ->success()
            ->title('Wysłano ofertę')
            ->send();
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        if ($this->saveIntent === 'confirm') {
            $data['offer_confirmed_at'] = now();
        }

        if ($this->saveIntent === 'send') {
            $data['offer_sent_at'] = now();
        }

        return $data;
    }

    protected function getSavedNotificationTitle(): ?string
    {
        return $this->saveIntent === 'confirm'
            ? 'Oferta została zatwierdzona'
            : parent::getSavedNotificationTitle();
    }

    protected function isOfferConfirmed(): bool
    {
        return filled($this->getRecord()?->offer_confirmed_at);
    }

    protected function isOfferSent(): bool
    {
        return filled($this->getRecord()?->offer_sent_at);
    }

}
