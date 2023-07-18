<?php

declare(strict_types=1);

namespace Domain\Customer\Models;

use Domain\Address\Models\Address;
use Domain\Customer\Enums\Gender;
use Domain\Customer\Notifications\VerifyEmail;
use Domain\Customer\Enums\Status;
use Domain\Shipment\Models\CustomerVerifiedAddress;
use Domain\Tier\Models\Tier;
use Illuminate\Contracts\Auth\MustVerifyEmail;
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
 * @property \Domain\Customer\Enums\Gender $gender
 * @property Status $status
 * @property \Illuminate\Support\Carbon $birth_date
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Address> $addresses
 * @property-read int|null $addresses_count
 * @property-read \Spatie\MediaLibrary\MediaCollections\Models\Collections\MediaCollection<int, \Spatie\MediaLibrary\MediaCollections\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Tier $tier
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read CustomerVerifiedAddress $verifiedAddress
 * @method static \Illuminate\Database\Eloquent\Builder|Customer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer query()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereBirthDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereCuid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereMobile($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereTierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Customer withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Customer withoutTrashed()
 * @mixin \Eloquent
 */
class Customer extends Authenticatable implements HasMedia, MustVerifyEmail
{
    use SoftDeletes;
    use LogsActivity;
    use InteractsWithMedia;
    use Notifiable;
    use HasApiTokens;

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
        'birth_date',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'password' => 'hashed',
        'birth_date' => 'date',
        'status' => Status::class,
        'gender' => Gender::class,
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
            ->singleFile();
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

    /** @return \Illuminate\Database\Eloquent\Relations\HasOne<\Domain\Shipment\Models\CustomerVerifiedAddress> */
    public function verifiedAddress(): HasOne
    {
        return $this->hasOne(CustomerVerifiedAddress::class);
    }
}
