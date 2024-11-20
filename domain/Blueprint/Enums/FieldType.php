<?php

declare(strict_types=1);

namespace Domain\Blueprint\Enums;

use Domain\Blueprint\DataTransferObjects\CheckBoxFieldData;
use Domain\Blueprint\DataTransferObjects\DatetimeFieldData;
use Domain\Blueprint\DataTransferObjects\FileFieldData;
use Domain\Blueprint\DataTransferObjects\MarkdownFieldData;
use Domain\Blueprint\DataTransferObjects\MediaFieldData;
use Domain\Blueprint\DataTransferObjects\RadioFieldData;
use Domain\Blueprint\DataTransferObjects\RelatedResourceFieldData;
use Domain\Blueprint\DataTransferObjects\RepeaterFieldData;
use Domain\Blueprint\DataTransferObjects\RichtextFieldData;
use Domain\Blueprint\DataTransferObjects\SelectFieldData;
use Domain\Blueprint\DataTransferObjects\TextareaFieldData;
use Domain\Blueprint\DataTransferObjects\TextFieldData;
use Domain\Blueprint\DataTransferObjects\TinyEditorData;
use Domain\Blueprint\DataTransferObjects\TipTapEditorData;
use Domain\Blueprint\DataTransferObjects\ToggleFieldData;

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
    case RELATED_RESOURCE = 'related_resource';
    case REPEATER = 'repeater';
    case MEDIA = 'media';
    case CHECKBOX = 'checkbox';
    case RADIO = 'radio';
    case TINYEDITOR = 'tinyeditor';
    case TIPTAPEDITOR = 'tiptap';

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
            self::RELATED_RESOURCE => RelatedResourceFieldData::class,
            self::REPEATER => RepeaterFieldData::class,
            self::MEDIA => MediaFieldData::class,
            self::CHECKBOX => CheckBoxFieldData::class,
            self::RADIO => RadioFieldData::class,
            self::TINYEDITOR => TinyEditorData::class,
<<<<<<< HEAD
=======
            self::TIPTAPEDITOR => TipTapEditorData::class,
>>>>>>> develop
        };
    }
}
