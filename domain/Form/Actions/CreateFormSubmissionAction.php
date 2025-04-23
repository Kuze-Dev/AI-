<?php

declare(strict_types=1);

namespace Domain\Form\Actions;

use Domain\Blueprint\Enums\FieldType;
use Domain\Form\Mail\FormEmailNotificationMail;
use Domain\Form\Models\Form;
use Domain\Form\Models\FormSubmission;
use Filament\Notifications\Actions\Action;
use Filament\Notifications\Notification;
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
                if ($field->type === FieldType::FILE) {
                    $filesFields[] = $field->state_name;
                }
            }
        }

        $attachments = [];

        if (count($filesFields) > 0) {
            foreach ($data as $field_key => $fields) {
                foreach ($filesFields as $fieldkey) {
                    $value = ($fields[$fieldkey]);

                    if (! is_null($value)) {

                        if (! Storage::disk(config('filament.default_filesystem_disk'))->exists($value)) {
                            abort(422, 'File '.$value.' Not Found');
                        } else {

                            $objectkey = 'uploads/forms/'.$form->id.'/'.basename((string) $value);

                            Storage::disk(config('filament.default_filesystem_disk'))->move($value, $objectkey);

                            $data[$field_key][$fieldkey] = $objectkey;

                        }

                        $attachments[] = $objectkey;
                    }

                }
            }

        }

        /** @var null|FormSubmission */
        $formSubmission = $form->store_submission
            ? $form->formSubmissions()->create(['data' => $data])
            : null;

        foreach ($form->formEmailNotifications as $emailNotification) {

            try {

                Mail::send(new FormEmailNotificationMail($emailNotification, $data, $attachments, $formSubmission?->id));

            } catch (\Throwable $th) {

                foreach (super_users() as $admin) {
                    Notification::make()
                        ->danger()
                        ->title('Error Sending Email | '.$form->name)
                        ->body($th->getMessage())
                        ->actions([
                            Action::make('View Form')
                                ->url(route('filament.tenant.resources.forms.edit', [
                                    'record' => $form,
                                ]))
                                ->button(),

                        ])
                        ->sendToDatabase($admin);
                }

            }

        }

        return $formSubmission;
    }
}
