<?php

declare(strict_types=1);

namespace Domain\Blueprint\Jobs;

use Domain\Blueprint\Models\BlueprintData;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateBlueprintDataMediaConversionJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        protected BlueprintData $blueprintData,
        protected array $updatedMediaConversions = [],
    ) {}

    public function handle(): void
    {
        $blueprintData = $this->blueprintData;

        $blueprintData->update([
            'blueprint_media_conversion' => $this->updatedMediaConversions,
        ]);

        $blueprintData->refresh();

        $mediaItems = $blueprintData->getMedia('blueprint_media');

        // regenerate conversions for media items
        foreach ($mediaItems as $mediaItem) {
            app(\Support\Media\Actions\RegenerateImageConversions::class)->execute($mediaItem);
        }
    }
}
