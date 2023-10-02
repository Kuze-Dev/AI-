<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Support\ApiCall\Actions\CreateApiCallLogAction;
use Support\ApiCall\DataTransferObjects\ApiCallData;
use Symfony\Component\HttpFoundation\Response;

class ApiCallFrontendMiddleware
{

    public function __construct(
        protected CreateApiCallLogAction $createApiCallLogAction
    ) {
    }
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $this->createApiCallLogAction->execute(
            new ApiCallData(
                requestType: 'web',
                requestUrl: $request->url(),
                requestResponse: $request->all(),
        ));

        return $next($request);
    }
}
