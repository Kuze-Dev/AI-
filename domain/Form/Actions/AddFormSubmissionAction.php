<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Form\Models\Form;
use Domain\Form\Models\FormSubmission;

class AddFormSubmissionAction
{
    public function execute(Form $form,  array $data): ?FormSubmission
    {
        if ( ! $form->store_submission) {
            return null;
        }

        return $form->formSubmissions()->create([
            'data' => $data,
        ]);
    }
}
