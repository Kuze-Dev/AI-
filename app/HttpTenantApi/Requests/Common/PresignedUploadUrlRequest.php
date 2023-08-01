<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;

class PresignedUploadUrlRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'resource' => 'required',
            'resource_id' => 'required',
            'ext' => 'required',

        ];
    }
}
