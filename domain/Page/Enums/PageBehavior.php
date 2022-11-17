<?php

declare(strict_types=1);

namespace Domain\Page\Enums;

enum PageBehavior: string
{
    case PUBLIC = 'public';
    case UNLISTED = 'unlisted';
    case HIDDEN = 'hidden';

    /** @return array<non-empty-string, non-empty-string> */
    public static function toArray(): array
    {
        $cases = [];

        foreach (self::cases() as $case) {
            $cases[$case->value] = $case->value;
        }

        return $cases;
    }
}
