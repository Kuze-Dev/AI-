<?php

declare(strict_types=1);

namespace App\Support\ActivityLog;

use Domain\Admin\Models\Admin;
use Domain\Customer\Models\Customer;
use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

/**
 * @todo for roy list all cases
 */
enum ActivitySubjectType: string implements HasLabel
{
    case Admin = Admin::class;

    case Customer = Customer::class;

    public function getLabel(): string
    {
        return (string) Str::of($this->value)->classBasename()->headline();
    }
}
