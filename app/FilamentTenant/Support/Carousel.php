<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Closure;
use Filament\Forms\Components\Field;

class Carousel extends Field
{
    protected Closure|array|null $value = null;

    protected string $view = 'filament.forms.components.carousel';

    public function value(Closure|array $closure): static
    {
        $this->value = $closure;

        return $this;
    }

    public function getValue(): ?array
    {
        return $this->evaluate($this->value);
    }
}
