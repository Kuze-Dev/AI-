<?php

namespace App\FilamentTenant\Pages;

use Filament\Pages\Page;

class TenantFullAIWidget extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-cpu-chip';
    protected static ?string $title = 'AI Widget';
    protected static ?string $navigationLabel = null;
    protected static ?string $slug = 'ai-widget';


    protected static string $view = 'livewire.tenant-full-a-i-widget-page';

    protected static string $layout = 'layouts.plain';

}
