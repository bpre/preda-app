<?php

namespace App\Observers;

use App\Models\Letter;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class LetterObserver
{

    public function creating(Letter $letter): void
    {
        $letter->id = Str::uuid();
    }

    public function saved(Letter $letter): void
    {

        if ($letter->isDirty('files')) {

            $originalFieldContents = $letter->getOriginal('files');
            $newFieldContents = $letter->files;

            # We attempt to JSON decode the field. If it is an array, this is an indication we have ->multiple() activated
            // $originalFieldContentsDecoded = json_decode($letter->getOriginal('files'));
            $originalFieldContentsDecoded = $letter->getOriginal('files');

            # Clean up empty entries in the resulting array
            if (is_array($originalFieldContentsDecoded)) $originalFieldContentsDecoded = array_filter($originalFieldContentsDecoded);

            # Simple case: one file
            if (!is_array($originalFieldContentsDecoded) or count($originalFieldContentsDecoded) == 0)
            {
                // dd($originalFieldContents);
                if(!is_null($originalFieldContents)) {
                    Storage::disk('local')->delete($originalFieldContents);
                }
            }

            # Complex case: multiple files
            else
            {
                foreach ($originalFieldContentsDecoded as $originalFile)
                 {
                    if (trim($originalFile) != null && !in_array($originalFile, $newFieldContents))
                     {
                        if(!is_null($originalFile)) {
                            Storage::disk('local')->delete($originalFile);
                        }
                     }
                 }
            }
        }

    }

    public function deleted(Letter $letter): void
    {
        if (! is_null($letter->files)) {

            # We attempt to JSON decode the field. If it is an array, there are multiple files
            // $fieldContentsDecoded = json_decode($letter->files);
            $fieldContentsDecoded = $letter->files;

            # Simple case: one file
            if (!is_array($fieldContentsDecoded))
            {
            //    Storage::disk('local')->delete($letter->files);
            }

            # Complex case: multiple files
            else
            {

                foreach ($fieldContentsDecoded as $file)
                 {
                    // dd($file);
                    if(!is_null($file)) {
                        Storage::disk('local')->delete($file);
                    }

                 }
            }
        }
    }


}
