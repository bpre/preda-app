<?php

namespace App\Http\Controllers;

use App\Models\Letter;
use App\Models\Neostamp;
use Illuminate\Support\Facades\Storage;

// use Illuminate\Support\Facades\Storage;

class ReadfileController extends Controller
{

    public function __invoke($k, $data, $file)
    {
        $file = $k.'/'.$data.'/'.$file;
        return $this->preview($file);
    }

    private function isPDForImage($ext)
    {
        return ($ext == 'pdf' || $ext == 'jpg' || $ext == 'jpeg' || $ext == 'png' || $ext == 'gif');
    }

    public function file($k, $data, $file)
    {
        $f = $k.'/'.$data.'/'.$file;

        $letter = Letter::whereJsonContains('files', $f)->firstOrFail();
        $filename = $letter->files_names[$f];

        // dadać logikę - czy użytkownik ma prawa do rekordu z tabeli 'letters'
        if(true) {

            return Storage::download($f, $filename);

        }
    }

    public function preview($file)
    {

        $letter = Letter::whereJsonContains('files', $file)->firstOrFail();

        // dadać logikę - czy użytkownik ma prawa do rekordu z tabeli 'letters'
        if(true) {

            $ext = pathinfo($file, PATHINFO_EXTENSION);
            $filename = $letter->files_names[$file];

            if($ext == 'pdf')
            {
                return view('filepreview/pdf', ['title' => $filename, 'file'=>$file])->layout('layouts.blank');
            }

        }
    }

    public function neostamp($date, $label)
    {
        $neostamp = Neostamp::where('created_at', 'LIKE', $date.'%')->where('label', $label)->firstOrFail();

        $file = 'neoznaczki/'.$date.'/'.$label.'_znaczek.jpg';

        if(Storage::disk('local')->exists($file)) {
            return Storage::download($file, 'Neoznaczek');
        } else {
            echo 'FILE NOT EXISTS.';
        }


    }

}
