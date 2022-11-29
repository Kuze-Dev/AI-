<?php

declare(strict_types=1);

namespace App\Filament\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @property-read string|null $id
 * @property-read string|null $hash
 */
class VerifyEmailRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ( ! hash_equals($this->id ?? '', (string) $this->user()?->getKey())) {
            return false;
        }

        return hash_equals($this->hash ?? '', sha1((string) $this->user()?->getEmailForVerification()));
    }

    public function rules(): array
    {
        return [];
    }
}
