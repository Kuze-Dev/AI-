<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\DataTransferObjects\FormData;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormEmailNotification;

class CreateFormAction
{
    public function execute(FormData $formData): Form
    {
        $form = Form::create([
            'blueprint_id' => $formData->blueprint_id,
            'name' => $formData->name,
            'slug' => $formData->slug,
            'store_submission' => $formData->store_submission,
        ]);

        foreach ($formData->form_email_notifications ?? [] as $formEmailNotification) {
            FormEmailNotification::create([
                'form_id' => $form->id,
                'recipient' => $formEmailNotification->recipient,
                'cc' => $formEmailNotification->cc,
                'bcc' => $formEmailNotification->bcc,
                'reply_to' => $formEmailNotification->reply_to,
                'sender' => $formEmailNotification->sender,
                'template' => $formEmailNotification->template,
            ]);
        }

        return $form;
    }
}
