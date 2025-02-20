<?php

declare(strict_types=1);

namespace App\Support\ActivityLog;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

/**
 * @todo for roy list all cases
 */
enum ActivityLogName: string implements HasLabel
{
    case default = 'default';
    case api = 'api';
    case admin = 'admin';
    case auth = 'auth';
    case sync = 'sync';
    case settings = 'settings';
    case email_link_clicked = 'email_link_clicked';

    public function getLabel(): ?string
    {
        return Str::headline($this->value);
    }
}
