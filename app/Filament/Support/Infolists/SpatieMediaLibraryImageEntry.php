<?php

declare(strict_types=1);

namespace App\Filament\Support\Infolists;

use Closure;
use Illuminate\Database\Eloquent\Model;

/**
 * issue on using this on infolist action slideOver
 */
class SpatieMediaLibraryImageEntry extends \Filament\Infolists\Components\SpatieMediaLibraryImageEntry
{
    protected Model|string|Closure|null $model = null;

    // additional
    public function model(Model|string|Closure|null $model = null): static
    {
        $this->model = $model;

        return $this;
    }

    // override
    #[\Override]
    public function getRecord(): ?Model
    {
        $model = $this->evaluate($this->model);

        if ($model instanceof Model) {
            return $model;
        }

        if (is_string($model)) {
            return null;
        }

        return $this->getContainer()->getRecord();
    }
}
