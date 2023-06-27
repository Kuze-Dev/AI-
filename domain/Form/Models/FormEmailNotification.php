<?php

declare(strict_types=1);

namespace Domain\Form\Models;

use Domain\Form\Models\Casts\DelimitedCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Domain\Form\Models\FormEmailNotification
 *
 * @property array|null $to
 * @property array|null $cc
 * @property array|null $bcc
 * @property array|null $reply_to
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Domain\Form\Models\Form|null $form
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification query()
 * @mixin \Eloquent
 */
class FormEmailNotification extends Model
{
    use LogsActivity;

    protected $fillable = [
        'form_id',
        'to',
        'cc',
        'bcc',
        'sender',
        'reply_to',
        'subject',
        'template',
    ];

    protected $casts = [
        'to' => DelimitedCast::class,
        'cc' => DelimitedCast::class,
        'bcc' => DelimitedCast::class,
        'reply_to' => DelimitedCast::class,
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Form\Models\Form, \Domain\Form\Models\FormEmailNotification> */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
