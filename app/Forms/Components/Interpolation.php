<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;
use Filament\Forms\Components\Component;
use Closure;

class Interpolation extends Component
{
    protected string $view = 'forms.components.interpolation';

    protected array | Closure $items = [];


    public static function make(): static
    {
        return new static();
    }

    public function items(array | Closure $items): static
    {
        $this->items = $items;
        
        return $this;
    }

    public function getItems(): array
    {
        return $this->evaluate($this->items);
    }
}
