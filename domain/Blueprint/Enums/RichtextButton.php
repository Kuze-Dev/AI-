<?php

declare(strict_types=1);

namespace Domain\Blueprint\Enums;

enum RichtextButton: string
{
    case ATTACH_FILES = 'attachFiles';
    case BLOCKQUOTE = 'blockquote';
    case BOLD = 'bold';
    case BULLET_LIST = 'bulletList';
    case CODE_BLOCK = 'codeBlock';
    case H2 = 'h2';
    case H3 = 'h3';
    case EDIT = 'edit';
    case ITALIC = 'italic';
    case LINK = 'link';
    case ORDERED_LIST = 'orderedList';
    case PREVIEW = 'preview';
    case REDO = 'redo';
    case STRIKE = 'strike';
    case UNDO = 'undo';
}
