<?php

declare(strict_types=1);

namespace App\Features;

use Domain\Tenant\Models\Tenant;
use Illuminate\Support\Str;
use Laravel\Pennant\Feature;

final readonly class GroupFeature
{
    /**
     * @param  class-string<FeatureContract>  $base
     * @param  array<int, GroupFeatureExtra>  $extra
     */
    public function __construct(
        public string $base,
        public array $extra = [],
    ) {

        if (
            collect($this->extra)
                ->map(fn (GroupFeatureExtra $extra) => $extra->groupLabel)
                ->duplicates()
                ->isNotEmpty()
        ) {
            throw new \Exception('Duplicate extra groupLabel on ['.$this->base.'].');
        }

    }

    public function getFeature(): FeatureContract
    {
        return app($this->base);
    }

    public function fieldName(): string
    {
        return (string) Str::of($this->base)->replace('\\', '_');
    }

    public function enabled(?Tenant $tenant): bool
    {
        if ($tenant === null) {
            return false;
        }

        return Feature::for($tenant)->active($this->getFeature()->name);
    }
}
