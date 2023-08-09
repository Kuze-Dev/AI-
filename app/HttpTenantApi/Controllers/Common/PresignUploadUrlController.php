<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Common;

use App\HttpTenantApi\Requests\Common\PresignedUploadUrlRequest;
use Spatie\RouteAttributes\Attributes\Post;
use Illuminate\Http\JsonResponse;
use Support\Common\Actions\CreateS3PresignUploadUrlAction;
use Support\Common\DataTransferObjects\CreatePresignUploadUrlData;
use Throwable;

class PresignUploadUrlController
{
    #[Post('/generate/upload-url', name: 'generate.upload-url')]
    public function index(PresignedUploadUrlRequest $request): JsonResponse
    {
        try {

            $presignedData = app(CreateS3PresignUploadUrlAction::class)->execute(
                presignedUrlData: CreatePresignUploadUrlData::fromArray($request->toArray())
            );

            return response()->json(
                $presignedData->toArray(),
                200
            );
        } catch (Throwable $th) {

            return response()->json(
                [
                    'message' => $th->getMessage(),
                ],
                422
            );
        }

    }
}
