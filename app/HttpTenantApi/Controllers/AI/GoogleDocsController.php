<?php

declare(strict_types=1);

namespace App\HttpTenantApi\Controllers\AI;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Spatie\RouteAttributes\Attributes\Post;

class GoogleDocsController extends Controller
{
    #[Post(uri: '/ai/google-docs', name: 'googledocs.store')]
    public function store(Request $request)
    {
        $request->validate([
            'docs_url' => ['required', 'url', 'regex:/^https:\/\/docs\.google\.com/'],
        ]);

        // Example: persist to DB if needed
        // TenantDocs::create(['tenant_id' => tenant()->id, 'url' => $request->docs_url]);

        return back()->with('success', 'Google Docs URL saved: ' . $request->docs_url);
    }
}
