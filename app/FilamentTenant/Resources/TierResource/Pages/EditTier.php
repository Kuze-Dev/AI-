<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\TierResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\TierResource;
use Domain\Customer\Actions\DeleteTierAction;
use Domain\Customer\Actions\EditTierAction;
use Domain\Customer\Actions\ForceDeleteTierAction;
use Domain\Customer\Actions\RestoreTierAction;
use Domain\Customer\DataTransferObjects\TierData;
use Domain\Customer\Models\Tier;
use Domain\Support\ConstraintsRelationships\Exceptions\DeleteRestrictedException;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class EditTier extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = TierResource::class;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make()
                ->using(function (Tier $record) {
                    try {
                        return app(DeleteTierAction::class)->execute($record);
                    } catch (DeleteRestrictedException $e) {
                        return false;
                    }
                }),
            Actions\ForceDeleteAction::make()
                ->using(function (Tier $record) {
                    try {
                        return app(ForceDeleteTierAction::class)->execute($record);
                    } catch (DeleteRestrictedException $e) {
                        return false;
                    }
                }),
            Actions\RestoreAction::make()
                ->using(
                    fn (Tier $record) => app(RestoreTierAction::class)
                        ->execute($record)
                ),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    /**
     * @param \Domain\Customer\Models\Tier$record
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app(EditTierAction::class)
                ->execute($record, new TierData(...$data))
        );
    }
}
