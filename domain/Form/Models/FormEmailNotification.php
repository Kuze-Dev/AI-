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
 * @property int $id
 * @property int $form_id
 * @property array|null $to
 * @property array|null|null $cc
 * @property array|null|null $bcc
 * @property string|null $sender_name
 * @property array|null|null $reply_to
 * @property string $subject
 * @property string $template
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property bool $has_attachments
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Activitylog\Models\Activity> $activities
 * @property-read int|null $activities_count
 * @property-read \Domain\Form\Models\Form $form
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification query()
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereBcc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereCc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereHasAttachments($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereReplyTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereSenderName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereTemplate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification whereUpdatedAt($value)
 *
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
        'sender_name',
        'reply_to',
        'subject',
        'template',
        'has_attachments',
    ];

    protected function casts(): array
    {
        return [
            'to' => DelimitedCast::class,
            'cc' => DelimitedCast::class,
            'bcc' => DelimitedCast::class,
            'reply_to' => DelimitedCast::class,
            'has_attachments' => 'bool',
        ];
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logFillable()
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    /** @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Domain\Form\Models\Form, $this> */
    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
