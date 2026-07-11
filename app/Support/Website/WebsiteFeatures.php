<?php

namespace App\Support\Website;

final class WebsiteFeatures
{
    public static function sentenceContentGeneratorEnabled(): bool
    {
        return (bool) config('website.features.sentence_content_generator.enabled', false);
    }
}
