<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Support\MetaData\Models\MetaData;

class MetaDataFormV2 extends Section
{
    #[\Override]
    public function setUp(): void
    {
        parent::setUp();

        $this
            ->heading(trans('Meta Data'))
            ->relationship('metaData')
            ->schema([

                Forms\Components\TextInput::make('title')
                    ->translateLabel()
                    ->string()
                    ->maxLength(255),

                Forms\Components\TextInput::make('keywords')
                    ->translateLabel()
                    ->string()
                    ->maxLength(255),

                Forms\Components\TextInput::make('author')
                    ->translateLabel()
                    ->string()
                    ->maxLength(255),

                Forms\Components\Textarea::make('description')
                    ->translateLabel()
                    ->maxLength(fn (int $value = 160) => $value),

                Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                    ->translateLabel()
                    ->collection('image')
                    ->preserveFilenames()
                    ->customProperties(fn (Get $get) => ['alt_text' => $get('image_alt_text')])
                    ->image(),

                Forms\Components\TextInput::make('image_alt_text')
                    ->translateLabel()
                    ->visible(fn (Get $get) => filled($get('image')))
                    ->formatStateUsing(
                        fn (?MetaData $record) => $record
                            ?->getFirstMedia('image')
                            ?->getCustomProperty('alt_text')
                    )
                    ->dehydrated(false),
            ]);
    }
}
