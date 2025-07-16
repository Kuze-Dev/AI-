<?php

declare(strict_types=1);

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as BaseHandler;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class Handler extends BaseHandler
{
    #[\Override]
    protected function unauthenticated($request, AuthenticationException $exception): Response
    {
        if ($request->is('api/*')) {
            return response()->json(['message' => 'Unauthorized access'], Response::HTTP_UNAUTHORIZED);
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
