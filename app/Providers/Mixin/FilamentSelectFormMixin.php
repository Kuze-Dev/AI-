<?php

declare(strict_types=1);

namespace App\Providers\Mixin;

use Closure;
use Filament\Forms;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\Rule;

/**
 * @mixin \Filament\Forms\Components\Select
 */
class FilamentSelectFormMixin
{
    public function optionsFromModel(): Closure
    {
        return function (string|Closure $model, string|Closure $titleColumnName, ?Closure $callback = null): Forms\Components\Select {

            if (blank($this->getSearchColumns())) {
                $this->searchable([$this->evaluate($titleColumnName)]);
            }

            $this->getSearchResultsUsing(function (Forms\Components\Select $component, ?string $search) use ($model, $titleColumnName, $callback): array {

                $query = $component->evaluate($model)::query();

                $keyName = $query->getModel()->getKeyName();

                if ($callback) {
                    $query = $component->evaluate($callback, ['query' => $query]) ?? $query;
                }

                $titleColumnName = $component->evaluate($titleColumnName);

                if (empty($query->getQuery()->orders)) {
                    $query->orderBy($titleColumnName);
                }

                /** @phpstan-ignore method.protected (PHPStan is not aware of Laravel's Macro magics)*/
                $component->applySearchConstraint($query, strtolower($search ?? ''));

                $baseQuery = $query->getQuery();

                if (isset($baseQuery->limit)) {
                    $component->optionsLimit($baseQuery->limit);
                } else {
                    $query->limit($component->getOptionsLimit());
                }

                if ($component->hasOptionLabelFromRecordUsingCallback()) {
                    return $query->get()
                        ->mapWithKeys(fn (Model $record) => [$record->{$keyName} => $component->getOptionLabelFromRecord($record)])
                        ->toArray();
                }

                if (str_contains((string) $titleColumnName, '->') && ! str_contains((string) $titleColumnName, ' as ')) {
                    $titleColumnName .= " as {$titleColumnName}";
                }

                return $query->pluck($titleColumnName, $keyName)
                    ->toArray();
            });

            $this->options(function (Forms\Components\Select $component) use ($model, $titleColumnName, $callback): ?array {
                if (($component->isSearchable()) && ! $component->isPreloaded()) {
                    return null;
                }

                $query = $component->evaluate($model)::query();

                $keyName = $query->getModel()->getKeyName();

                if ($callback) {
                    $query = $component->evaluate($callback, ['query' => $query]) ?? $query;
                }

                if (empty($query->getQuery()->orders)) {
                    $query->orderBy($titleColumnName);
                }

                if ($component->hasOptionLabelFromRecordUsingCallback()) {
                    return $query->get()
                        ->mapWithKeys(fn (Model $record) => [$record->{$keyName} => $component->getOptionLabelFromRecord($record)])
                        ->toArray();
                }

                $titleColumnName = $component->evaluate($titleColumnName);

                if (str_contains((string) $titleColumnName, '->') && ! str_contains((string) $titleColumnName, ' as ')) {
                    $titleColumnName .= " as {$titleColumnName}";
                }

                return $query->pluck($titleColumnName, $keyName)
                    ->toArray();
            });

            $this->getOptionLabelUsing(function (Forms\Components\Select $component, $value) use ($model, $titleColumnName, $callback): ?string {

                $query = $component->evaluate($model)::query();

                if ($callback) {
                    $query = $component->evaluate($callback, ['query' => $query]) ?? $query;
                }

                $record = $query->where($query->getModel()->getKeyName(), $value)->first();

                if (! $record) {
                    return null;
                }

                return $component->hasOptionLabelFromRecordUsingCallback()
                    ? $component->getOptionLabelFromRecord($record)
                    : $record->getAttributeValue($component->evaluate($titleColumnName));
            });

            $this->getOptionLabelsUsing(function (Forms\Components\Select $component, array $values) use ($model, $titleColumnName, $callback): array {

                $query = $component->evaluate($model)::query();

                $keyName = $query->getModel()->getKeyName();

                if ($callback) {
                    $query = $component->evaluate($callback, ['query' => $query]) ?? $query;
                }

                $query->whereIn($keyName, $values);

                if ($component->hasOptionLabelFromRecordUsingCallback()) {
                    return $query->get()
                        ->mapWithKeys(fn (Model $record) => [$record->{$keyName} => $component->getOptionLabelFromRecord($record)])
                        ->toArray();
                }

                $titleColumnName = $component->evaluate($titleColumnName);

                if (str_contains((string) $titleColumnName, '->') && ! str_contains((string) $titleColumnName, ' as ')) {
                    $titleColumnName .= " as {$titleColumnName}";
                }

                return $query->pluck($titleColumnName, $keyName)
                    ->toArray();
            });

            $this->rule(function (Forms\Components\Select $component) use ($model) {
                $model = $component->evaluate($model);
                $keyName = $model::query()->getModel()->getKeyName();

                return Rule::exists($model, $keyName);
            });

            /** @phpstan-ignore return.type */
            return $this;
        };
    }
}
