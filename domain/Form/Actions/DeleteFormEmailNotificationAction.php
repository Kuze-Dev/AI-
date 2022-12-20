<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\Models\FormEmailNotification;

class DeleteFormEmailNotificationAction
{
    public function execute(FormEmailNotification $formEmailNotification): ?bool
    {
        return $formEmailNotification->delete();
    }
}
