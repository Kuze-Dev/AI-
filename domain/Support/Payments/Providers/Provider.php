<?php

namespace Domain\Support\Payments\Providers;

abstract class Provider
{
    protected $name;

    public function getName()
    {
        return $this->name;
    }
}
