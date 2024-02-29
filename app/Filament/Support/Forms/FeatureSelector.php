<?php

declare(strict_types=1);

namespace App\Filament\Support\Forms;

use App\Features\GroupFeature;
use App\Features\GroupFeatureExtra;
use Domain\Tenant\Models\Tenant;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Concerns\HasOptions;
use Filament\Forms\Components\Field;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Illuminate\Support\Str;

class FeatureSelector extends Field
{
    use HasOptions;

    protected string $view = 'filament-forms::components.group';

    protected function setUp(): void
    {
        parent::setUp();

        $this->columns(['sm' => 2])
            ->default([])
            ->schema(
                fn (self $component) => collect($component->getOptions())
                    ->ensure(GroupFeature::class)
                    ->map(
                        fn (GroupFeature $data) => Section::make([
                            Toggle::make($data->fieldName())
                                ->label($data->getFeature()->getLabel())
                                ->formatStateUsing(fn (?Tenant $record) => $data->isActive($record))
                                ->afterStateUpdated(function (Set $set, bool $state) use ($data) {

                                    if (! $state) {
                                        foreach ($data->extra as $extra) {
                                            $set($data->fieldName().$extra->fieldName().'_extra', []);
                                        }
                                    }
                                })
                                ->reactive(),
                            Fieldset::make(trans('Extra'))
                                ->visible(
                                    fn (Get $get) => $get($data->fieldName()) === true && filled($data->extra)
                                )
                                ->schema(function () use ($data) {
                                    $fields = [];

                                    foreach ($data->extra as $extra) {
                                        $fields[] = $extra->groupLabel === null
                                            ? static::nonLabeledGroup($data, $extra)
                                            : static::labeledGroup($data, $extra);
                                    }

                                    return $fields;

                                }),
                        ])
                            ->columnSpan(1)
                    )
                    ->toArray()
            );

    }

    private static function nonLabeledGroup(GroupFeature $data, GroupFeatureExtra $extra): CheckboxList
    {
        return CheckboxList::make(
            $data->fieldName().$extra->fieldName().'_extra'
        )
            ->hiddenLabel()
            ->bulkToggleable()
            ->options($extra->getOptions())
            ->formatStateUsing(
                fn (CheckboxList $component, ?Tenant $record): array => collect($component->getOptions())
                    ->keys()
                    ->filter(fn (string $feature) => $record?->features()->active($feature))
                    ->values()
                    ->toArray()
            );
    }

    private static function labeledGroup(GroupFeature $data, GroupFeatureExtra $extra): Fieldset
    {
        return Fieldset::make(
            $data->fieldName().'_'.Str::slug($extra->groupLabel)
        )
            ->label($extra->groupLabel)
            ->schema([
                self::nonLabeledGroup($data, $extra),
            ]);
    }
}
