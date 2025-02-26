<?php

declare(strict_types=1);

namespace App\Support\ActivityLog;

use Filament\Support\Contracts\HasLabel;
use Illuminate\Support\Str;

/**
 * @todo for roy list all cases
 */
enum ActivityLogEvent: string implements HasLabel
{
    case sync_manual_starting = 'manual:starting';
    case sync_terminal_starting = 'terminal:starting';
    case sync_scheduler_starting = 'scheduler:starting';

    case sync_manual_finished = 'manual:finished';
    case sync_terminal_finished = 'terminal:finished';
    case sync_scheduler_finished = 'scheduler:finished';

    case sync_manual_failed = 'manual:failed';
    case sync_terminal_failed = 'terminal:failed';
    case sync_scheduler_failed = 'scheduler:failed';

    case auth_customer_login = 'customer:login';
    case auth_admin_login = 'admin:login';
    case auth_admin_logout = 'admin:logout';

    case settings_updated = 'settings updated';

    case deleting_attempt = 'deleting-attempt';

    case api_submit_order = 'api-submit-order';
    case api_single_sync_found = 'api-single-sync-found';
    case api_received_user = 'api-received-user';
    case api_received_user_duplicate_email = 'api-received-user-duplicate-email';

    case created = 'created';
    case updated = 'updated';
    case deleted = 'deleted';

    case impersonated = 'impersonated';

    case some_random_event = 'some-random-event'; //testing

    case email_link_clicked_forgot_password = 'forgot-password';
    case email_link_clicked_setup_password = 'setup-password';
    case email_link_clicked_setup_password_already_confirmed = 'setup-password-already-confirmed';


    public function getLabel(): ?string
    {
        return Str::headline($this->value);
    }

    public static function isApiPayload(?string $enum): bool
    {
        if ($enum === null) {
            return false;
        }

        try {
            /** @var self $self */
            $self = self::tryFrom($enum);
        } catch (\Exception) {
            return false;
        }

        return $self->apiPayload();
    }

    public function apiPayload(): bool
    {
        return match ($this) {
            self::api_submit_order, self::api_single_sync_found => true,
            default => false,
        };
    }
}
