<?php

declare(strict_types=1);

namespace Tests\Fixtures;

use Spatie\LaravelSettings\Settings;

class TestSettings extends Settings
{
    public string $property1;

    public string $property2;

    #[\Override]
    public static function group(): string
    {
        return 'test_group';
    }
}
