<?php

declare(strict_types=1);

namespace Domain\Cart\Actions;

use Domain\Cart\DataTransferObjects\UpdateCartLineData;
use Domain\Cart\Models\CartLine;
use Domain\Media\Actions\CreateMediaFromS3UrlAction;
use Exception;
use Illuminate\Support\Facades\DB;
use Spatie\MediaLibrary\MediaCollections\Exceptions\FileIsTooBig;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Spatie\MediaLibrary\Support\File;

class UpdateCartLineAction
{
    public function __construct(
        private readonly CreateMediaFromS3UrlAction $createMediaFromS3UrlAction
    ) {
    }

    public function execute(CartLine $cartLine, UpdateCartLineData $cartLineData): CartLine
    {
        return DB::transaction(function () use ($cartLine, $cartLineData) {
            try {
                DB::beginTransaction();

                if ($cartLineData->quantity) {
                    $cartLine->update([
                        'quantity' => $cartLineData->quantity,
                    ]);
                }

                if ($cartLineData->remarks) {
                    $cartLine->update([
                        'remarks' => $cartLineData->remarks->notes !== null ? [
                            'notes' => $cartLineData->remarks->notes,
                        ] : null,
                    ]);
                    if ($cartLineData->remarks->medias) {
                        $this->createMediaFromS3UrlAction->execute(
                            $cartLine,
                            $cartLineData->remarks->medias,
                            'cart_line_notes'
                        );
                    } else {
                        $cartLine->clearMediaCollection('cart_line_notes');
                    }
                }

                return $cartLine;

                DB::commit();
            } catch (Exception $e) {
                DB::rollBack();
                $maxFileSize = File::getHumanReadableSize(config('media-library.max_file_size'));

                if ($e instanceof FileIsTooBig) {
                    throw new BadRequestException("File is too big , please upload file less than $maxFileSize");
                }
            }
        });
    }
}
