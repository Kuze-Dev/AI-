<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\BlockResource\Blocks;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\BlockResource;
use Domain\Page\Actions\CreateBlockAction;
use Domain\Page\DataTransferObjects\BlockData;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateBlock extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = BlockResource::class;

    #[\Override]
    protected function getHeaderActions(): array
    {
        return [
            Action::make('create')
                ->label(trans('filament-panels::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
            $this->getCreateAnotherFormAction(),
        ];
    }

    #[\Override]
    protected function handleRecordCreation(array $data): Model
    {
        return app(CreateBlockAction::class)
            ->execute(new BlockData(
                name: $data['name'],
                component: $data['component'],
                image: $data['image'] ?? null,
                blueprint_id: $data['blueprint_id'],
                is_fixed_content: $data['is_fixed_content'],
                data: $data['data'] ?? null,
                sites: $data['sites'] ?? [],
            )
            );
    }
}
