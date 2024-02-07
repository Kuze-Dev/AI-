<?php

declare(strict_types=1);

namespace App\Filament\Resources\TenantResource\Pages;

use App\Features;
use App\Filament\Pages\Concerns\LogsFormActivity;
use App\Filament\Resources\TenantResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Support\Str;

/**
 * @property-read \Domain\Tenant\Models\Tenant $record
 */
class EditTenant extends EditRecord
{
    use LogsFormActivity;

    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('filament-panels::resources/pages/edit-record.form.actions.save.label'))
                ->requiresConfirmation(fn (Action $livewire) => $livewire->data['is_suspended'] === true)
                ->modalCancelAction(fn (Action $livewire) => Action::makeModalAction('redirect')
                    ->label(trans('Cancel & Revert Changes'))
                    ->color('gray')
                    ->url(TenantResource::getUrl('edit', [$this->record])))
                ->modalHeading(fn (Action $livewire) => $livewire->data['is_suspended'] ? 'Warning' : null)
                ->modalDescription(fn (Action $livewire) => $livewire->data['is_suspended'] ? 'The suspend option is enabled. Please proceed with caution as this action will suspend the tenant. Would you like to proceed ?' : null)
                ->action('save')
                ->keyBindings(['mod+s']),
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    //    public function beforeSave(): void
    //    {
    //    }

    public function afterSave(): void
    {
        $data = $this->form->getRawState();

        $tenant = $this->record;

        $features = self::getNormalizedFeatureNames($data['features']);

        $activeFeatures = array_keys(array_filter($tenant->features()->all()));
        $inactiveFeatures = array_diff($activeFeatures, $features);

        foreach ($inactiveFeatures as $inactiveFeature) {
            $tenant->features()->deactivate($inactiveFeature);
        }

        foreach ($features as $feature) {
            $tenant->features()->activate($feature);
        }
    }

    private static function getNormalizedFeatureNames(array $features): array
    {
        $bases = [
            Features\CMS\CMSBase::class,
            Features\Customer\CustomerBase::class,
            Features\ECommerce\ECommerceBase::class,
            Features\Service\ServiceBase::class,
            Features\Shopconfiguration\ShopconfigurationBase::class,
        ];

        $bases = collect($bases)
            ->mapWithKeys(fn (string $base) => [class_basename($base) => $base])
            ->toArray();

        $results = [];
        foreach ($features as $k => $extra) {
            if (is_bool($extra)) {
                if ($extra) {
                    $results[] = $bases[$k];
                }

                continue;
            }

            if (
                $features[
                (string) Str::of('CMSBase_extras')
                    ->before('_extra')
                ] === false
            ) {
                continue;
            }

            foreach ($extra as $v) {
                $results[] = $v;
            }
        }

        foreach ($results as $k => $r) {
            $results[$k] = app($r)->name;
        }

        return $results;
    }
}
