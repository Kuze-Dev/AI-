<?php

declare(strict_types=1);

namespace Domain\Form\Models;

use AlexJustesen\FilamentSpatieLaravelActivitylog\Contracts\IsActivitySubject;
use Domain\Blueprint\Models\Blueprint;
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
 * @property int $id
 * @property int $blueprint_id
 * @property string $name
 * @property string $slug
 * @property bool $store_submission
 *
 * @property-read \Illuminate\Database\Eloquent\Collection|Activity[] $activities
 * @property-read int|null $activities_count
 * @property-read Blueprint|null $blueprint
 * @property-read \Illuminate\Database\Eloquent\Collection|\Domain\Form\Models\FormEmailNotification[] $formEmailNotifications
 * @property-read int|null $form_email_notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\Domain\Form\Models\FormSubmission[] $formSubmissions
 * @property-read int|null $form_submissions_count
 * @method static \Illuminate\Database\Eloquent\Builder|Form newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Form newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Form query()
 * @mixin \Eloquent
 */
class Form extends Model implements IsActivitySubject
{
    use HasSlug;
    use LogsActivity;

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

    public function blueprint(): BelongsTo
    {
        return $this->belongsTo(Blueprint::class);
    }

    public function formEmailNotifications(): HasMany
    {
        return $this->hasMany(FormEmailNotification::class);
    }

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
