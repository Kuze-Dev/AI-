<?php

declare(strict_types=1);

namespace Domain\Form\Models;

use Domain\Blueprint\Models\Blueprint;
use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * Domain\Form\Models\Form
 *
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Activity> $activities
 * @property-read int|null $activities_count
 * @property-read Blueprint|null $blueprint
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Form\Models\FormEmailNotification> $formEmailNotifications
 * @property-read int|null $form_email_notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Domain\Form\Models\FormSubmission> $formSubmissions
 * @property-read int|null $form_submissions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Form newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Form newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Form query()
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['formEmailNotifications', 'formSubmissions'])]
class Form extends Model
{
    use HasSlug;
    use LogsActivity;
    use ConstraintsRelationships;

    protected $fillable = [
        'blueprint_id',
        'name',
        'slug',
        'store_submission',
        'uses_captcha',
    ];

    protected $casts = [
        'store_submission' => 'bool',
        'uses_captcha' => 'bool',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Blueprint\Models\Blueprint, \Domain\Form\Models\Form> */
    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Form\Models\FormEmailNotification> */
    public function formEmailNotifications(): HasMany
    {
        return $this->hasMany(FormEmailNotification::class);
    }

    /** @return \Illuminate\Database\Eloquent\Relations\HasMany<\Domain\Form\Models\FormSubmission> */
    public function formSubmissions(): HasMany
    {
        return $this->hasMany(FormSubmission::class);
    }

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
