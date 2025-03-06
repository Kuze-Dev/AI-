<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Support\ApiCall\Actions\CreateApiCallLogAction;
use Support\ApiCall\DataTransferObjects\ApiCallData;
use Symfony\Component\HttpFoundation\Response;

class ApiCallTrackMiddleware
{
    public function __construct(
        protected CreateApiCallLogAction $createApiCallLogAction
    ) {}

    /**
     * Handle an incoming request.
     *
     * @param  Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // dd($request->getPathInfo());
        $isApi = Str::startsWith($request->getPathInfo(), '/api');

        $this->createApiCallLogAction->execute(
            new ApiCallData(
                requestType: $isApi ? 'api' : 'admin',
                requestUrl: $request->url(),
                requestResponse: $request->all(),
            )
        );

        return $next($request);
    }
}
