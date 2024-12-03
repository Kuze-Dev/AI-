<?php

declare(strict_types=1);

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class CMSSettings extends Settings
{
    public ?string $deploy_hook = null;

    public ?string $front_end_domain = null;

<<<<<<< HEAD
    #[\Override]
=======
    public ?string $media_blueprint_id = null;

>>>>>>> develop
    public static function group(): string
    {
        return 'cms';
    }
}
