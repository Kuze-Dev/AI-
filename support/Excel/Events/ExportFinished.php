<?php

declare(strict_types=1);

namespace Support\Excel\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;

class ExportFinished
{
    use Dispatchable;

    public function __construct(
        public readonly Model $notifiable,
        public readonly string $fileName,
    ) {
    }
}
