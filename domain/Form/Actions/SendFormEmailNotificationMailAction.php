<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\Mail\FormEmailNotificationMail;
use Domain\Form\Models\FormEmailNotification;
use Illuminate\Support\Facades\Mail;

class SendFormEmailNotificationMailAction
{
    public function execute(FormEmailNotification $emailNotification): void
    {
        Mail::send(new FormEmailNotificationMail($emailNotification));
    }
}
