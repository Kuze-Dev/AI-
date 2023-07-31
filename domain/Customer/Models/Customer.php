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
use Domain\Tier\Models\Tier;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
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
 * @property int $tier_id
 * @property string $cuid customer unique ID
 * @property string $email
 * @property mixed|null $password
 * @property-read  string $full_name
 * @property string $first_name
 * @property string $last_name
 * @property string $mobile
 * @property Gender $gender
 * @property Status $status
 * @property RegisterStatus $register_status
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon $birth_date
 * @property EmailVerificationType $email_verification_type
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Address> $addresses
 * @property-read int|null $addresses_count
 * @property-read \Domain\Auth\Model\EmailVerificationOneTimePassword|null $emailVerificationOneTimePassword
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Tier|null $tier
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
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
}
