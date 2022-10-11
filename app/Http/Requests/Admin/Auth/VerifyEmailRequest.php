<?php

declare(strict_types=1);

namespace App\Http\Requests\Admin\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ( ! hash_equals((string) $this->route('id'), (string) $this->user()?->getKey())) {
            return false;
        }

        return ! ( ! hash_equals((string) $this->route('hash'), sha1((string) $this->user()?->getEmailForVerification())));
    }

    public function rules(): array
    {
        return [];
    }
}
