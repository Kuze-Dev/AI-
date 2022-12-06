<?php

declare(strict_types=1);

namespace App\FilamentTenant\Resources\FormResource\Pages;

use App\FilamentTenant\Resources\FormResource;
use Domain\Form\Actions\UpdateFormAction;
use Domain\Form\DataTransferObjects\FormData;
use Filament\Pages\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Throwable;
use Exception;

class EditForm extends EditRecord
{
    protected static string $resource = FormResource::class;
    private array $rawValidatedData;

    /** @throws Exception */
    protected function getActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    /**
     * @param \Domain\Form\Models\Form $record
     * @throws Throwable
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return DB::transaction(fn () => app(UpdateFormAction::class)
            ->execute(
                $record,
                FormData::fromArray($this->rawValidatedData)
            ));
    }

    public function beforeValidate(): void
    {
        $this->rawValidatedData = $this->form->validate()[$this->form->getStatePath()];
    }

    public function afterSave(): void
    {
        $this->fillForm();
    }
}
