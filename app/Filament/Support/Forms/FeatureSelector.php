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
                            ->schema(function () use ($statePath, $data) {

                                $fields = [];

                                $unGroupOptions = [];
                                $groupOptions = [];

                                foreach ($data['extras'] as $key => $value) {

                                    is_array($value) ? $groupOptions[$key] = $value : $unGroupOptions[$key] = $value;
                                }

                                if (count($unGroupOptions) > 0) {

                                    $fields[] = CheckboxList::make($statePath . '_extras')
                                        ->disableLabel()
                                        ->options($unGroupOptions)
                                        ->formatStateUsing(
                                            fn (CheckboxList $component, ?Model $record) => collect($component->getOptions())
                                                ->keys()
                                                ->filter(fn (string $feature) => $record && Feature::for($record)->active($feature))
                                                ->toArray()
                                        )
                                        ->dehydrated(false);
                                }

                                if (count($groupOptions) > 0) {
                                    foreach ($groupOptions as $key => $value) {

                                        $fields[] = Fieldset::make($key)
                                            ->label(ucfirst(trans($key)))
                                            ->schema([
                                                CheckboxList::make($statePath .'_'.$key. '_extras')
                                                    ->disableLabel()
                                                    ->bulkToggleable()
                                                    ->options($value)
                                                    ->formatStateUsing(
                                                        fn (CheckboxList $component, ?Model $record) => collect($component->getOptions())
                                                            ->keys()
                                                            ->filter(fn (string $feature) => $record && Feature::for($record)->active($feature))
                                                            ->toArray()
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
            fn (self $component, Closure $get) => collect($component->getOptions())
                ->reduce(
                    function (array $state, array $data, string $key) use ($component, $get) {

                        $statePath = $component->getStatePath(false) . '.' . class_basename($key);

                        $mutateState = [];

                        foreach ($data['extras'] as $xkey => $value) {
                            if (is_array($value)) {
                                /** @var array */
                                $checkboxState = is_array($get($statePath . '_'.$xkey.'_extras')) ? $get($statePath . '_'.$xkey.'_extras') : ($get($statePath . '_'.$xkey.'_extras') ? [$get($statePath . '_'.$xkey.'_extras')] : []);

                                $mutateState = array_merge($mutateState, $checkboxState);

                            }

                        }

                        if (count($mutateState) > 0) {

                            return array_merge(
                                $state,
                                $mutateState,
                                $get($statePath . '_extras') ?: [],
                                [$key]
                            );
                        }

                        return array_merge(
                            $state,
                            $get($statePath)
                                    ? ($get($statePath . '_extras') ? [$key, ...$get($statePath . '_extras')] : [$key])
                                    : []
                        );

                    },
                    []
                )
        );
    }
}
