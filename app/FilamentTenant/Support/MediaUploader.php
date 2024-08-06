<?php

namespace App\FilamentTenant\Support;

use Filament\Forms\Components\FileUpload;

class MediaUploader extends FileUpload
{
    protected string $view = 'filament.forms.components.media-uploader';
}
