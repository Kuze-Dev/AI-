<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\DiscountResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\DiscountResource;
use DB;
use Domain\Discount\Actions\CreateDiscountAction;
use Domain\Discount\DataTransferObjects\DiscountCodeData;
use Domain\Discount\DataTransferObjects\DiscountConditionData;
use Domain\Discount\DataTransferObjects\DiscountData;
use Domain\Discount\Enums\DiscountStatus;
use Domain\Discount\Enums\DiscountType;
use Filament\Resources\Pages\CreateRecord;
use Filament\Pages\Actions\Action;
use Illuminate\Database\Eloquent\Model;

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

    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(
            fn () => app(CreateDiscountAction::class)
                ->execute(new DiscountData(
                    name: $data['name'],
                    slug: $data['slug'],
                    description: $data['description'],
                    type: DiscountType::tryFrom($data['type']),
                    status: DiscountStatus::tryFrom($data['status']),
                    amount: $data['amount'],
                    max_uses: $data['max_uses'],
                    valid_start_at: $data['valid_start_at'],
                    valid_end_at: $data['valid_end_at'],
                ), new DiscountConditionData(
                    discount_condition_type: $data['discount_condition_type'],
                    discount_id: $data['discount_id']
                ) , new DiscountCodeData(
                    code: $data['code'],
                    discount_id: $data['discount_id']
                ))
        );
    }

}
