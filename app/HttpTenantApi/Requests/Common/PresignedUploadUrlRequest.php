<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Requests\Common;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PresignedUploadUrlRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'ext' => 'required',
            'acl' => [
                'required', Rule::in(
                    [
                        'private',
                        'public-read',
                        'public-read-write',
                        'authenticated-read',
                        'aws-exec-read',
                        'bucket-owner-read',
                        'bucket-owner-full-control',
                    ]
                ),
            ],
        ];
    }
}
