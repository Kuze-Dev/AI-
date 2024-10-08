<?php

declare(strict_types=1);

namespace Domain\Blueprint\Enums;

enum TiptapTools: string
{
    case heading = 'heading';
    case BLOCKQUOTE = 'blockquote';
    case BOLD = 'bold';
    case BULLET_LIST = 'bullet-list';
    case CODE_BLOCK = 'code-block';
    case CODE = 'code';
    case HR = 'hr';
    case ITALIC = 'italic';
    case UNDERLINE = 'underline';
    case LINK = 'link';
    case ORDERED_LIST = 'ordered-list';
    case CHECKED_LIST = 'checked-list';
    case MEDIA = 'media';
    case OEMBED = 'oembed';
    case TABLE = 'table';
    case GRID = 'grid';
    case GRID_BUILDER = 'grid-builder';
    case DETAILS = 'details';
    case HURDLE = 'hurdle';
    case STRIKE = 'strike';
    case UNDO = 'undo';
    case SUPER_SCRIPT = 'superscript';
    case SUB_SCRIPT = 'subscript';
    case LEAD = 'lead';
    case SMALL = 'small';
    case COLOR = 'color';
    case HIGH_LIGHT = 'highlight';
    case ALIGN_LEFT = 'align-left';
    case ALIGN_CENTER = 'align-center';
    case ALIGN_RIGHT = 'align-right';
    case SOURCE = 'source';

}
