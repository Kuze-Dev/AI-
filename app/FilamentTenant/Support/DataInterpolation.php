<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Closure;
use Filament\Forms\Components\Component;

class DataInterpolation extends Component
{
    protected string $view = 'filament.forms.schema-interpolations';

    protected Closure|array|null $schemaData;

    protected string $name = 'data';

    final public function __construct(string $name, Closure|array|null $schemaData = null)
    {
        $this->statePath($name);
        $this->schemaData($schemaData);
    }

    public static function make(string $name, ?Closure $schemaData = null): static
    {
        $static = app(static::class, [
            'name' => $name,
            'schemaData' => $schemaData,
        ]);

        $static->configure();

        return $static;
    }

    public function schemaData(Closure|array|null $schemaData = null): self
    {
        $this->schemaData = $schemaData;

        return $this;
    }

    public function getSchemaData(): ?array
    {
        return $this->evaluate($this->schemaData);
    }

    public function getInterpolations(): array
    {
        /** @var string */
        $label = is_string($this->label) ? $this->label : 'main';

        /** @var array */
        $data = $this->getSchemaData() ?? [];

        return $this->getAllKeyPaths($data, $label);

    }

    public function getAllKeyPaths(array $array, string $variableName = 'main'): array
    {
        $result = [];

        $traverse = function ($array, $prefix = '') use (&$traverse, &$result, $variableName) {
            foreach ($array as $key => $value) {
                $currentKey = $prefix === '' ? $key : $prefix."']['".$key;

                if (is_array($value)) {
                    $traverse($value, $currentKey);
                } else {
                    $result[] = "{{ \${$variableName}['".$currentKey."'] }}";
                }
            }
        };

        $traverse($array);

        return $result;
    }
}
