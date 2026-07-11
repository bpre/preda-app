<?php

namespace App\Filament\Website\Resources\Faqs\Schemas;

use App\Enums\Website\FAQPrefixes;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Schema;

class FaqForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Group::make([
                    Select::make('prefix')
                        ->label('Lista')
                        ->options([
                            FAQPrefixes::HOMEPAGE->value => FAQPrefixes::HOMEPAGE->getLabel(),
                        ])
                        ->native(false)
                        ->required(),
                    TextInput::make('question')
                        ->label('Pytanie')
                        ->required(),
                    RichEditor::make('answer')
                        ->label('Odpowiedź')
                        ->required(),
                ]),

            ]);
    }
}
