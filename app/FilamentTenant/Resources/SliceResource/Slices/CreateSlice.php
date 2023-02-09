<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\SliceResource\Slices;

use App\FilamentTenant\Resources\SliceResource;
use Domain\Page\Actions\CreateSliceAction;
use Domain\Page\DataTransferObjects\SliceData;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;

class CreateSlice extends CreateRecord
{
    protected static string $resource = SliceResource::class;

    /** @throws Throwable */
    protected function handleRecordCreation(array $data): Model
    {
        return DB::transaction(
            fn () => app(CreateSliceAction::class)
                ->execute(new SliceData(
                    name: $data['name'],
                    component: $data['component'],
                    blueprint_id: $data['blueprint_id'],
                    is_fixed_content: $data['is_fixed_content'],
                    data: $data['data'],
                ))
        );
    }
}
