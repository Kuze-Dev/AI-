<?php

declare(strict_types=1);

namespace Domain\Blueprint\Enums;

enum MarkdownButton: string
{
    case ATTACH_FILES = 'attachFiles';
    case BOLD = 'bold';
    case BULLET_LIST = 'bulletList';
    case CODE_BLOCK = 'codeBlock';
    case EDIT = 'edit';
    case ITALIC = 'italic';
    case LINK = 'link';
    case ORDERED_LIST = 'orderedList';
    case PREVIEW = 'preview';
    case STRIKE = 'strike';
}
