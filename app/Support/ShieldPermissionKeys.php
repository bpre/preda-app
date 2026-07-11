<?php

namespace App\Support;

use App\Filament\Website\Resources\Users\UserResource as WebsiteUserResource;
use Illuminate\Support\Str;

class ShieldPermissionKeys
{
    public static function build(string $entity, string $affix, string $subject): string
    {
        if (
            str_starts_with($entity, 'App\\Filament\\Website\\Resources\\')
            && $entity !== WebsiteUserResource::class
        ) {
            return Str::studly($affix).':'.Str::studly($subject);
        }

        $legacySubject = [
            'BankMatter' => 'bank::matter',
            'CHFMatter' => 'c::h::f::matter',
            'CHFPaymentMatter' => 'c::h::f::payment::matter',
            'CHFPotentialMatter' => 'c::h::f::potential::matter',
            'ContactMatter' => 'contact::matter',
            'DatabaseNotification' => 'notification',
            'ExchangeRate' => 'exchange::rate',
            'LetterNotification' => 'letter::notification',
            'LetterNotificationTemplate' => 'letter::notification::template',
            'OtherMatter' => 'other::matter',
            'PortalUser' => 'portal::user',
            'TemplateStage' => 'template::stage',
        ][$subject] ?? Str::snake($subject);

        return Str::snake($affix).'_'.$legacySubject;
    }
}
