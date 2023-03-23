<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\SliceResource\Slices;

use App\FilamentTenant\Resources\SliceResource;
use Domain\Page\Actions\UpdateSliceAction;
use Domain\Page\DataTransferObjects\SliceData;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;
use Exception;
use Filament\Pages\Actions\Action;

class EditSlice extends EditRecord
{
    protected static string $resource = SliceResource::class;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [
            Action::make('save')
                ->label(__('filament::resources/pages/edit-record.form.actions.save.label'))
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
        ];
    }

    protected function getFormActions(): array
    {
        return $this->getCachedActions();
    }

    /**
     * @param \Domain\Page\Models\Slice $record
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app(UpdateSliceAction::class)
                ->execute(
                    $record,
                    new SliceData(
                        name: $data['name'],
                        component: $data['component'],
                        blueprint_id: $data['blueprint_id'],
                        is_fixed_content: $data['is_fixed_content'],
                        data: $data['data'] ?? null,
                    )
                )
        );
    }
}
