<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Requests\FormSubmission;

use App\Settings\FormSettings;
use Illuminate\Foundation\Http\FormRequest;
use Support\Captcha\CaptchaRule;

class FormSubmissionRequest extends FormRequest
{
    public function rules(): array
    {
        /** @var \Domain\Form\Models\Form $form */
        $form = $this->route('form');

        $rules = $form->blueprint->schema->getValidationRules();

        if ($form->uses_captcha && app(FormSettings::class)->provider) {
            $rules['captcha_token'] = ['required', new CaptchaRule($this->ip())];
        }

        return $rules;
    }
}
