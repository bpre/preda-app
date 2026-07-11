<?php

namespace App\Filament\Website\Resources\Faqs\Schemas;

use Filament\Schemas\Schema;
use App\Enums\Website\FAQ\Prefix;
use Filament\Forms\Components\Select;
use Filament\Schemas\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\RichEditor;

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
                            Prefix::HOMEPAGE->value => Prefix::HOMEPAGE->getLabel()
                        ])
                        ->native(false)
                        ->required(),
                    TextInput::make('question')
                        ->label('Pytanie')
                        ->required(),
                    RichEditor::make('answer')
                        ->label('Odpowiedź')
                        ->required(),
                ])

            ]);
    }
}
