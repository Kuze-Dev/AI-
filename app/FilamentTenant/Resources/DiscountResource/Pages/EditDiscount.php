<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\DiscountResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\DiscountResource;
use DB;
use Domain\Discount\Actions\ForceDeleteDiscountAction;
use Domain\Discount\Actions\UpdateDiscountAction;
use Domain\Discount\DataTransferObjects\DiscountData;
use Domain\Discount\Models\Discount;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException as ExceptionsDeleteRestrictedException;

class EditDiscount extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = DiscountResource::class;

    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
            Actions\RestoreAction::make(),
            Actions\ForceDeleteAction::make()
                ->using(function (Discount $record) {
                    try {
                        return app(ForceDeleteDiscountAction::class)->execute($record);
                    } catch (ExceptionsDeleteRestrictedException) {
                        return false;
                    }
                }),

        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    /** @param  \Domain\Discount\Models\Discount  $record */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app(UpdateDiscountAction::class)
                ->execute($record, DiscountData::fromArray($data))
        );
    }
}
