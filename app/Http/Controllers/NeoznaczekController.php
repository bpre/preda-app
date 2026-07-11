<?php

namespace App\Http\Controllers;

use Imagick;
use Spatie\PdfToImage\Pdf;
use Illuminate\Http\Request;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\HttpFactory;
use OCR\Engine\OcrSpaceEngine;
use OCR\Input\File;
use OCR\Utility\Http\Request\Multipart\GuzzleMultipartFormFactory;

class NeoznaczekController extends Controller
{
    public function index()
    {

        // phpinfo();

        echo 'Neoznaczek';

        $pdf = new Pdf('../app/Neoznaczek/01.pdf');

        $pdf->setResolution(300);
        // $pdf->setCompressionQuality(100);

        // $pdf->saveImage('../app/Neoznaczek/1.png');

        // $image = new Imagick('../app/Neoznaczek/1.png');

        // $image->setImageBackgroundColor('#ffffff');
        // $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
        // $image = $image->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

        // $image->cropImage(690, 530, 1900, 90);

        // $image->writeImage('../app/Neoznaczek/1.png');


        $numberOfPages = $pdf->getNumberOfPages();

        for($i = 1; $i <= $numberOfPages; $i++) {

            $pdf->setPage($i);

            $pdf->saveImage('../app/Neoznaczek/'.$i.'.png');

            $image = new Imagick('../app/Neoznaczek/'.$i.'.png');

            $image->setImageBackgroundColor('#ffffff');
            $image->setImageAlphaChannel(Imagick::ALPHACHANNEL_REMOVE);
            $image = $image->mergeImageLayers(Imagick::LAYERMETHOD_FLATTEN);

            $image->cropImage(690, 530, 1900, 90);

            $image->writeImage('../app/Neoznaczek/'.$i.'.png');

            $image->cropImage(750, 100, 1900, 540);

            $image->writeImage('../app/Neoznaczek/'.$i.'-c.png');

        }


    }

    public function ocr()
    {
        echo 'OCR';

        $httpClient = new Client();
        $requestFactory = new HttpFactory();
        $formFactory = new GuzzleMultipartFormFactory();
        $engine = new OcrSpaceEngine(
            $httpClient,
            $requestFactory,
            $formFactory,
            'K86940890188957',
        );

        $image = new File('../app/Neoznaczek/5-c.png');

        $text = $engine->process($image);

        echo '<hr>'.$text;
    }
}
