<?php

declare(strict_types=1);

namespace App\Filament\Support\Forms;

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

    protected string $view = 'filament-forms::components.group';

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
                            ->visible(fn (\Filament\Forms\Get $get) => count($data['extras']) && $get($statePath))
                            ->schema(function () use ($statePath, $data) {

                                $fields = [];

                                $unGroupOptions = [];
                                $groupOptions = [];

                                foreach ($data['extras'] as $key => $value) {

                                    is_array($value) ? $groupOptions[$key] = $value : $unGroupOptions[$key] = $value;
                                }

                                if (count($unGroupOptions) > 0) {

                                    $fields[] = CheckboxList::make($statePath.'_extras')
                                        ->disableLabel()
                                        ->options($unGroupOptions)
                                        ->formatStateUsing(
                                            function (CheckboxList $component, ?Model $record) {
                                                $state = collect($component->getOptions())
                                                    ->keys()
                                                    ->filter(fn (string $feature) => $record && Feature::for($record)->active($feature))
                                                    ->toArray();

                                                return array_values($state);
                                            }
                                        )
                                        ->dehydrated(false);
                                }

                                if (count($groupOptions) > 0) {
                                    foreach ($groupOptions as $key => $value) {

                                        /** @var string */
                                        $label = $key;

                                        $fields[] = Fieldset::make($label)
                                            ->label(ucfirst(trans($label)))
                                            ->schema([
                                                CheckboxList::make($statePath.'_'.$label.'_extras')
                                                    ->disableLabel()
                                                    ->bulkToggleable()
                                                    ->options($value)
                                                    ->formatStateUsing(
                                                        function (CheckboxList $component, ?Model $record) {
                                                            $state = collect($component->getOptions())
                                                                ->keys()
                                                                ->filter(fn (string $feature) => $record && Feature::for($record)->active($feature))
                                                                ->toArray();

                                                            return array_values($state);
                                                        }
                                                    )
                                                    ->dehydrated(false),
                                            ]);

                                    }
                                }

                                return $fields;

                            }),
                    ])
                        ->columnSpan(1);
                })
                ->toArray()
        );

        $this->default([]);

        $this->mutateDehydratedStateUsing(
            fn (self $component, \Filament\Forms\Get $get) => collect($component->getOptions())
                ->reduce(
                    function (array $state, array $data, string $key) use ($component, $get) {
                        $statePath = $component->getStatePath(false).'.'.class_basename($key);

                        $mutateState = [];

                        foreach ($data['extras'] as $xkey => $value) {
                            if (is_array($value) && $get($statePath)) {

                                /** @var array */
                                $checkboxState = is_array($get($statePath.'_'.$xkey.'_extras')) ? $get($statePath.'_'.$xkey.'_extras') : ($get($statePath.'_'.$xkey.'_extras') ? [$get($statePath.'_'.$xkey.'_extras')] : []);

                                $mutateState = array_merge($mutateState, $checkboxState);

                            }

                        }

                        if (count($mutateState) > 0) {

                            return array_merge(
                                $state,
                                $mutateState,
                                $get($statePath.'_extras') ?: [],
                                [$key]
                            );
                        }

                        /** @var array */
                        $checkboxExtras = is_array($get($statePath.'_extras')) ? $get($statePath.'_extras') : ($get($statePath.'_extras') ? [$get($statePath.'_extras')] : []);

                        return array_merge(
                            $state,
                            $get($statePath)
                                ? [$key, ...$checkboxExtras]
                                : []
                        );
                    },
                    []
                )
        );
    }
}
