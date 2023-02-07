<?php

declare(strict_types=1);

namespace Domain\Support\MetaTag;
use Filament\Forms;

trait MetaTagsForm 
{
    public static function metaTagsForm()
    {
        return Forms\Components\Card::make([
            Forms\Components\TextInput::make('title'),
            Forms\Components\TextInput::make('keywords'),
            Forms\Components\TextInput::make('author'),
            Forms\Components\Textarea::make('description')
        ]);
    }

    public function taggable()
    {
        return $this->morphTo();
    }
}