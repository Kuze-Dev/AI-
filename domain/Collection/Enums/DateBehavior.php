<?php 

declare(strict_types=1);

namespace App\Domain\Collection\Enums;

enum DateBehavior: string 
{
    case Public = 'public';
    case Private = 'private';
    case Unlisted = 'unlisted';
}