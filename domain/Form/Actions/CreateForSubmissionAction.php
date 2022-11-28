<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\DataTransferObjects\ForSubmissionData;
use Domain\Form\Mail\FormEmailNotificationMail;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;

class CreateForSubmissionAction
{
    public function execute(ForSubmissionData $forSubmissionData): ?FormSubmission
    {
        /** @var \Domain\Form\Models\Form $form */
        $form = Form::whereKey($forSubmissionData->form_id)->first();

        $formSubmission = null;

        if ($form->store_submission) {
            $formSubmission = FormSubmission::create([
                'form_id' => $forSubmissionData->form_id,
                'data' => $forSubmissionData->data,
            ]);
        }

        foreach ($form->formEmailNotifications as $emailNotification) {
            Mail::send(new FormEmailNotificationMail($emailNotification));
        }

        return  $formSubmission;
    }
}
