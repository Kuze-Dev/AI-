<?php

declare(strict_types=1);

namespace Domain\Customer\Models;

use App\Settings\SiteSettings;
use Domain\Address\Models\Address;
use Domain\Auth\Contracts\HasEmailVerificationOTP;
use Domain\Auth\EmailVerificationOTP;
use Domain\Auth\Enums\EmailVerificationType;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Notifications\ResetPassword;
use Domain\Customer\Enums\RegisterStatus;
use Domain\Customer\Notifications\VerifyEmail;
use Domain\Customer\Enums\Status;
use Domain\Discount\Models\DiscountLimit;
use Domain\Favorite\Models\Favorite;
use Domain\ServiceOrder\Enums\ServiceOrderStatus;
use Domain\ServiceOrder\Models\ServiceOrder;
use Domain\Shipment\Models\VerifiedAddress;
use Domain\Tier\Enums\TierApprovalStatus;
use Domain\Tier\Models\Tier;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Customer\Models\Customer
 *
 * @property int $id
 * @property int|null $tier_id
 * @property string $cuid customer unique ID
 * @property string $email
 * @property mixed|null $password
 * @property string $first_name
 * @property string $last_name
 * @property string|null $mobile
 * @property Gender|null $gender
 * @property Status|null $status
 * @property RegisterStatus $register_status
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $birth_date
 * @property EmailVerificationType|null $email_verification_type
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Address> $addresses
 * @property-read int|null $addresses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, DiscountLimit> $discountLimits
 * @property-read int|null $discount_limits_count
 * @property-read \Domain\Auth\Model\EmailVerificationOneTimePassword|null $emailVerificationOneTimePassword
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Favorite> $favorites
 * @property-read int|null $favorites_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Tier|null $tier
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read VerifiedAddress|null $verifiedAddress
 * @method static \Illuminate\Database\Eloquent\Builder|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereEmailVerificationType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereGender($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereRegisterStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereTierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer withoutTrashed()
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['addresses'])]
class Customer extends Authenticatable implements HasMedia, MustVerifyEmail, HasEmailVerificationOTP
{
    use SoftDeletes;
    use LogsActivity;
    use InteractsWithMedia;
    use Notifiable;
    use HasApiTokens;
    use ConstraintsRelationships;
    use EmailVerificationOTP;

    protected $fillable = [
        'tier_id',
        'cuid',
        'email',
        'password',
        'first_name',
        'last_name',
        'mobile',
        'gender',
        'status',
        'register_status',
        'birth_date',
        'email_verification_type',
        'tier_approval_status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'birth_date' => 'date',
        'status' => Status::class,
        'gender' => Gender::class,
        'email_verification_type' => EmailVerificationType::class,
        'register_status' => RegisterStatus::class,
        'email_verified_at' => 'datetime',
        'tier_approval_status' => TierApprovalStatus::class,
    ];

    public function getRouteKeyName(): string
    {
        return 'cuid';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->logExcept(['password'])
            ->dontSubmitEmptyLogs();
    }

    /** @return Attribute<string, never> */
    protected function fullName(): Attribute
    {
        return Attribute::get(
            fn ($value): string => "{$this->first_name} {$this->last_name}"
        );
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('image')
            ->singleFile()
            ->useFallbackUrl(app(SiteSettings::class)->getLogoUrl())
            ->registerMediaConversions(fn () => $this->addMediaConversion('original'));
    }

    /** @param \Illuminate\Database\Eloquent\Builder<\Domain\Customer\Models\Customer> $query */
    public function scopeWhereActive(Builder $query): void
    {
        $query->where('status', Status::ACTIVE);
    }

    /** @param \Illuminate\Database\Eloquent\Builder<\Domain\Customer\Models\Customer> $query */
    public function scopeWhereRegistered(Builder $query): void
    {
        $query->where('register_status', RegisterStatus::REGISTERED);
    }

    /** @param \Illuminate\Database\Eloquent\Builder<\Domain\Customer\Models\Customer> $query */
    public function scopeWhereHasActiveSubscriptionBasedServiceOrder($query): void
    {
        $query->whereHas('serviceOrders', function ($nestedQuery) {
            $nestedQuery
                ->where('status', ServiceOrderStatus::ACTIVE)
                ->whereHas('service', function ($deepQuery) {
                    $deepQuery->where('is_subscription', true);
                });
        });
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Tier\Models\Tier, \Domain\Customer\Models\Customer> */
    public function tier(): BelongsTo
    {
        return $this->belongsTo(Tier::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Address\Models\Address> */
    public function addresses(): HasMany
    {
        return $this->hasMany(Address::class);
    }

    public function sendEmailVerificationNotification(): void
    {
        $this->notify(new VerifyEmail());
    }

    public function sendPasswordResetNotification($token): void
    {
        $this->notify(new ResetPassword($token));
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Favorite\Models\Favorite> */
    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Discount\Models\DiscountLimit> */
    public function discountLimits(): HasMany
    {
        return $this->hasMany(DiscountLimit::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasOne<\Domain\Shipment\Models\VerifiedAddress> */
    public function verifiedAddress(): HasOne
    {
        return $this->hasOne(VerifiedAddress::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\ServiceOrder\Models\ServiceOrder>*/
    public function serviceOrders(): HasMany
    {
        return $this->hasMany(ServiceOrder::class);
    }
}
