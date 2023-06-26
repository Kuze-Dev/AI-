<?php

namespace App\Services\Payments\Exceptions;

use Exception;
use Illuminate\Support\Facades\Request;

class PaymentException extends Exception
{
    public function report()
    {
        return false;
    }

    public function render()
    {
        return Request::ajax()
            ? response()->json(['message' => $this->getMessage()], 500)
            : abort(500);
    }
}
