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
 * @property \Domain\Form\Models\Casts\DelimiterCast $recipient
 * @property \Domain\Form\Models\Casts\DelimiterCast|null $cc
 * @property \Domain\Form\Models\Casts\DelimiterCast|null $bcc
 * @property string|null $reply_to
 * @property string $sender
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
        'recipient',
        'cc',
        'bcc',
        'reply_to',
        'sender',
        'template',
    ];

    protected $casts = [
        'recipient' => DelimiterCast::class,
        'cc' => DelimiterCast::class,
        'bcc' => DelimiterCast::class,
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
