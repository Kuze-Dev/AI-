<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\MenuResource\Pages\CreateMenu;
use Domain\Menu\Enums\NodeType;
use Domain\Menu\Enums\Target;
use Filament\Facades\Filament;

use function Pest\Livewire\livewire;

beforeEach(function () {
    testInTenantContext();
    Filament::setContext('filament-tenant');
    loginAsSuperAdmin();
});

it('can render page', function () {
    livewire(CreateMenu::class)
        ->assertFormExists()
        ->assertSuccessful();
});

it('can create menu', function () {
    livewire(CreateMenu::class)
        ->fillForm([
            'name' => 'Test Main Menu',
            'nodes' => [
                [
                    'label' => 'Test Home',
                    'target' => Target::BLANK->value,
                    'type' => NodeType::URL->value,
                    'url' => 'https://test-url-home.com',
                    'children' => [
                        [
                            'label' => 'Test Home',
                            'target' => Target::BLANK->value,
                            'type' => NodeType::URL->value,
                            'url' => 'https://test-url-home.com',
                        ],
                        [
                            'label' => 'Test About Child 2',
                            'target' => Target::BLANK->value,
                            'type' => NodeType::URL->value,
                            'url' => 'https://test-url-home.com',
                        ],
                    ],
                ],
            ],
        ]);
});
