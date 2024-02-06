<?php

declare(strict_types=1);

namespace Domain\Tenant\Exceptions;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SuspendTenantException extends Exception
{
    public function report(): void
    {
    }

    public function render(Request $request): Response
    {
        return response()->view('errors.suspended-tenant', [
            'title' => trans('ACCESS TO THIS PAGE IS RESTRICTED'),
            'message' => trans('PLEASE CONTACT ADMINISTRATOR.'),
        ], 403);
    }
}
