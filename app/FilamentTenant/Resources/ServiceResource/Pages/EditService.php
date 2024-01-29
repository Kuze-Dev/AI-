<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\ServiceResource\Pages;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\ServiceResource;
use Domain\Service\Actions\DeleteServiceAction;
use Domain\Service\Actions\UpdateServiceAction;
use Domain\Service\DataTransferObjects\ServiceData;
use Domain\Service\Models\Service;
use Exception;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class EditService extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = ServiceResource::class;

    /**
     * @param  Service  $record
     *
     * @throws Throwable
     */
    public function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateServiceAction::class)
            ->execute($record, ServiceData::fromArray($data)));
    }

    /** @throws Exception */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\RestoreAction::make(),
            Actions\DeleteAction::make()
                ->using(fn (Service $record) => DB::transaction(
                    fn () => app(DeleteServiceAction::class)
                        ->execute($record)
                )),
        ];
    }
}
