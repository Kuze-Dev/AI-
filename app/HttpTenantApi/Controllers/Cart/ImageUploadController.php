<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use Spatie\RouteAttributes\Attributes\Post;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ImageUploadController
{
    #[Post('fileupload-tmp', name: 'fileupload.tmp')]
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        if ($request->file('image')->isValid()) {
            $file = $request->file('image');
            $fileName = Str::random(20) . '.' . $file->getClientOriginalExtension();
            $path = Storage::disk('s3')->putFileAs('livewire-tmp', $file, $fileName, 'public');

            $url = Storage::disk('s3')->url($path);

            return response()->json(['url' => $url], 200);
        }

        return response()->json(['error' => 'File upload failed'], 500);
    }
}
