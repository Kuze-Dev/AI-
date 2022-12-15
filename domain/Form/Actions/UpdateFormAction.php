<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\DataTransferObjects\FormData;
use Domain\Form\DataTransferObjects\FormEmailNotificationData;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;

class UpdateFormAction
{
    public function execute(Form $form, FormData $formData): Form
    {
        $form->update([
            'blueprint_id' => $formData->blueprint_id,
            'name' => $formData->name,
            'slug' => $formData->slug,
            'store_submission' => $formData->store_submission,
        ]);

        foreach ($formData->form_email_notifications ?? [] as $formEmailNotification) {
            self::formEmailNotification($formEmailNotification, $form);
        }

        return $form;
    }

    protected static function formEmailNotification(FormEmailNotificationData $formEmailNotificationData, Form $form): void
    {
        $newData = [
            'to' => $formEmailNotificationData->to,
            'cc' => $formEmailNotificationData->cc,
            'bcc' => $formEmailNotificationData->bcc,
            'sender' => $formEmailNotificationData->sender,
            'reply_to' => $formEmailNotificationData->reply_to,
            'subject' => $formEmailNotificationData->subject,
            'template' => $formEmailNotificationData->template,
        ];

        if ($formEmailNotificationData->id === null) {
            $form->formEmailNotifications()->create($newData);
        } else {
            FormEmailNotification::whereKey($formEmailNotificationData->id)
                ->whereBelongsTo($form)
                ->update($newData);
        }
    }
}
