<?php

declare(strict_types=1);

namespace App\Filament\Requests;

use App\Attributes\CurrentApiCustomer;
use Domain\Customer\Models\Customer;
use Illuminate\Container\Attributes\RouteParameter;
use Illuminate\Foundation\Http\FormRequest;

class VerifyEmailRequest extends FormRequest
{
    public function authorize(
        #[RouteParameter('id')] string|null $id,
        #[RouteParameter('hash')] string|null $hash,
        #[CurrentApiCustomer] Customer $customer,
    ) : bool
    {
        if (! hash_equals($id ?? '' , (string) $customer->getKey())) {
            return false;
        }

        return hash_equals($hash ?? '', sha1($customer->getEmailForVerification()));
    }

    public function rules(): array
    {
        return [];
    }
}
