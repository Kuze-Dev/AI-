<?php

declare(strict_types=1);

namespace Domain\Form\Imports;

use Domain\Form\Actions\CreateFormAction;
use Domain\Form\DataTransferObjects\FormData;
use Domain\Form\Models\Form;
use Domain\Site\Models\Site;
use Filament\Actions\Imports\ImportColumn;
use Filament\Actions\Imports\Importer;
use Filament\Actions\Imports\Models\Import;
use Illuminate\Support\Str;

/**
 * @property-read Form $record
 */
class FormImporter extends Importer
{
    protected static ?string $model = Form::class;

    #[\Override]
    public static function getColumns(): array
    {
        return [

            ImportColumn::make('name')
                ->requiredMapping()
                ->rules([
                    function (string $attribute, mixed $value, \Closure $fail, \Illuminate\Validation\Validator $validator) {

                        $taxo = Form::where('name', $value)->count();

                        if (! (
                            \Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class))
                        ) {

                            if ($taxo > 0) {
                                $fail("Form name {$value} has already been taken.");
                            }
                        }

                    },
                ],
                ),

            ImportColumn::make('slug')
                ->requiredMapping(),

            ImportColumn::make('locale')
                ->requiredMapping(),

            ImportColumn::make('blueprint_id')
                ->requiredMapping(),

            ImportColumn::make('store_submission')
                ->requiredMapping(),

            ImportColumn::make('uses_captcha')
                ->requiredMapping(),

            ImportColumn::make('sites')
                ->requiredMapping(),

            ImportColumn::make('formEmailNotifications')
                ->requiredMapping(),

        ];
    }

    #[\Override]
    public function resolveRecord(): Form
    {

        if (is_null($this->data['slug'])) {
            return new Form;
        }

        return Form::where('slug', $this->data['slug'])->first() ?? new Form;
    }

    #[\Override]
    public function fillRecord(): void
    {
        /** Disabled Filament Built in Record Creation Handle the Forms
         * Creation thru Domain Level Action
         */
    }

    /**
     * @throws \Throwable
     */
    #[\Override]
    public function saveRecord(): void
    {

        if ($this->record->exists) {
            return;
        }

        /** @var array $siteIDs */
        $siteIDs = (array_key_exists('sites', $this->data) && ! is_null($this->data['sites'])) ?
            Site::whereIn('domain', explode(',', $this->data['sites']))->pluck('id')->toArray() :
            [];
        $form_email_notifications = ! empty($this->data['formEmailNotifications'])
        ? json_decode($this->data['formEmailNotifications'], true)
        : [];

        $form_email_notifications = array_map(fn (array $notification) => array_diff_key($notification, ['id' => '']),
            $form_email_notifications
        );

        $formData = FormData::fromArray([
            'name' => $this->data['name'],
            'blueprint_id' => $this->data['blueprint_id'],
            'locale' => $this->data['locale'],
            'store_submission' => (bool) $this->data['store_submission'],
            'uses_captcha' => (bool) $this->data['uses_captcha'],
            'form_email_notifications' => $form_email_notifications,
            'sites' => $siteIDs,
        ]);

        app(CreateFormAction::class)->execute($formData);

    }

    #[\Override]
    public static function getCompletedNotificationBody(Import $import): string
    {
        $body = 'Your Taxonmy import has completed and '.
            number_format($import->successful_rows).' '.
            Str::of('row')->plural($import->successful_rows).' imported.';

        if ($failedRowsCount = $import->getFailedRowsCount()) {
            $body .= ' '.number_format($failedRowsCount).' '.Str::of('row')
                ->plural($failedRowsCount).' failed to import.';
        }

        return $body;
    }
}
