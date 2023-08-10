<?php

declare(strict_types=1);

namespace Domain\Customer\Actions;

use Illuminate\Support\Str;

class GenerateCustomerIDAction
{
    public function execute(): string
    {
        return (string) Str::uuid();
    }
}
