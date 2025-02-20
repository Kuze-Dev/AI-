<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support\Forms;

use App\FilamentTenant\Support\Divider;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Forms\Set;

class LocationPickerField extends Field
{
    protected string $view = 'filament-forms::components.group';

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->schema([
            Group::make([
                Group::make([
                    TextInput::make('full_address')
                        ->label(trans('Search Address'))
                        ->dehydrated(false)
                        ->columnspan(2)
                        ->placeholder('Search Address'),
                    TextInput::make('address_name')
                        ->label(trans('Address Name'))
                        ->columnspan(2)
                        ->placeholder('Add Address Name'),
                    TextInput::make('latitude')
                        ->lazy()
                        // ->default('14.5454321')
                        ->afterStateUpdated(function ($state, Get $get, Set $set) {
                            $set('map', [
                                'lat' => floatval($state),
                                'lng' => floatval($get('longitude')),
                            ]);
                        })
                        ->columnspan(1),
                    TextInput::make('longitude')
                        ->lazy()
                        // ->default('121.0686773')
                        ->afterStateUpdated(function ($state, Get $get, Set $set) {
                            $set('map', [
                                'lat' => floatval($get('latitude')),
                                'lng' => floatval($state),
                            ]);
                        })
                        ->columnspan(1),
                ])->columns(2),
                Map::make('map')
                    ->dehydrated(false)
                    ->mapControls([
                        'mapTypeControl' => true,
                        'scaleControl' => true,
                        'streetViewControl' => true,
                        'rotateControl' => true,
                        'fullscreenControl' => true,
                        'searchBoxControl' => false, // creates geocomplete field inside map
                        'zoomControl' => false,
                    ])
                    ->height(fn () => '400px') // map height (width is controlled by Filament options)
                    ->defaultZoom(18) // default zoom level when opening form
                    ->autocomplete(
                        fieldName:'full_address',
                        types: ['airport'],
                        placeField: 'name',
                        ) // field on form to use as Places geocompletion field
                    ->autocompleteReverse(true) // reverse geocode marker location to autocomplete field
                    ->draggable() // allow dragging to move marker
                    ->defaultLocation([
                        '14.5454321',
                        '121.0686773'
                    ]) 
                    ->afterStateUpdated(function ($state, Get $get, Set $set) {
                       
                        $set('latitude', $state['lat']);
                        $set('longitude', $state['lng']);
                    })
                    ->afterStateHydrated(function (Map $component, Get $get, Set $set) {


                        // $component->state([
                        //     'lat' => floatval($get('latitude')),
                        //     'lng' => floatval($get('longitude')),
                        // ]);

                    
                    }),
               
            ])
                ->columns(2),
            Divider::make('div')
                ->dehydrated(false),
        ]);
    }
}
