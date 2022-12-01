<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\DataTransferObjects\FormEmailNotificationData;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;

class AddFormEmailNotificationsAction
{
    public function execute(Form $form, FormEmailNotificationData $formEmailNotificationData): FormEmailNotification
    {
        return $form->formEmailNotifications()->create([
            'recipient' => $formEmailNotificationData->recipient,
            'cc' => $formEmailNotificationData->cc,
            'bcc' => $formEmailNotificationData->bcc,
            'reply_to' => $formEmailNotificationData->reply_to,
            'sender' => $formEmailNotificationData->sender,
            'template' => $formEmailNotificationData->template,
        ]);
    }
}
