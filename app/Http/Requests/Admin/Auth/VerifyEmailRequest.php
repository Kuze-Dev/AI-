<?php

namespace App\Http\Requests\Admin\Auth;

use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        if (! hash_equals((string) $this->route('id'), (string) $this->user()?->getKey())) {
            return false;
        }

        if (! hash_equals((string) $this->route('hash'), sha1((string) $this->user()?->getEmailForVerification()))) {
            return false;
        }

        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
