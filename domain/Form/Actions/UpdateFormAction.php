<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\DataTransferObjects\FormData;
use Domain\Form\Models\Form;
use Illuminate\Support\Arr;

class UpdateFormAction
{
    public function __construct(
        protected AddFormEmailNotificationAction $addFormEmailNotification,
        protected UpdateFormEmailNotificationAction $updateFormEmailNotification,
        protected DeleteFormEmailNotificationAction $deleteFormEmailNotification
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

        foreach ($form->formEmailNotifications()->whereNotIn('id', Arr::pluck($formData->form_email_notifications, 'id'))->get() as $formEmailNotification) {
            $this->deleteFormEmailNotification->execute($formEmailNotification);
        }

        foreach ($formData->form_email_notifications as $formEmailNotificationData) {
            if ($formEmailNotification = $form->formEmailNotifications->firstWhere('id', $formEmailNotificationData->id)) {
                $this->updateFormEmailNotification->execute($formEmailNotification, $formEmailNotificationData);

                continue;
            }

            $this->addFormEmailNotification->execute($form, $formEmailNotificationData);
        }

        return $form;
    }
}
