<?php

declare(strict_types=1);

namespace Domain\Support\MetaTag;
use Filament\Forms;

trait MetaTagsForm 
{
    public static function metaTagsForm()
    {
        return Forms\Components\Section::make('Meta Tags')
            ->schema([
                Forms\Components\TextInput::make('meta_title')
                    ->label('Title'),
                Forms\Components\TextInput::make('meta_keywords')
                    ->label('Keywords'),
                Forms\Components\TextInput::make('meta_author')
                    ->label('Author'),
                Forms\Components\Textarea::make('meta_description')
                    ->label('Description')

            ]);
    }
}