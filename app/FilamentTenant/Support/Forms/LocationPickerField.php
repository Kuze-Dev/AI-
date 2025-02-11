<?php

declare(strict_types=1);

namespace App\FilamentTenant\Support\Forms;

use App\FilamentTenant\Support\Divider;
use Cheesegrits\FilamentGoogleMaps\Fields\Map;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\TextInput;

class LocationPickerField extends Field
{
    protected string $view = 'forms::components.group';

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
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $set('map', [
                                'lat' => floatval($state),
                                'lng' => floatval($get('longitude')),
                            ]);
                        })
                        ->columnspan(1)
                        ->lazy(),
                    TextInput::make('longitude')
                        ->reactive()
                        ->afterStateUpdated(function ($state, callable $get, callable $set) {
                            $set('map', [
                                'lat' => floatval($get('latitude')),
                                'lng' => floatval($state),
                            ]);
                        })
                        ->columnspan(1)
                        ->lazy(),
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
                    ->autocomplete('full_address') // field on form to use as Places geocompletion field
                    ->autocompleteReverse(true) // reverse geocode marker location to autocomplete field
                    ->defaultLocation([
                        '14.5454321',
                        '121.0686773',
                    ])
                    ->afterStateUpdated(function ($state, callable $get, callable $set) {
                        $set('latitude', $state['lat']);
                        $set('longitude', $state['lng']);
                    })
                    // ->formatStateUsing(function (callable $get, callable $set) {
                    //     return [
                    //         'lat' => floatVal($get('latitude')),
                    //         'lng' => floatVal($get('longitude')),
                    //     ];

                    // })
                    ->afterStateHydrated(function (Map $component, callable $get, callable $set) {
                        $component->state([
                            'lat' => floatval($get('latitude')),
                            'lng' => floatval($get('longitude')),
                        ]);

                        // $component->getContainer()->getParentComponent()->callAfterStateHydrated();
                    }),
                // ->afterStateHydrated(function ($component){

                //     dd($component->getContainer());

                // }
                // )
                // ->reverseGeocode([
                //     'street' => '%n %S',
                //     'city' => '%L',
                //     'state' => '%A1',
                //     'zip' => '%z',
                // ]) // reverse geocode marker location to form fields, see notes below
                // ->debug() // prints reverse geocode format strings to the debug console
                // ->defaultLocation([39.526610, -107.727261]) // default for new forms
                // ->draggable() // allow dragging to move marker
                // ->clickable(false) // allow clicking to move marker
                // ->geolocate() // adds a button to request device location and set map marker accordingly
                // ->geolocateLabel('Get Location') // overrides the default label for geolocate button
                // ->geolocateOnLoad(true, false) // geolocate on load, second arg 'always' (default false, only for new form))
                // ->layers([
                //     'https://googlearchive.github.io/js-v2-samples/ggeoxml/cta.kml',
                // ]) // array of KML layer URLs to add to the map
                // ->geoJson('https://fgm.test/storage/AGEBS01.geojson') // GeoJSON file, URL or JSON
                // ->geoJsonContainsField('geojson') // field to capture GeoJSON polygon(s) which contain the map marker
            ])
                ->columns(2),
            Divider::make('div')
                ->dehydrated(false),
        ]);
    }
}
