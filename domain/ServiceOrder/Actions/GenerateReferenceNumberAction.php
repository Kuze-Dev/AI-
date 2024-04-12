<?php

declare(strict_types=1);

namespace Domain\ServiceOrder\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class GenerateReferenceNumberAction
{
    /**
     * @param  class-string<Model>  $model
     */
    public function execute(string $model): string
    {
        /** @var array|false $words */
        $words = preg_split(
            '/(?=[A-Z])/',
            Str::of($model)
                ->classBasename()
                ->value(),
            -1,
            PREG_SPLIT_NO_EMPTY
        );

        /** @var string $prefix */
        $prefix = is_array($words)
            ? implode('', array_map(fn ($word) => substr((string) $word, 0, 1), $words))
            : '';

        $referenceNumber = $prefix.now()->format('ymd');

        /** @var int $count */
        $count = $model::where(
            'reference',
            'LIKE',
            "$referenceNumber%"
        )
            ->count() + 1;

        return Str::upper(
            $referenceNumber.
            Str::of((string) $count)->padLeft(4, '0')
        );
    }
}
