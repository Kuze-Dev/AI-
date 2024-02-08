<?php

declare(strict_types=1);

namespace App\Providers\Mixin;

use Closure;
use Filament\Tables;
use Illuminate\Support\HtmlString;

class FilamentTextColumnMixin
{
    public function truncate(): Closure
    {
        return function (string $size = 'md', bool|Closure $tooltip = false): Tables\Columns\TextColumn {
            /** @var Tables\Columns\TextColumn $this */
            $this->tooltip(function (Tables\Columns\TextColumn $column) use ($tooltip): ?string {
                if ($tooltip instanceof Closure) {
                    return $column->evaluate($tooltip);
                }

                return $tooltip
                    ? $column->getState()
                    : null;
            });

            $this->formatStateUsing(function (?string $state) use ($size): HtmlString {
                $cssClass = match ($size) {
                    'xs' => 'max-w-xs',
                    'sm' => 'max-w-sm',
                    'md' => 'max-w-md',
                    'lg' => 'max-w-lg',
                    'xl' => 'max-w-xl',
                    '2xl' => 'max-w-2xl',
                    '3xl' => 'max-w-3xl',
                    '4xl' => 'max-w-4xl',
                    '5xl' => 'max-w-5xl',
                    '6xl' => 'max-w-6xl',
                    default => $size,
                };

                return new HtmlString(
                    <<<html
                            <div class="truncate {$cssClass}">
                                {$state}
                            </div>
                        html
                );
            });

            return $this;
        };
    }
}
