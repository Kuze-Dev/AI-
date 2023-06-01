<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PageResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\PageResource;
use Domain\Page\Actions\CreatePageAction;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Models\Page;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;
use Throwable;

class CreatePage extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = PageResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('create')
                ->label(__('filament::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function afterFill()
    {
        $pageSlug = Request::input('pageSlug');
        $clone = Request::input('clone');

        if ($clone) {
            $page = Page::whereSlug($pageSlug)->with(['metaData', 'blockContents'])
                ->firstOrFail();

            $this->data['visibility'] = $page->visibility;
            $this->data['published_at'] = $page->published_at ? true : false;

            $this->data['block_contents'] = $page->blockContents->toArray() ?? [];

            $metaData = $page->metaData->toArray();
            $this->data['meta_data'] = [
                'author' => $metaData['author'],
                'description' => $metaData['description'],
                'keywords' => $metaData['keywords'],
            ];

            if ( ! empty($metaData['media'])) {
                $imageUuid = $metaData['media'][0]['uuid'];
                $imageAltText = $metaData['media'][0]['custom_properties']['alt_text'];

                $this->data['meta_data']['image'] = [
                    $imageUuid => $imageUuid,
                ];
                $this->data['meta_data']['image_alt_text'] = $imageAltText;
            }
        }
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    /** @throws Throwable */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(fn () => app(CreatePageAction::class)->execute(PageData::fromArray($data)));
    }
}
