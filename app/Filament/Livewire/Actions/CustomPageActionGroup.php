<?php

declare(strict_types=1);

namespace App\Filament\Livewire\Actions;

// use Filament\Pages\Actions\ActionGroup as BasePageActionGroup;
use Filament\Actions\ActionGroup as BasePageActionGroup;
use Filament\Pages\Actions\Concerns\BelongsToLivewire;

class CustomPageActionGroup extends BasePageActionGroup
{
    use BelongsToLivewire;

    public string $name = 'custom_page_action_group';

    // protected string $view = 'filament::pages.actions.group';

    public static function getName(): string
    {
        return 'custom_page_action_group';
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
