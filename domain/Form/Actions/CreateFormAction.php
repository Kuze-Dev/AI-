<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\DataTransferObjects\FormData;
use Domain\Form\Models\Form;

class CreateFormAction
{
    public function __construct(
        protected AddFormEmailNotificationAction $addFormEmailNotification
    ) {
    }

    public function execute(FormData $formData): Form
    {
        $form = Form::create([
            'blueprint_id' => $formData->blueprint_id,
            'name' => $formData->name,
            'slug' => $formData->slug,
            'store_submission' => $formData->store_submission,
        ]);

        foreach ($formData->form_email_notifications ?? [] as $formEmailNotification) {
            $this->addFormEmailNotification->execute($form, $formEmailNotification);
        }

        return $form;
    }
}
