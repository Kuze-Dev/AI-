<?php

declare(strict_types=1);

namespace Domain\Blueprint\Enums;

use Domain\Blueprint\DataTransferObjects\DatetimeFieldData;
use Domain\Blueprint\DataTransferObjects\FileFieldData;
use Domain\Blueprint\DataTransferObjects\MarkdownFieldData;
use Domain\Blueprint\DataTransferObjects\RichtextFieldData;
use Domain\Blueprint\DataTransferObjects\SelectFieldData;
use Domain\Blueprint\DataTransferObjects\TextareaFieldData;
use Domain\Blueprint\DataTransferObjects\TextFieldData;
use Domain\Blueprint\DataTransferObjects\ToggleFieldData;
use InvalidArgumentException;

enum FieldType: string
{
    case DATETIME = 'datetime';
    case FILE = 'file';
    case MARKDOWN = 'markdown';
    case RICHTEXT = 'richtext';
    case SELECT = 'select';
    case TEXTAREA = 'textarea';
    case TEXT = 'text';
    case EMAIL = 'email';
    case NUMBER = 'number';
    case TEL = 'tel';
    case URL = 'url';
    case PASSWORD = 'password';
    case TOGGLE = 'toggle';

    public function getFieldDataClass(): string
    {
        return match ($this) {
            self::DATETIME => DatetimeFieldData::class,
            self::FILE => FileFieldData::class,
            self::MARKDOWN => MarkdownFieldData::class,
            self::RICHTEXT => RichtextFieldData::class,
            self::SELECT => SelectFieldData::class,
            self::TEXTAREA => TextareaFieldData::class,
            self::TEXT,
            self::EMAIL,
            self::NUMBER,
            self::TEL,
            self::URL,
            self::PASSWORD => TextFieldData::class,
            self::TOGGLE => ToggleFieldData::class,
            default => throw new InvalidArgumentException("`FieldData` class for `{$this->value}` is not specified.")
        };
    }
}
