<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\DiscountResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\DiscountResource;
use DB;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\Actions\Action;

class CreateDiscount extends CreateRecord
{
    use LogsFormActivity;

    protected static string $resource = DiscountResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('create')
                ->label(__('filament::resources/pages/create-record.form.actions.create.label'))
                ->action('create')
                ->keyBindings(['mod+s']),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    // protected function handleRecordCreation(array $data): Model
    // {
    //     return DB::transaction(
    //         fn () => app(CreateContentAction::class)
    //             ->execute(new ContentData(
    //                 name: $data['name'],
    //                 taxonomies: $data['taxonomies'],
    //                 blueprint_id: $data['blueprint_id'],
    //                 is_sortable: $data['is_sortable'],
    //                 past_publish_date_behavior: PublishBehavior::tryFrom($data['past_publish_date_behavior'] ?? ''),
    //                 future_publish_date_behavior: PublishBehavior::tryFrom($data['future_publish_date_behavior'] ?? ''),
    //                 prefix: $data['prefix'],
    //             ))
    //     );
    // }
}
