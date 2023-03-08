<?php

declare(strict_types=1);

namespace Domain\Admin\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Auth\Notifications\VerifyEmail as BaseVerifyMail;

class VerifyEmail extends BaseVerifyMail implements ShouldQueue
{
    use Queueable;
}
