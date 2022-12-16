<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\DataTransferObjects\FormEmailNotificationData;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;

class SyncFormEmailNotificationAction
{
    public function execute(Form $form, FormEmailNotificationData $formEmailNotificationData): FormEmailNotification
    {
        return $form->formEmailNotifications()->updateOrCreate(
            ['id' => $formEmailNotificationData->id],
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
    }
}
