<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\Cart;

use Spatie\RouteAttributes\Attributes\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ImageUploadController
{
    #[Post('fileupload-tmp', name: 'fileupload.tmp')]
    public function __invoke(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'images.*' => 'required|image|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $urls = [];

        foreach ($request->file('images') as $image) {
            if ($image->isValid()) {
                $fileName = Str::random(20) . '.' . $image->getClientOriginalExtension();
                $path = Storage::disk('s3')->putFileAs('livewire-tmp', $image, $fileName, 'public');
                $url = Storage::disk('s3')->url($path);
                $urls[] = $url;
            }
        }

        return response()->json(['urls' => $urls], 200);
    }
}
