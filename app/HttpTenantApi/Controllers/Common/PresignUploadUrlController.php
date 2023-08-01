<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Common;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Spatie\RouteAttributes\Attributes\Post;
use Illuminate\Http\JsonResponse;
use InvalidArgumentException;
use Throwable;

class PresignUploadUrlController
{
    #[Post('/generate/upload-url', name: 'generate.upload-url')]
    public function index(Request $request): JsonResponse
    {
        try {
            $path = match ($request->resource) {
                'forms' => 'forms/',
                default => throw new InvalidArgumentException('resource not supported'),
            };

            $filename = $request->resource_id.'/'.uniqid().'.'.$request->ext;

            $object_key = $path.$filename;
            /** @var \Illuminate\Filesystem\AwsS3V3Adapter */
            $s3 = Storage::disk('s3');

            $client = $s3->getClient();

            $expiry = '+20 minutes';

            $cmd = $client->getCommand('PutObject', [
                'Bucket' => config('filesystems.disks.s3.bucket'),
                'Key' => $object_key,
                'ACL' => 'public-read',
            ]);

            $awsResponse = $client->createPresignedRequest($cmd, $expiry);

            $presignedUrl = (string) $awsResponse->getUri();

            return response()->json([
                'upload_url' => $presignedUrl,
                'object_key' => $object_key,
            ]);
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
