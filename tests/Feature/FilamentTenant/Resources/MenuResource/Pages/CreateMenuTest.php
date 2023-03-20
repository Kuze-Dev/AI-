<?php

declare(strict_types=1);

use App\FilamentTenant\Resources\MenuResource\Pages\CreateMenu;
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
                    'url' => 'https://test-url-home.com',
                    'target' => '_blank',
                    'model_type' => '',
                    'model_id' => '',
                    'type' => 'url',
                ],
                [
                    'label' => 'Test About',
                    'url' => 'https://test-url-about.com',
                    'target' => '_blank',
                    'type' => 'url',
                    'model_type' => '',
                    'model_id' => '',
                    'children' => [
                        
                        [
                            'label' => 'Test About Child',
                            'url' => 'https://test-url-about-child.com',
                            'target' => '_blank',
                            'model_type' => '',
                            'model_id' => ' ',
                            'type' => 'url',
                        ],
                        [
                            'label' => 'Test About Child 2',
                            'url' => '',
                            'target' => '_blank',
                            'model_type' => 'pages',
                            'model_id' => '1',
                            'type' => 'resource',
                        ],
                    ],
                ],
                [
                    'label' => 'Test Contact',
                    'url' => '',
                    'target' => '_blank',
                    'model_type' => 'pages',
                    'model_id' => '1',
                    'type' => 'resource',
                ],
            ],
        ]);
});
