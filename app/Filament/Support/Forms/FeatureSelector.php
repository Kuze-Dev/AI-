<?php

declare(strict_types=1);

namespace App\Filament\Support\Forms;

use Closure;
use Filament\Forms\Components\Card;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Concerns\HasOptions;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Toggle;
use Illuminate\Database\Eloquent\Model;
use Laravel\Pennant\Feature;

class FeatureSelector extends Field
{
    use HasOptions;

    protected string $view = 'forms::components.group';

    protected function setUp(): void
    {
        parent::setUp();

        $this->columns(['sm' => 2]);

        $this->schema(
            fn (self $component) => collect($component->getOptions())
                ->map(function (array $data, string $key) {
                    $statePath = class_basename($key);

                    return Card::make([
                        Toggle::make($statePath)
                            ->label($data['label'])
                            ->reactive()
                            ->formatStateUsing(fn (?Model $record) => $record && Feature::for($record)->active($key))
                            ->dehydrated(false),
                        Fieldset::make('Extras')
                            ->visible(fn (Closure $get) => count($data['extras']) && $get($statePath))
                            ->schema([
                                CheckboxList::make($statePath . '_extras')
                                    ->disableLabel()
                                    ->options($data['extras'])
                                    ->formatStateUsing(
                                        fn (CheckboxList $component, ?Model $record) => collect($component->getOptions())
                                            ->keys()
                                            ->filter(fn (string $feature) => $record && Feature::for($record)->active($feature))
                                            ->toArray()
                                    )
                                    ->dehydrated(false),
                            ]),
                    ])
                        ->columnSpan(1);
                })
                ->toArray()
        );

        $this->default([]);

        $this->mutateDehydratedStateUsing(
            fn (self $component, Closure $get) => collect($component->getOptions())
                ->reduce(
                    function (array $state, array $data, string $key) use ($component, $get) {
                        $statePath = $component->getStatePath(false) . '.' . class_basename($key);

                        return array_merge(
                            $state,
                            array_filter([
                                $get($statePath) ? $key : null,
                                ...$get($statePath . '_extras'),
                            ])
                        );
                    },
                    []
                )
        );
    }
}
