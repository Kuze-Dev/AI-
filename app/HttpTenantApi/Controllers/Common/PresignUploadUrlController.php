<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Common;

use App\HttpTenantApi\Resources\SettingResource;
use Illuminate\Support\Facades\Storage;
use Spatie\LaravelSettings\SettingsContainer;
use Spatie\RouteAttributes\Attributes\Get;
use TiMacDonald\JsonApi\JsonApiResource;

class PresignUploadUrlController
{
    #[Get('/generate/upload-url/{resource}' , name: 'generate.upload-url')]
    public function index(string $resource)
    {
               

        $s3 = Storage::disk('s3');
        dd($s3);
        $adapter = $s3->getDriver()->getAdapter();

        dd($adapter);
        $client = $s3->getDriver()->getAdapter()->getClient();
        $expiry = "+10 minutes";


        $options = ['user-data' => 'user-meta-value'];

        $cmd = $client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => 'path/to/file/',
            'ACL' => 'public-read',
            'Metadata' => $options,
        ]);

        $request = $client->createPresignedRequest($cmd, $expiry);

        $presignedUrl = (string)$request->getUri();
        
        dd($presignedUrl);
    }
}
