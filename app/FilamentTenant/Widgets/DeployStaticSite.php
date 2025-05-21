<?php

declare(strict_types=1);

namespace App\FilamentTenant\Widgets;

use App\Settings\CMSSettings;
use Filament\Notifications\Notification;
use Filament\Widgets\Widget;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Http;
use Spatie\Activitylog\ActivityLogger;
use Livewire\Features\SupportRedirects\Redirector;

class DeployStaticSite extends Widget
{
    protected static string $view = 'filament.widgets.deploy-static-site';

    public function getDeployHook(): ?string
    {
        return app(CMSSettings::class)->deploy_hook;
    }

    public function deploy(): RedirectResponse|Redirector|bool
    {

        if (\Domain\Tenant\TenantFeatureSupport::active(\App\Features\CMS\SitesManagement::class)) {

            return redirect()->away(route('filament.tenant.resources.sites.index'));
        }

        if ($this->getDeployHook() === null) {
            Notification::make()
                ->danger()
                ->title(trans('No Deploy Hook Set'))
                ->body(trans('Please set a deploy hook first before trying to deploy.'))
                ->send();

            return false;
        }

        /** @var \Illuminate\Http\Client\Response $response */
        $response = Http::post($this->getDeployHook());

        tap(Notification::make(), function (Notification $notification) use ($response) {
            if ($exception = $response->toException()) {
                report($exception);
                $notification->danger()
                    ->title(trans('Unable to Deploy Static Site'))
                    ->body(trans('There was a problem when trying to request a deployment. Please try again later.'));

                return;
            }

            app(ActivityLogger::class)
                ->useLog('admin')
                ->event('deployed-hook')
                ->withProperties([
                    'custom' => [
                        'deploy_hook' => $this->getDeployHook(),
                    ],
                ])
                ->log('Deployed hook');

            $notification->success()
                ->title(trans('Deployment Request Sent'));
        })->send();

        return true;
    }
}
