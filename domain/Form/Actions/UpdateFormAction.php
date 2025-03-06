<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\DataTransferObjects\FormData;
use Domain\Form\Models\Form;
use Illuminate\Support\Arr;

class UpdateFormAction
{
    public function __construct(
        protected CreateFormEmailNotificationAction $createFormEmailNotification,
        protected UpdateFormEmailNotificationAction $updateFormEmailNotification,
        protected DeleteFormEmailNotificationAction $deleteFormEmailNotification
    ) {}

    public function execute(Form $form, FormData $formData): Form
    {
        $form->update([
            'name' => $formData->name,
            'store_submission' => $formData->store_submission,
            'uses_captcha' => $formData->uses_captcha,
        ]);

        foreach ($form->formEmailNotifications->whereNotIn('id', Arr::pluck($formData->form_email_notifications, 'id')) as $formEmailNotification) {
            $this->deleteFormEmailNotification->execute($formEmailNotification);
        }

        foreach ($formData->form_email_notifications as $formEmailNotificationData) {
            if ($formEmailNotification = $form->formEmailNotifications->firstWhere('id', $formEmailNotificationData->id)) {
                $this->updateFormEmailNotification->execute($formEmailNotification, $formEmailNotificationData);

                continue;
            }

            $this->createFormEmailNotification->execute($form, $formEmailNotificationData);
        }

        $form->sites()
            ->sync($formData->sites);

        return $form;
    }
}
