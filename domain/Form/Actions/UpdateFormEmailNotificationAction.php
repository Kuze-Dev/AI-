<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\DataTransferObjects\FormEmailNotificationData;
use Domain\Form\Models\FormEmailNotification;

class UpdateFormEmailNotificationAction
{
    public function execute(FormEmailNotification $formEmailNotification, FormEmailNotificationData $formEmailNotificationData): FormEmailNotification
    {
        $formEmailNotification->update(
            [
                'to' => $formEmailNotificationData->to,
                'cc' => $formEmailNotificationData->cc,
                'bcc' => $formEmailNotificationData->bcc,
                'sender' => $formEmailNotificationData->sender,
                'reply_to' => $formEmailNotificationData->reply_to,
                'subject' => $formEmailNotificationData->subject,
                'template' => $formEmailNotificationData->template,
            ]
        );

        return $formEmailNotification;
    }
}
