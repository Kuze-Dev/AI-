<?php

declare(strict_types=1);

namespace Domain\Tenant\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SuspendTenantException extends Exception
{
    /**
     * Report the exception.
     */
    public function report(): void
    {
        // ...
    }

    /**
     * Render the exception into an HTTP response.
     */
    public function render(Request $request): Response
    {
        return response()->view('errors.suspended-tenant', [
            'title' => 'ACCESS TO THIS PAGE IS RESTRICTED',
            'message' => 'PLEASE CONTACT ADMINISTRATOR.',
        ], 403);
    }
}
