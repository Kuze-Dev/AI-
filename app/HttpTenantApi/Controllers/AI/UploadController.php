<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\AI;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\RouteAttributes\Attributes\Post;

class UploadController extends Controller
{
    #[Post(uri: '/ai/upload', name: 'upload')]
    public function store(Request $request)
    {
        $request->validate([
            'document' => ['required', 'file', 'mimes:pdf,doc,docx,txt'],
        ]);

        $path = $request->file('document')->store('tenant-uploads', 'public');

        return back()->with('success', 'File uploaded successfully: '.$path);
    }
}
