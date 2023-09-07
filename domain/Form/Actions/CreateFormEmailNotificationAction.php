<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\DataTransferObjects\FormEmailNotificationData;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;

class CreateFormEmailNotificationAction
{
    public function execute(Form $form, FormEmailNotificationData $formEmailNotificationData): FormEmailNotification
    {
        return $form->formEmailNotifications()->create(
            [
                'to' => $formEmailNotificationData->to,
                'cc' => $formEmailNotificationData->cc,
                'bcc' => $formEmailNotificationData->bcc,
                'sender' => $formEmailNotificationData->sender,
                'sender_name' => $formEmailNotificationData->sender_name,
                'reply_to' => $formEmailNotificationData->reply_to,
                'subject' => $formEmailNotificationData->subject,
                'template' => $formEmailNotificationData->template,
                'has_attachments' => $formEmailNotificationData->has_attachments,
            ]
        );
    }
}
