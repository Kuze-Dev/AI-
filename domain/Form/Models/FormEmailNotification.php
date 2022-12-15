<?php

declare(strict_types=1);

namespace Domain\Form\Models;

use Domain\Form\Models\Casts\DelimiterCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * Domain\Form\Models\FormEmailNotification
 *
 * @property int $id
 * @property int $form_id
 * @property array $to
 * @property array|null $cc
 * @property array|null $bcc
 * @property string $sender
 * @property array|null $reply_to
 * @property string $template
 *
 * @property-read \Domain\Form\Models\Form $form
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
        'to' => DelimiterCast::class,
        'cc' => DelimiterCast::class,
        'bcc' => DelimiterCast::class,
        'reply_to' => DelimiterCast::class,
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
