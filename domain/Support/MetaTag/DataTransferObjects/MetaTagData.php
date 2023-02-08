<?php 

declare(strict_types=1);

namespace Domain\Support\MetaTag\DataTransferObjects;

use Illuminate\Database\Eloquent\Model;

class MetaTagData
{
    public function __construct(
        public readonly Model $model,
        public readonly ?string $meta_title = null,
        public readonly ?string $meta_author = null,
        public readonly ?string $meta_description = null,
        public readonly ?string $meta_keywords = null
    ) {
        
    }
}