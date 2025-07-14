<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as BaseHandler;
use Illuminate\Auth\AuthenticationException;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends BaseHandler
{
    #[\Override]
    protected function unauthenticated($request, AuthenticationException $exception): Response
    {
        if ($request->is('api/*')) {
            return response()->json(['message' => 'Forbidden'], Response::HTTP_FORBIDDEN);
        }

        return parent::unauthenticated($request, $exception);
    }

    public function register(): void
    {
        // You can still register custom reportables here
        $this->reportable(function (Throwable $e) {
            // Log or handle other exceptions here
        });
    }
}
