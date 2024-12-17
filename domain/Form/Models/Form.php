<?php

declare(strict_types=1);

namespace Domain\Form\Models;

use Domain\Blueprint\Models\Blueprint;
use Domain\Site\Traits\Sites;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Support\ConstraintsRelationships\ConstraintsRelationships;

/**
 * Domain\Form\Models\Form
 *
 * @property int $id
 * @property string $blueprint_id
 * @property string $name
 * @property string $slug
 * @property bool $store_submission
 * @property bool $uses_captcha
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Blueprint $blueprint
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Form\Models\FormEmailNotification> $formEmailNotifications
 * @property-read int|null $form_email_notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Form\Models\FormSubmission> $formSubmissions
 * @property-read int|null $form_submissions_count
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Form newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Form newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Form query()
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereBlueprintId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereStoreSubmission($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereUsesCaptcha($value)
 *
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['formEmailNotifications', 'formSubmissions'])]
class Form extends Model
{
    use ConstraintsRelationships;
    use HasSlug;
    use LogsActivity;
    use Sites;

    protected $fillable = [
        'blueprint_id',
        'name',
        'slug',
        'store_submission',
        'uses_captcha',
    ];

    protected function casts(): array
    {
        return [
            'store_submission' => 'bool',
            'uses_captcha' => 'bool',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Blueprint\Models\Blueprint, $this> */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Form\Models\FormEmailNotification, $this> */
    public function formEmailNotifications(): HasMany
    {
        return $this->hasMany(FormEmailNotification::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Form\Models\FormSubmission, $this> */
    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

    #[\Override]
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->preventOverwrite()
            ->doNotGenerateSlugsOnUpdate()
            ->saveSlugsTo($this->getRouteKeyName());
    }
}
