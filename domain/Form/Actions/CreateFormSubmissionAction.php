<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Blueprint\Enums\FieldType;
use Domain\Form\Mail\FormEmailNotificationMail;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormSubmission;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class CreateFormSubmissionAction
{
    public function execute(Form $form, array $data): ?FormSubmission
    {

        $filesFields = [];
        $schema = $form->blueprint->schema->toArray();

        foreach ($schema['sections'] as $section) {
            foreach ($section->fields as $field) {
                if ($field->type == FieldType::FILE) {
                    $filesFields[] = $field->state_name;
                }
            }
        }

        $attachments = [];

        if (count($filesFields) > 0) {
            foreach ($data as $fields) {
                foreach ($filesFields as $fieldkey) {
                    $value = ($fields[$fieldkey]);

                    if ( ! is_null($value)) {

                        if ( ! Storage::disk('s3')->exists($value)) {
                            abort(422, 'File '.$value.' Not Found');
                        }

                        $attachments[] = $value;
                    }

                }
            }

        }

        $formSubmission = $form->store_submission
            ? $form->formSubmissions()->create(['data' => $data])
            : null;

        foreach ($form->formEmailNotifications as $emailNotification) {
            Mail::send(new FormEmailNotificationMail($emailNotification, $data, $attachments));
        }

        return $formSubmission;
    }
}
