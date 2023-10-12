<?php

declare(strict_types=1);

namespace Domain\Blueprint\Enums;

enum ManipulationFormat: string
{
    case JPG = 'jpg';
    case PJPG = 'pjpg';
    case PNG = 'png';
    case GIF = 'gif';
    case WEBP = 'webp';
    case AVIF = 'avif';
    case TIFF = 'tiff';
}
