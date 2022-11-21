<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\PageResource\Pages;

use App\FilamentTenant\Resources\PageResource;
use Domain\Page\Actions\UpdatePageAction;
use Domain\Page\DataTransferObjects\PageData;
use Domain\Page\Enums\PageBehavior;
use Domain\Page\Models\Page;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class ConfigurePage extends EditRecord
{
    protected static string $resource = PageResource::class;

    protected function getActions(): array
    {
        return [
            Actions\EditAction::make()
                ->url(route('filament-tenant.resources.' . self::$resource::getSlug() . '.edit', $this->record)),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getTitle(): string
    {
        return trans('Configure :label', [
            'label' => $this->getRecordTitle(),
        ]);
    }

    /**
     * @param Page $record
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app(UpdatePageAction::class)
                ->execute($record, new PageData(
                    name: $data['name'],
                    blueprint_id: (int) $data['blueprint_id'],
                    past_behavior: PageBehavior::tryFrom($data['past_behavior'] ?? ''),
                    future_behavior: PageBehavior::tryFrom($data['future_behavior'] ?? ''),
                ))
        );
    }
}
