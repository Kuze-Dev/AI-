<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\DataTransferObjects\FormData;
use Domain\Form\Models\Form;

class CreateFormAction
{
    public function __construct(
        protected CreateFormEmailNotificationAction $createFormEmailNotification
    ) {}

    public function execute(FormData $formData): Form
    {
        $form = Form::create([
            'blueprint_id' => $formData->blueprint_id,
            'locale' => $formData->locale,
            'name' => $formData->name,
            'store_submission' => $formData->store_submission,
            'uses_captcha' => $formData->uses_captcha,
        ]);

        foreach ($formData->form_email_notifications ?? [] as $formEmailNotification) {
            $this->createFormEmailNotification->execute($form, $formEmailNotification);
        }

        $form->sites()
            ->attach($formData->sites);

        return $form;
    }
}
