<?php

declare(strict_types=1);

namespace Domain\Form\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Domain\Form\Models\FormSubmission
 *
 * @property int $id
 * @property int $form_id
 * @property array $data
 *
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormSubmission query()
 * @mixin \Eloquent
 */
class FormSubmission extends Model
{
    protected $fillable = [
        'form_id',
        'data',
    ];

    protected $casts = [
        'data' => 'array',
    ];
}
