<?php

declare(strict_types=1);

namespace Domain\Discount\Actions;

use Str;

final class AutoGenerateCode
{
    public function __invoke(): string
    {
        return Str::random(8);
    }
}
