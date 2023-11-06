<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Closure;
use Filament\Forms\Components\Concerns\HasExtraInputAttributes;
use Filament\Forms\Components\Concerns\HasState;
use Filament\Forms\Components\Field;
use Filament\Support\Concerns\HasExtraAttributes;
use Filament\Tables\Columns\Concerns;

class TextLabel extends Field
{
    use Concerns\CanBeInline;
    use Concerns\HasAlignment;
    use Concerns\HasColor;
    use Concerns\HasFontFamily;
    use Concerns\HasSize;
    use Concerns\HasWeight;
    use HasExtraAttributes;
    use HasExtraInputAttributes;
    use HasState;

    protected string $view = 'filament.forms.components.text-label';

    protected array|Closure $items = [];

    protected bool|Closure $hasState = true;

    public static function make(string $name): static
    {
        return new static($name);
    }

    public function readOnly(bool|Closure $condition = false): static
    {
        $this->hasState = $condition;

        return $this;
    }

    public function hasState(): bool
    {
        return $this->evaluate($this->hasState);
    }
}
