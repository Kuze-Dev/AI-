<?php

declare(strict_types=1);

namespace Support\Excel\Events;

use Illuminate\Database\Eloquent\Model;

class ImportFinished
{
    public function __construct(
        public readonly Model $notifiable,
    ) {
    }
}
