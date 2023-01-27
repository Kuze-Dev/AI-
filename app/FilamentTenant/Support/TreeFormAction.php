<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support;

use App\FilamentTenant\Support\Contracts\HasTrees;
use Filament\Forms\ComponentContainer;
use Filament\Pages\Actions\Action;
use Illuminate\Support\Str;

class TreeFormAction extends Action
{
    protected string $view = 'filament.pages.actions.tree-form-action';

    public static function getDefaultName(): ?string
    {
        return 'tree-form';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->modalHeading(function (HasTrees $livewire) {
            if ( ! $activeTreeStatePath = $livewire->getActiveTreeItemStatePath()) {
                return;
            }

            $state = data_get($livewire, $activeTreeStatePath);

            $treeComponent = $livewire->getTreeComponent();

            $name = (string) Str::of($treeComponent->getName())->headline()->singular();

            if ($state !== null) {
                return trans('Edit :label', ['label' => $treeComponent->getItemLabel($state) ?? $name]);
            }

            return trans('Add :name', ['name' => $name]);
        });

        $this->slideOver(true);

        $this->mountUsing(function (HasTrees $livewire, ComponentContainer $form) {
            if ( ! $activeTreeStatePath = $livewire->getActiveTreeItemStatePath()) {
                return;
            }

            $state = data_get($livewire, $activeTreeStatePath) ?? [];

            $form->fill($state);
        });

        $this->form(fn (HasTrees $livewire) => $livewire->getTreeFormSchema());

        $this->action(function (HasTrees $livewire, array $data) {
            if ( ! $activeTreeStatePath = $livewire->getActiveTreeItemStatePath()) {
                return;
            }

            $oldData = data_get($livewire, $activeTreeStatePath) ?? [];

            data_set($livewire, $activeTreeStatePath, array_merge($oldData, $data));

            $livewire->unmountTreeItem();
        });
    }
}
