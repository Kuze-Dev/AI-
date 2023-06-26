<?php

namespace App\Services\Payments;

use Money\Money;

class PaymentDetails
{
    private $id;
    private Money $amount;
    private $raw;

    public function __construct($id, Money $amount, $raw)
    {
        $this->id = $id;
        $this->amount = $amount;
        $this->raw = $raw;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAmount()
    {
        return $this->amount;
    }

    public function getRaw()
    {
        return $this->raw;
    }
}
