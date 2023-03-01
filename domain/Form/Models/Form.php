<?php

declare(strict_types=1);

namespace Domain\Form\Models;

use Domain\Site\Models\Site;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Activitylog\LogOptions;
use Domain\Blueprint\Models\Blueprint;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Activity;
use Spatie\Activitylog\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Domain\Support\ConstraintsRelationships\ConstraintsRelationships;
use Domain\Support\ConstraintsRelationships\Attributes\OnDeleteCascade;
use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;

/**
 * Domain\Form\Models\Form
 *
 * @property int $id
 * @property string $blueprint_id
 * @property string $name
 * @property string $slug
 * @property bool $store_submission
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read Blueprint $blueprint
 * @property-read \Illuminate\Database\Eloquent\Collection|\Domain\Form\Models\FormEmailNotification[] $formEmailNotifications
 * @property-read int|null $form_email_notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Domain\Form\Models\FormSubmission[] $formSubmissions
 * @property-read int|null $form_submissions_count
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
 * @mixin \Eloquent
 */
#[OnDeleteCascade(['formEmailNotifications', 'formSubmissions'])]
class Form extends Model implements IsActivitySubject
{
    use HasSlug;
    use LogsActivity;
    use ConstraintsRelationships;

    protected $fillable = [
        'blueprint_id',
        'name',
        'slug',
        'store_submission',
    ];

    protected $casts = [
        'store_submission' => 'bool',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function getActivitySubjectDescription(Activity $activity): string
    {
        return 'Form: '.$this->name;
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

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(Site::class);
    }
}
