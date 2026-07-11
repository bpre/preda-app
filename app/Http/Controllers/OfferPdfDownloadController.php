<?php

namespace App\Http\Controllers;

use App\Models\Offer;
use Illuminate\Support\Facades\Storage;

class OfferPdfDownloadController extends Controller
{
    public function __invoke(Offer $offer)
    {
        // TODO: autoryzacja (policy / gate)
        // $this->authorize('view', $offer);

        abort_unless($offer->pdf_path, 404);

        $disk = 'private'; // albo 'public' – jak wolisz
        abort_unless(Storage::disk($disk)->exists($offer->pdf_path), 404);

        $filename = "Oferta_".str_replace(' ', '_', $offer->name)."_".
        str_replace(array(' ', '-', ':'),'', $offer->created_at).".pdf";

        return Storage::disk($disk)->download($offer->pdf_path, $filename);
    }
}
