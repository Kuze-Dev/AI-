<?php

declare(strict_types=1);

namespace Domain\Auth\Model;

use BaconQrCode\Renderer\Color\Rgb;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\Fill;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Domain\Auth\Contracts\TwoFactorAuthenticatable;
use Domain\Auth\Contracts\TwoFactorAuthenticationProvider;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Eloquent;

/**
 * Domain\Auth\Model\TwoFactorAuthentication
 *
 * @property int $id
 * @property string $authenticatable_type
 * @property int $authenticatable_id
 * @property string|null $enabled_at
 * @property mixed|null $secret
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Model|Eloquent $authenticatable
 * @property-read \Illuminate\Database\Eloquent\Collection|\Domain\Auth\Model\RecoveryCode[] $recoveryCodes
 * @property-read int|null $recovery_codes_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Domain\Auth\Model\SafeDevice[] $safeDevices
 * @property-read int|null $safe_devices_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication query()
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication whereAuthenticatableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication whereAuthenticatableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication whereEnabledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication whereSecret($value)
 * @method static \Illuminate\Database\Eloquent\Builder|TwoFactorAuthentication whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class TwoFactorAuthentication extends Model
{
    protected $casts = [
        'secret' => 'encrypted',
    ];

    /** @return HasMany<RecoveryCode> */
    public function recoveryCodes(): HasMany
    {
        return $this->hasMany(RecoveryCode::class);
    }

    /** @return HasMany<SafeDevice> */
    public function safeDevices(): HasMany
    {
        return $this->hasMany(SafeDevice::class);
    }

    /** @return MorphTo<Model&TwoFactorAuthenticatable, self> */
    public function authenticatable(): MorphTo
    {
        /** @var MorphTo<Model&TwoFactorAuthenticatable, self> */
        return $this->morphTo();
    }

    public function qrCodeSvg(): string
    {
        $svg = (new Writer(
            new ImageRenderer(
                new RendererStyle(192, 0, null, null, Fill::uniformColor(new Rgb(255, 255, 255), new Rgb(45, 55, 72))),
                new SvgImageBackEnd()
            )
        ))->writeString($this->qrCodeUrl());

        return trim(substr($svg, strpos($svg, "\n") + 1));
    }

    public function qrCodeUrl(): string
    {
        return app(TwoFactorAuthenticationProvider::class)->qrCodeUrl(
            config('app.name'),
            $this->authenticatable->twoFactorHolder(), // @phpstan-ignore-line
            $this->secret
        );
    }
}
