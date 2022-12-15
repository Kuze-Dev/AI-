<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\MenuResource\Pages\CreateMenu;
use Domain\Blueprint\Enums\FieldType;
use Domain\Blueprint\Models\Blueprint;
use Filament\Facades\Filament;
use Filament\Forms\Components\Field;

use function Pest\Laravel\assertDatabaseCount;
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
                    'name' => 'Test Home',
                    'url' => 'https://test-url-home.com',
                    'target' => '_blank',
                ],
                [
                    'name' => 'Test About',
                    'url' => 'https://test-url-about.com',
                    'target' => '_blank',
                    'childs' => [
                        [
                            'name' => 'Test About Child',
                            'url' => 'https://test-url-about-child.com',
                            'target' => '_blank',
                        ],
                        [
                            'name' => 'Test About Child 2',
                            'url' => 'https://test-url-about-child-2.com',
                            'target' => '_blank',
                        ]
                    ]
                ],
            ]
        ]);
});
