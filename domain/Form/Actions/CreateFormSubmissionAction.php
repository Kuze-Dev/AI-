<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\Models\Form;
use Domain\Form\Models\FormSubmission;

class CreateFormSubmissionAction
{
    public function __construct(
        protected SendFormEmailNotificationMailAction $sendFormEmailNotificationMailAction,
        protected AddFormSubmissionAction $addFormSubmissionAction
    ) {
    }

    public function execute(Form $form,  array $data): ?FormSubmission
    {
        foreach ($form->formEmailNotifications as $emailNotification) {
            $this->sendFormEmailNotificationMailAction->execute($emailNotification);
        }

        return $this->addFormSubmissionAction->execute($form,   $data);
    }
}
