<?php

declare(strict_types=1);

namespace App\Forms\Components;

use Filament\Forms\Components\Component;
use Closure;

class Interpolation extends Component
{
    protected string $view = 'forms.components.interpolation';

    protected array|Closure $items = [];

    public static function make(string $name): static
    {
        $static = app(static::class, ['name' => $name]);
        $static->configure();

        return $static;
    }

    public function items(array|Closure $items): static
    {
        $this->items = $items;

        return $this;
    }

    public function getItems(): array
    {
        return $this->evaluate($this->items);
    }
}
