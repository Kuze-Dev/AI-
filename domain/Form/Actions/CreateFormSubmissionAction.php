<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\Mail\FormEmailNotificationMail;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;

class CreateFormSubmissionAction
{
    public function __construct(
        protected AddFormSubmissionAction $addFormSubmissionAction
    ) {
    }

    public function execute(Form $form,  array $data): ?FormSubmission
    {
        foreach ($form->formEmailNotifications as $emailNotification) {
            Mail::send(new FormEmailNotificationMail($emailNotification));
        }

        return $this->addFormSubmissionAction->execute($form,   $data);
    }
}
