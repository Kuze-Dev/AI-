<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\BlockResource\Blocks;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\BlockResource;
use Domain\Page\Actions\CreateBlockAction;
use Domain\Page\DataTransferObjects\BlockData;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateBlock extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = BlockResource::class;

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

    // /** @throws Throwable */
    // protected function handleRecordCreation(array $data): Model
    // {
    //     return DB::transaction(
    //         fn () => app(CreateBlockAction::class)
    //             ->execute(new BlockData(
    //                 name: $data['name'],
    //                 component: $data['component'],
    //                 image: $data['image'],
    //                 blueprint_id: $data['blueprint_id'],
    //                 is_fixed_content: $data['is_fixed_content'],
    //                 data: $data['data'] ?? null,
    //             ))
    //     );
    // }
}
