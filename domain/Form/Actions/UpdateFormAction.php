<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\DataTransferObjects\FormData;
use Domain\Form\Models\Form;

class UpdateFormAction
{
    public function __construct(
        protected SyncFormEmailNotificationAction $syncFormEmailNotification
    ) {
    }

    public function execute(Form $form, FormData $formData): Form
    {
        $form->update([
            'blueprint_id' => $formData->blueprint_id,
            'name' => $formData->name,
            'slug' => $formData->slug,
            'store_submission' => $formData->store_submission,
        ]);

        foreach ($formData->form_email_notifications ?? [] as $formEmailNotification) {
            $this->syncFormEmailNotification->execute($form, $formEmailNotification);
        }

        return $form;
    }
}
