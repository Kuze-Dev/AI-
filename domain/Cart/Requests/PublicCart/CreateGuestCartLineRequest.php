<?php

declare(strict_types=1);

namespace Domain\Cart\Requests\PublicCart;

use Domain\Cart\Requests\CreateCartLineRequest;

class CreateGuestCartLineRequest extends CreateCartLineRequest
{
    public function rules(): array
    {
        $rules = parent::rules();

        $rules['session_id'] = [
            'nullable|string',
        ];

        return $rules;
    }
}
