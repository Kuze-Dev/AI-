<?php

declare(strict_types=1);

namespace Support\Common\Actions;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use InvalidArgumentException;
use League\Flysystem\UnableToCheckExistence;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Support\Common\DataTransferObjects\MediaCollectionData;
use Support\Common\DataTransferObjects\MediaData;

class SyncMediaCollectionAction
{
    /**
     * @return MediaCollection<int, Media>|null
     */
    public function execute(Model&HasMedia $model, MediaCollectionData $mediaCollectionData): ?MediaCollection
    {

        $media = collect($mediaCollectionData->media)
            ->map(function (MediaData $mediaData) use ($model, $mediaCollectionData) {
                if ($mediaData->media instanceof UploadedFile) {
                    return $this->addMedia($model, $mediaCollectionData->collection, $mediaData->media, $mediaData->custom_properties);
                }

                if (Str::isUrl($mediaData->media)) {
                    return $this->addMediaFromUrl($model, $mediaCollectionData->collection, $mediaData->media);
                }

                if (pathinfo($mediaData->media, PATHINFO_EXTENSION)) {

                    $storage = Storage::disk(config('filament.default_filesystem_disk'));

                    /**
                     * @var Media|null $new_uploaded_media
                     *
                     *  @phpstan-ignore-next-line */
                    $new_uploaded_media = $model->media->where('file_name', $mediaData->media)->first();

                    if ($new_uploaded_media) {
                        return $new_uploaded_media;
                    }

                    if (! $storage->exists($mediaData->media)) {
                        throw new UnableToCheckExistence;
                    }

                    /**
                     * Ignore unknown addMediaFromDisk error for \Spatie\MediaLibrary\HasMedia interface
                     *
                     * @phpstan-ignore method.notFound
                     * */
                    return $model->addMediaFromDisk($mediaData->media, config('filament.default_filesystem_disk'))
                        ->withCustomProperties($mediaData->custom_properties)
                        ->toMediaCollection($mediaCollectionData->collection);
                }

                if (! Str::isUuid($mediaData->media)) {
                    throw new InvalidArgumentException;
                }

                if ($media = $model->getMedia($mediaCollectionData->collection)->firstWhere('uuid', $mediaData->media)) {
                    return $this->updateMedia($media, $mediaData->custom_properties);
                }

                return $this->copyMedia($model, Media::whereUuid($mediaData->media)->firstOrFail(), $mediaCollectionData->collection, $mediaData->custom_properties);
            })
            ->pipe(fn (Collection $items) => MediaCollection::make($items));
        /**
         * Causing Vapor to Lost Image Conversions need more research why it is happening..
         */
        // $model->clearMediaCollectionExcept($mediaCollectionData->collection, $media);

        // $medias_uuid = $media->pluck('uuid')->toArray();
        $excludedMedia = $media;
        /**
         * handle deletion of media manualy.
         */
        $model->getMedia($mediaCollectionData->collection)
            ->reject(fn (Media $media) => $excludedMedia->where($media->getKeyName(), $media->getKey())->count())
            // ->whereNotIn('uuid', $medias_uuid)
            ->each(fn ($media_item) => $media_item->delete());

        return $media;
    }

    protected function addMedia(Model&HasMedia $model, string $collection, UploadedFile $media, array $customProperties = []): Media
    {
        if (! $imageString = $media->get()) {
            throw new InvalidArgumentException;
        }

        if (! method_exists($model, 'addMediaFromString')) {
            throw new InvalidArgumentException;
        }

        return $model->addMediaFromString($imageString)
            ->usingFileName($media->getClientOriginalName())
            ->usingName(pathinfo($media->getClientOriginalName(), PATHINFO_FILENAME))
            ->withCustomProperties($customProperties)
            ->toMediaCollection($collection);
    }

    protected function addMediaFromUrl(Model&HasMedia $model, string $collection, string $media): Media
    {
        if (! method_exists($model, 'addMediaFromUrl')) {
            throw new InvalidArgumentException;
        }

        return $model
            ->addMediaFromUrl($media)
            ->toMediaCollection($collection);
    }

    public function updateMedia(Media $media, array $customProperties = []): Media
    {
        $media->custom_properties = $customProperties;

        $media->save();

        return $media;
    }

    protected function copyMedia(Model&HasMedia $model, Media $media, string $collection, array $customProperties = []): Media
    {
        $media->custom_properties = $customProperties;

        return $media->copy($model, $collection);
    }
}
