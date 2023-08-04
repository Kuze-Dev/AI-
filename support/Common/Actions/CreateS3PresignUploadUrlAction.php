<?php

declare(strict_types=1);

namespace Support\Common\Actions;

use Illuminate\Support\Facades\Storage;
use Support\Common\DataTransferObjects\CreatePresignUploadUrlData;
use Support\Common\DataTransferObjects\PresignUrlData;

class CreateS3PresignUploadUrlAction
{
    public function execute(CreatePresignUploadUrlData $presignedUrlData): PresignUrlData
    {

        $filename = $presignedUrlData->resource_id.'/'.uniqid().'.'.$presignedUrlData->ext;

        $object_key = $presignedUrlData->resource.'/'.$filename;

        while (Storage::disk('s3')->exists($object_key)) {

            $filename = $presignedUrlData->resource_id.'/'.uniqid().'.'.$presignedUrlData->ext;

            $object_key = $presignedUrlData->resource.'/'.$filename;
        }

        /** @var \Illuminate\Filesystem\AwsS3V3Adapter */
        $s3 = Storage::disk('s3');

        $client = $s3->getClient();

        $expiry = '+20 minutes';

        $cmd = $client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $object_key,
            'ACL' => 'private-read',
        ]);

        $awsResponse = $client->createPresignedRequest($cmd, $expiry);

        $presignedUrl = (string) $awsResponse->getUri();

        return new PresignUrlData(
            presigned_url: $presignedUrl,
            object_key: $object_key
        );
    }
}
