<?php

namespace App\Http\Controllers\Website\Admin;

use App\Models\Website\Offer;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class OfferPdfDownloadController extends Controller
{
    public function __invoke(Offer $offer)
    {
        // autoryzacja (dopasuj do swoich Policy/Filament)
        // $this->authorize('view', $offer);

        abort_if(blank($offer->offer_sent_at), 404);

        $disk = Storage::disk('local');
        $path = "offers/{$offer->id}/offer.pdf"; // dopasuj do miejsca, gdzie zapisujesz PDF

        abort_unless($disk->exists($path), 404);

        return $disk->download($path, "Oferta_" . str_replace(' ', '_', $offer->name) . ".pdf");
    }
}