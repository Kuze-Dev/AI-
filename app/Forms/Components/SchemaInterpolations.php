<?php

declare(strict_types=1);

namespace App\Forms\Components;

use Filament\Forms\Components\Component;
use Closure;

class SchemaInterpolations extends Component
{
    protected string $view = 'forms.components.schema_interpolation';

    protected Closure|null $schemaData = null;

    final public function __construct(string $name, Closure|null $schemaData)
    {
        $this->statePath($name);
        $this->schemaData($schemaData);
    }

    public static function make(string $name, Closure $schemaData = null): static
    {
        $static = app(static::class, [
            'name' => $name,
            'schemaData' => $schemaData,
        ]);

        $static->configure();

        return $static;
    }

    public function schemaData(Closure $schemaData = null): self
    {
        $this->schemaData = $schemaData;

        return $this;
    }

    public function getSchemaData(): array
    {
        return $this->evaluate($this->schemaData);
    }
}
