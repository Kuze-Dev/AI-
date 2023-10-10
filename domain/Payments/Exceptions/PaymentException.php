<?php

declare(strict_types=1);

namespace Domain\Payments\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Request;

class PaymentException extends Exception
{
    public function report(): bool
    {
        return false;
    }

    public function render(): JsonResponse
    {
        return Request::ajax()
            ? response()->json(['message' => $this->getMessage()], 500)
            : abort(500);
    }
}
