<?php

namespace App\Observers;

use App\Models\Stage;
use Illuminate\Support\Facades\Storage;

class StageObserver
{
    public function saved(Stage $stage): void
    {

        if ($stage->isDirty('files')) {

            $originalFieldContents = $stage->getOriginal('files');
            $newFieldContents = $stage->files;

            $originalFieldContentsDecoded = $stage->getOriginal('files');

            if (is_array($originalFieldContentsDecoded)) $originalFieldContentsDecoded = array_filter($originalFieldContentsDecoded);
            if (!is_array($originalFieldContentsDecoded) or count($originalFieldContentsDecoded) == 0)
            {
                if(!is_null($originalFieldContents)) {
                    Storage::disk('local')->delete($originalFieldContents);
                }
            }

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

    public function deleted(Stage $stage): void
    {
        if (! is_null($stage->files)) {

            $fieldContentsDecoded = $stage->files;
            if (!is_array($fieldContentsDecoded))
            {
            //    Storage::disk('local')->delete($stage->files);
            }

            else
            {

                foreach ($fieldContentsDecoded as $file)
                 {

                    if(!is_null($file)) {
                        Storage::disk('local')->delete($file);
                    }

                 }
            }
        }
    }
}
