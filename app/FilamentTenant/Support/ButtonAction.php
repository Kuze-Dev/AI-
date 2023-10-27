<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Closure;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\Field;
use Filament\Tables\Columns\Concerns;
use Illuminate\Support\Arr;

class ButtonAction extends Field
{
    use Concerns\HasAlignment;
    use Concerns\HasSize;

    /** @var array<Action|Closure> */
    protected array $actions = [];

    private array $evaluatedActions = [];

    protected string|Closure|null $width = null;

    protected string $view = 'filament.forms.components.button-action';

    protected function setUp(): void
    {
        $this->dehydrated(false);
    }

    public function execute(array|Action|Closure|null $actions): static
    {
        foreach (Arr::wrap($actions) as $action) {
            $this->actions[] = $action;
        }

        return $this;
    }

    /** @return array<Action|Closure> */
    public function getExecutableActions(bool $reevaluate = false): array
    {
        if ((! $reevaluate) && $this->evaluatedActions) {
            return $this->evaluatedActions;
        }

        $this->evaluatedActions = [];

        foreach ($this->actions as $action) {
            $actions = $this->evaluate($action);

            foreach (Arr::wrap($actions) as $nestedAction) {
                $this->evaluatedActions[] = $this->evaluate($nestedAction)?->component($this);
            }
        }

        return $this->evaluatedActions;
    }

    public function getActions(): array
    {
        $actions = collect($this->getExecutableActions())
            /** @phpstan-ignore-next-line */
            ->mapWithKeys(fn ($action) => [$action->getName() => $action->component($this)])
            ->toArray();

        return array_merge(parent::getActions(), $actions);
    }

    public function fullWidth(bool|Closure $condition = true): static
    {
        $this->width = $condition ? 'w-full' : null;

        return $this;
    }

    public function isFullWidth(): bool
    {
        return $this->evaluate($this->width == 'w-full');
    }

    public function getWidth(): ?string
    {
        return $this->evaluate($this->width);
    }
}
