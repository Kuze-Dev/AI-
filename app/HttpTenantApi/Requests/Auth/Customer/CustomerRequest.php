<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Requests\Auth\Customer;

use Domain\Customer\Models\Customer;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class CustomerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => ['required', Rule::unique(Customer::class), Rule::email(), 'max:255'],
            'mobile' => 'required|string|max:255',
            'birth_date' => 'required|date',
            'password' => Password::required(),
        ];
    }
}
