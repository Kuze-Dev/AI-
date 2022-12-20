<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Requests\FormSubmission;

use Illuminate\Foundation\Http\FormRequest;

class FormSubmissionRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var \Domain\Form\Models\Form $form */
        $form = $this->route('form');

        return  $form->blueprint->schema->getValidationRules();
    }
}
