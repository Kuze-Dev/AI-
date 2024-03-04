<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use Filament\Forms\ComponentContainer;
use Filament\Forms\Components\Actions\Action;
use Illuminate\Support\Str;

class TreeFormAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'tree-form';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->modalHeading(function ($component, self $action) {
            return ($activeTreeState = $action->getActiveTreeState())
                ? trans('Edit :label', ['label' => $component->getItemLabel($activeTreeState)])
                : trans('Add :name', ['name' => (string) Str::of($component->getName())->headline()->singular()]);
        });

        $this->slideOver(true);

        $this->closeModalByClickingAway(false);

        $this->mountUsing(function (self $action, ComponentContainer $form) {
            $form->fill($action->getActiveTreeState());
        });

        $this->form(function ($component) {
            return $component->getChildComponents();
        });

        $this->action(function ($livewire, self $action, array $data) {
            data_set(
                $livewire,
                $action->getActiveTreeStatePath(),
                array_merge($action->getActiveTreeState(), $data)
            );
        });
    }

    protected function getActiveTreeStatePath(): string
    {
        /** @phpstan-ignore-next-line */
        return $this->getLivewire()->mountedFormComponentActionsArguments[0]['activeTreeStatePath'];
    }

    protected function getActiveTreeState(): array
    {
        return data_get($this->getLivewire(), $this->getActiveTreeStatePath()) ?? [];
    }
}
