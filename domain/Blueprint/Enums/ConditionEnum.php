<?php

declare(strict_types=1);

namespace Domain\Blueprint\Enums;

enum ConditionEnum: string
{
    case Equals = '=';
    case NotEquals = '!=';
    case GreaterThan = '>';
    case GreaterThanOrEqual = '>=';
    case LessThan = '<';
    case LessThanOrEqual = '<=';
    case InArray = 'in_array';
    case NotInArray = 'not_in_array';

    public function label(): string
    {
        return match ($this) {
            self::Equals => 'Equals',
            self::NotEquals => 'Not Equals',
            self::GreaterThan => 'Greater Than',
            self::GreaterThanOrEqual => 'Greater Than or Equal',
            self::LessThan => 'Less Than',
            self::LessThanOrEqual => 'Less Than or Equal',
            self::InArray => 'In Array',
            self::NotInArray => 'Not In Array',
        };
    }

    public static function fromValue(string $value): ?self
    {
        return self::tryFrom($value);
    }

    public function evaluate(mixed $value, mixed $target): bool
    {
        return match ($this) {
            self::Equals => $value === $target,
            self::NotEquals => $value !== $target,
            self::GreaterThan => $value > $target,
            self::GreaterThanOrEqual => $value >= $target,
            self::LessThan => $value < $target,
            self::LessThanOrEqual => $value <= $target,
            self::InArray => in_array($value, explode(',', (string) $target), true),
            self::NotInArray => ! in_array($value, explode(',', (string) $target), true),
        };
    }
}
