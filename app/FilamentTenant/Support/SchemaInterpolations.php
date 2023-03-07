<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Filament\Forms\Components\Component;
use Closure;
use Domain\Blueprint\DataTransferObjects\SchemaData;

class SchemaInterpolations extends Component
{
    protected string $view = 'filament.forms.schema-interpolations';

    protected SchemaData|Closure|null $schemaData = null;

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

    public function schemaData(SchemaData|Closure $schemaData = null): self
    {
        $this->schemaData = $schemaData;

        return $this;
    }

    public function getSchemaData(): ?SchemaData
    {
        return $this->evaluate($this->schemaData);
    }

    public function getInterpolations(): array
    {
        $interpolations = [];

        foreach ($this->getSchemaData()?->sections ?? [] as $section) {
            foreach ($section->fields as $field) {
                $interpolations[] = "{{ \${$section->state_name}['{$field->state_name}'] }}";
            }
        }

        return $interpolations;
    }
}
