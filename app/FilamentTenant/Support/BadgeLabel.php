<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Filament\Forms\Components\Concerns\HasState;
use Filament\Forms\Components\Field;
use Filament\Tables\Columns\Concerns;

class BadgeLabel extends Field
{
    use Concerns\CanBeCopied;
    use Concerns\CanBeInline;
    use Concerns\HasAlignment;
    use Concerns\HasColors;
    use Concerns\HasIcons;
    use HasState;

    protected string $view = 'filament.forms.components.badge-label';
}
