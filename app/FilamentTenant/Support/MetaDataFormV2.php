<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

//use Domain\Support\MetaData\Models\MetaData;
use Filament\Forms;
use Filament\Forms\Components\Section;
use Filament\Forms\Get;
use Support\MetaData\Models\MetaData;

class MetaDataFormV2 extends Section
{
    public function setUp(): void
    {
        parent::setUp();

        $this
            ->relationship('metaData')
            ->schema([
                Forms\Components\TextInput::make('title')
                    ->string()
                    ->maxLength(255)
                    ->formatStateUsing(fn (?MetaData $record) => $record?->title),
                Forms\Components\TextInput::make('keywords')
                    ->string()
                    ->maxLength(255)
                    ->formatStateUsing(fn (?MetaData $record) => $record?->keywords),
                Forms\Components\TextInput::make('author')
                    ->string()
                    ->maxLength(255)
                    ->formatStateUsing(fn (?MetaData $record) => $record?->author),
                Forms\Components\Textarea::make('description')
                    ->maxLength(fn (int $value = 160) => $value)
                    ->formatStateUsing(fn (?MetaData $record) => $record?->description),
                Forms\Components\SpatieMediaLibraryFileUpload::make('image')
                    ->collection('image')
                    ->preserveFilenames()
                    ->customProperties(fn (Get $get) => ['alt_text' => $get('image_alt_text')])
                    ->image(),
                Forms\Components\TextInput::make('image_alt_text')
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
