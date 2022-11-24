<?php

declare(strict_types=1);

namespace Domain\Form\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Domain\Form\Models\FormEmailNotification
 *
 * @property int $id
 * @property int $form_id
 * @property string $recipient
 * @property string|null $cc
 * @property string|null $bcc
 * @property string|null $reply_to
 * @property string $sender
 * @property string $template
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormEmailNotification query()
 * @mixin \Eloquent
 */
class FormEmailNotification extends Model
{
    protected $fillable = [
        'form_id',
        'recipient',
        'cc',
        'bcc',
        'reply_to',
        'sender',
        'template',
    ];

    public function form(): BelongsTo
    {
        return $this->belongsTo(Form::class);
    }
}
