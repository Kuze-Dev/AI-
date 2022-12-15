<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\Mail\FormEmailNotificationMail;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;

class CreateFormSubmissionAction
{
    public function execute(Form $form, array $data): ?FormSubmission
    {
        $formSubmission = $form->store_submission
            ? $form->formSubmissions()->create(['data' => $data])
            : null;

        foreach ($form->formEmailNotifications as $emailNotification) {
            Mail::send(new FormEmailNotificationMail($emailNotification));
        }

        return $formSubmission;
    }
}
