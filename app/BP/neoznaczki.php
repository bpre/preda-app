<?php

namespace App\BP;
use Imagick;
use Exception;
use OCR\Input\File;
use GuzzleHttp\Psr7;
use GuzzleHttp\Client;
use App\Models\Neostamp;
use Spatie\PdfToImage\Pdf;
use App\Models\ContactLetter;
use OCR\Engine\OcrSpaceEngine;
use GuzzleHttp\Psr7\HttpFactory;
use Filament\Notifications\Notification;
use App\Filament\Resources\NeostampResource;
use OCR\Utility\Http\Request\Multipart\GuzzleMultipartFormFactory;

set_time_limit(3000);

class neoznaczki {

    // PRZYPISUJE ZNACZKI DO KORESPONDENCJI

    public static function przypiszZnaczek(ContactLetter $contactLetter, $c = NULL)
    {
        if(in_array($contactLetter->delivery_type, NeostampResource::types())) {

            $neostamp = Neostamp::where('type', $contactLetter->delivery_type)
                ->whereNull('contact_letter_id')
                ->orderBy('expiration_date')
                ->first();

            if($neostamp === null) {

                Notification::make()
                    ->title('Skończyły się neoznaczki tego typu!')
                    ->body('Dodaj znaczki tego typu, a następnie przypisz znaczek do tej przesyłki.')
                    ->danger()->send();

                return '';

            } else {

                $neostamp->contact_letter_id = $c ? $c->id : $contactLetter->id;
                $neostamp->save();

                ContactLetter::where('id', $neostamp->contact_letter_id)->update([
                    'neostamp_id' => $neostamp->id
                ]);

                if($contactLetter->neostamp_id != $neostamp->id) {
                    Notification::make()->title('Przypisano Neoznaczek')->success()->send();
                }

                return  $neostamp->id;

            }

        }
    }

    // TWORZY NEOZNACZKI NA PODSTAWIE PLIKU PDF Z KOPERTAMI
    public function create($neoznaczek_pdf, $type, $expiration_date)
    {
        $directory = '../storage/app/neoznaczki/';

        $pdf = new Pdf($neoznaczek_pdf);

        $resolution = 1;

        $pdf->setResolution(300 * $resolution);

        $ile_nowych = 0;
        $pominiete = 0;

        for($i = 1; $i <= $pdf->getNumberOfPages(); $i++) {

            if(!file_exists($directory . date("Y-m-d"))) {
                mkdir($directory . date("Y-m-d"));
            }

            $file = $directory . date("Y-m-d"). '/'.$i.'.jpg';

            $pdf->setPage($i);
            $pdf->saveImage($file);

            // ZNACZEK

            $image = new Imagick($file);

            $image->setImageBackgroundColor('#ffffff');
            $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
            $image = $image->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

            $img_neoznaczek = $directory . date("Y-m-d"). '/'.$i.'_znaczek.jpg';
            // width, height, x, y
            $image->cropImage(690 * $resolution, 530 * $resolution, 1900 * $resolution, 120 * $resolution);
            $image->writeImage($img_neoznaczek);

            // NUMER

            $image_number = new Imagick($file);

            // $image_number->setImageBackgroundColor('#ffffff');
            // $image_number->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
            // $image_number = $image_number->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

            $img_numer = $directory . date("Y-m-d"). '/'.$i.'_numer.jpg';
            $image_number->cropImage(800 * $resolution, 120 * $resolution, 1850 * $resolution, 560 * $resolution);
            // $image_number->scaleimage(
            //     $image_number->getImageWidth() / 2,
            //     $image_number->getImageHeight() / 2
            // );


            // $canvas = new Imagick();
            // $finalWidth = 2400 * $resolution;
            // $finalHeight = 1600 * $resolution;
            // $canvas->newImage($finalWidth, $finalHeight, 'white', 'jpg' );
            // $offsetX = (int)($finalWidth  / 2) - (int)($image_number->getImageWidth() / 2);
            // $offsetY = (int)($finalHeight / 2) - (int)($image_number->getImageWidth() / 2);
            // $canvas->compositeImage( $image_number, imagick::COMPOSITE_OVER, $offsetX, $offsetY );
            // $canvas->writeImage($img_numer);

            $image_number->writeImage($img_numer);



            $filename = preg_replace('/[^a-zA-Z0-9_-]/', '',
                $this->bp_ocr($img_numer)
            );

            $record = Neostamp::where('label', $filename)->first();

            if ($record === null) {
                Neostamp::create([
                    'label' => $filename,
                    'type' => $type,
                    'expiration_date' => $expiration_date
                ]);
                $ile_nowych++;
            } else {
                $pominiete++;
            }

            rename($img_neoznaczek, $directory . date("Y-m-d"). '/' . $filename . '_znaczek.jpg');
            rename($img_numer, $directory . date("Y-m-d"). '/' . $filename . '_numer.jpg');
            unlink($directory . date("Y-m-d"). '/' . $i . '.jpg');
        }

        unlink($neoznaczek_pdf);

        return array('dodane' => $ile_nowych, 'pominiete' => $pominiete);
    }

    // OCR
    public function ocr($file)
    {
        $httpClient = new Client();
        $requestFactory = new HttpFactory();
        $formFactory = new GuzzleMultipartFormFactory();
        $engine = new OcrSpaceEngine(
            $httpClient,
            $requestFactory,
            $formFactory,
            'K86940890188957',
        );

        $image = new File($file);

        return $engine->process($image);
    }

    public function bp_ocr($file, $dd = false)
    {

        $client = new Client();

        // $file = '../storage/app/neoznaczki/2024-04-14/1_numer.png';

        // $image = new File($file);

        // $fileData = Psr7\Utils::tryFopen($file, 'r');

        $fileData = fopen($file, 'r');

        // $fileData = $file;

        try {

            $r = $client->request('POST', 'https://api.ocr.space/parse/image', [

                'headers' => [
                    'apiKey' => 'K86940890188957'
                ],

                'multipart' => [
                    // [
                    //     'name' => 'isTable',
                    //     'contents' => 'true'
                    // ],
                    // [
                    //     'name' => 'scale',
                    //     'contents' => 'true'
                    // ],
                    // [
                    //     'name' => 'filetype',
                    //     'contents' => 'PNG'
                    // ],
                    // [
                    //     'name' => 'isOverlayRequired',
                    //     'contents' => 'true'
                    // ],
                    [
                        'name' => 'OCREngine',
                        'contents' => '2'
                    ],
                    [
                        'name' => 'file',
                        'contents' => $fileData
                    ]
                ]

            ], ['file' => $fileData]);

            $response =  json_decode($r->getBody(),true);

            if(isset($response['ParsedResults'][0]['ParsedText'])) {
                if($dd) {
                    // dd($response );
                    dd($response['ParsedResults'][0]['ParsedText']);
                } else {
                    return $response['ParsedResults'][0]['ParsedText'];
                }
            } else {
                dd('OCR API error.');
            }


        } catch(Exception $err) {
            dd('OCR API error.');
            header('HTTP/1.0 403 Forbidden');
            echo $err->getMessage();
        }


    }

    // WALIDACJA PRZED DRUKOWANIEM KOPERT / KSIĄŻKI NADAWCZEJ
    public function validateNeostamps($records, $action)
    {

        $cancel = false;
        $notifications = [];

        foreach($records as $record) {

            foreach($record->recipients as $recipient) {

                // dd($recipient);



                if($recipient->contact_lawfirm()->exists()) {

                    if(empty($recipient->contact_lawfirm->address) || empty($recipient->contact_lawfirm->zip_code) || empty($recipient->contact_lawfirm->city)) {
                        $notifications['contact_' . $recipient->id] = 'Uzupełnij dane adresowe kontaktu: '.($recipient->type == 'organizacja' ? $recipient->contact_lawfirm->organization : $recipient->contact_lawfirm->label);
                        $cancel = true;
                    }

                } elseif(empty($recipient->address) || empty($recipient->zip_code) || empty($recipient->city)) {

                    $notifications['contact_' . $recipient->id] = 'Uzupełnij dane adresowe kontaktu: '.($recipient->type == 'organizacja' ? $recipient->organization : $recipient->label);
                    $cancel = true;
                }
            }
        }

        if($cancel) {

            foreach($notifications as $notification) {
                Notification::make()
                    ->title($notification)->danger()->send();
            }

            $action->cancel();
        }

    }



}


?>
