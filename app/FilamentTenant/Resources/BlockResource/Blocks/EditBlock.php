<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\BlockResource\Blocks;

use App\Filament\Pages\Concerns\LogsFormActivity;
use App\FilamentTenant\Resources\BlockResource;
use Domain\Page\Actions\UpdateBlockAction;
use Domain\Page\DataTransferObjects\BlockData;
use Exception;
use Filament\Pages\Actions;
use Filament\Pages\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class EditBlock extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = BlockResource::class;

    /** @throws Exception */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament::resources/pages/edit-record.form.actions.save.label'))
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
     * @param  \Domain\Page\Models\Block  $record
     *
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(
            fn () => app(UpdateBlockAction::class)
                ->execute(
                    $record,
                    new BlockData(
                        name: $data['name'],
                        component: $data['component'],
                        image: $data['image'],
                        blueprint_id: $data['blueprint_id'],
                        is_fixed_content: $data['is_fixed_content'],
                        data: $data['data'] ?? null,
                    )
                )
        );
    }
}
