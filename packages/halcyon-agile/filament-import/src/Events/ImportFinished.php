<?php

declare(strict_types=1);

namespace HalcyonAgile\FilamentImport\Events;

use Illuminate\Database\Eloquent\Model;

readonly class ImportFinished
{
    public function __construct(
        public Model $notifiable,
    ) {}
}
