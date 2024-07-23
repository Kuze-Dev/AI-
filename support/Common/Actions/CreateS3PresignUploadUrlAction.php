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

        $filename = uniqid().'.'.$presignedUrlData->ext;

        $object_key = 'tmp/'.$filename;

        while (Storage::disk(config('filament.default_filesystem_disk'))->exists($object_key)) {

            $filename = uniqid().'.'.$presignedUrlData->ext;

            $object_key = 'tmp/'.$filename;
        }

        /** @var \Illuminate\Filesystem\AwsS3V3Adapter */
        $s3 = Storage::disk(config('filament.default_filesystem_disk'));

        $client = $s3->getClient();

        $expiry = '+20 minutes';

        $cmd = $client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $object_key,
            'ACL' => $presignedUrlData->acl,
        ]);

        $awsResponse = $client->createPresignedRequest($cmd, $expiry);

        $presignedUrl = (string) $awsResponse->getUri();

        return new PresignUrlData(
            presigned_url: $presignedUrl,
            object_key: $object_key
        );
    }
}
