<?php

declare(strict_types=1);

namespace App\Features;

use Illuminate\Support\Str;

final readonly class GroupFeatureExtra
{
    /**
     * @param  array<int, class-string<FeatureContract>>  $extra
     * @param  non-empty-string|null  $groupLabel
     */
    public function __construct(
        public array $extra,
        public ?string $groupLabel = null,
    ) {
    }

    public function fieldName(): string
    {
        if ($this->groupLabel === null) {
            return '';
        }

        return '_'.Str::of($this->groupLabel)->slug();
    }

    /**
     * @return array<string, string>
     */
    public function getOptions(): array
    {
        return collect($this->extra)
            ->map(fn (string $extra) => app($extra))
            ->mapWithKeys(fn (FeatureContract $extra) => [$extra::class => $extra->getLabel()])
            ->toArray();
    }
}
