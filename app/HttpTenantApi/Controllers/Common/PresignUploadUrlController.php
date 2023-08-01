<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Common;

use App\HttpTenantApi\Resources\SettingResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Livewire\GenerateSignedUploadUrl;
use Spatie\LaravelSettings\SettingsContainer;
use Spatie\RouteAttributes\Attributes\Get;
use Spatie\RouteAttributes\Attributes\Post;
use TiMacDonald\JsonApi\JsonApiResource;

class PresignUploadUrlController
{
    #[Post('/generate/upload-url' , name: 'generate.upload-url')]
    public function index(Request $request)
    {          
       $path = match ($request->resource) {
            'forms' => 'forms/'
        };
        
        $filename = $request->resource_id.'/'.uniqid().'.'.$request->ext;
       
        $object_key = $path.$filename;
        $s3 = Storage::disk('s3');

        $client = $s3->getClient();
        $expiry = "+20 minutes";

        $cmd = $client->getCommand('PutObject', [
            'Bucket' => config('filesystems.disks.s3.bucket'),
            'Key' => $object_key,
            'ACL' => 'public-read',
        ]);

        $request = $client->createPresignedRequest($cmd, $expiry);

        $presignedUrl = (string)$request->getUri();
        
        return response()->json([
            'upload_url' => $presignedUrl,
            'object_key' => $object_key,
        ]);
        
    }
}
